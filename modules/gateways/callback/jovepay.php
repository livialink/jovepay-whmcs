<?php
/**
 * WHMCS JOVEpay Payment Callback File
 *
 * Receives HMAC-signed Instant Payment Notifications from JOVEpay and applies
 * payment to the matching WHMCS invoice.
 *
 * @see https://developers.whmcs.com/payment-gateways/callbacks/
 *
 * @copyright Copyright (c) JOVEpay
 * @license GPL-2.0-or-later
 */

require_once __DIR__ . '/../../../init.php';
require_once __DIR__ . '/../../../includes/gatewayfunctions.php';
require_once __DIR__ . '/../../../includes/invoicefunctions.php';

$gatewayModuleName = basename(__FILE__, '.php');
$gatewayParams = getGatewayVariables($gatewayModuleName);

if (!$gatewayParams['type']) {
    http_response_code(503);
    die('Module Not Activated');
}

$rawRequest = file_get_contents('php://input');
if ($rawRequest === false || $rawRequest === '') {
    logTransaction($gatewayParams['name'], [], 'Empty callback body');
    http_response_code(400);
    die('Empty callback body');
}

$receivedHmac = $_SERVER['HTTP_X_JOVEPAY_SIG'] ?? '';
if ($receivedHmac === '') {
    logTransaction($gatewayParams['name'], $rawRequest, 'Missing HMAC signature');
    http_response_code(401);
    die('Missing HMAC signature');
}

$ipnSecret = trim((string) ($gatewayParams['ipnSecret'] ?? ''));
if ($ipnSecret === '') {
    logTransaction($gatewayParams['name'], $rawRequest, 'IPN secret not configured');
    http_response_code(503);
    die('IPN secret not configured');
}

$expectedHmac = hash_hmac('sha256', $rawRequest, $ipnSecret);
if (!hash_equals($expectedHmac, trim($receivedHmac))) {
    logTransaction($gatewayParams['name'], $rawRequest, 'Invalid HMAC signature');
    http_response_code(401);
    die('Invalid HMAC signature');
}

$requestData = json_decode($rawRequest, true);
if (!is_array($requestData)) {
    logTransaction($gatewayParams['name'], $rawRequest, 'Invalid JSON payload');
    http_response_code(400);
    die('Invalid JSON payload');
}

$orderId = (string) ($requestData['orderId'] ?? '');
if ($orderId === '' || strpos($orderId, 'WHMCS-') !== 0) {
    logTransaction($gatewayParams['name'], $requestData, 'Invalid or missing orderId');
    http_response_code(400);
    die('Invalid or missing orderId');
}

$invoiceId = substr($orderId, strlen('WHMCS-'));
$invoiceId = checkCbInvoiceID($invoiceId, $gatewayParams['name']);

$transactionId = trim((string) ($requestData['paymentId'] ?? ''));
$status = (string) ($requestData['paymentStatus'] ?? '');
$priceAmount = $requestData['priceAmount'] ?? 0;
$paymentAmount = $requestData['payAmount'] ?? 0;
$currency = mb_strtoupper((string) ($requestData['priceCurrency'] ?? ''));
$paymentFee = 0;

switch ($status) {
    case 'finished':
        if ($transactionId === '') {
            logTransaction($gatewayParams['name'], $requestData, 'Missing paymentId for finished payment');
            http_response_code(400);
            die('Missing paymentId');
        }

        checkCbTransID($transactionId);

        addInvoicePayment(
            $invoiceId,
            $transactionId,
            $priceAmount,
            $paymentFee,
            $gatewayModuleName
        );

        logTransaction(
            $gatewayParams['name'],
            $requestData,
            "Success: Invoice {$invoiceId} paid. Amount: {$priceAmount} {$currency}"
        );
        break;

    case 'partially_paid':
        if ($transactionId === '') {
            logTransaction($gatewayParams['name'], $requestData, 'Missing paymentId for partial payment');
            http_response_code(400);
            die('Missing paymentId');
        }

        checkCbTransID($transactionId);

        addInvoicePayment(
            $invoiceId,
            $transactionId,
            $paymentAmount,
            $paymentFee,
            $gatewayModuleName
        );

        logTransaction(
            $gatewayParams['name'],
            $requestData,
            "Partial payment: Invoice {$invoiceId}. Expected {$priceAmount} {$currency}, received {$paymentAmount} {$currency}"
        );
        break;

    case 'confirming':
    case 'confirmed':
    case 'sending':
    case 'waiting':
        logTransaction($gatewayParams['name'], $requestData, 'Pending: ' . $status);
        break;

    case 'failed':
        logTransaction($gatewayParams['name'], $requestData, 'Failed payment');
        break;

    default:
        logTransaction($gatewayParams['name'], $requestData, 'Unhandled payment status: ' . $status);
        break;
}

http_response_code(200);
echo 'OK';
