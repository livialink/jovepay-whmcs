<?php
/**
 * WHMCS JOVEpay Payment Gateway Module
 *
 * Third-party payment gateway that redirects customers to the JOVEpay hosted
 * payment widget to complete cryptocurrency payments. WHMCS receives HMAC-signed
 * Instant Payment Notifications (IPN) when payments are confirmed.
 *
 * @see https://developers.whmcs.com/payment-gateways/
 *
 * @copyright Copyright (c) JOVEpay
 * @license GPL-2.0-or-later
 */

if (!defined('WHMCS')) {
    die('This file cannot be accessed directly');
}

/**
 * Define module related meta data.
 *
 * @see https://developers.whmcs.com/payment-gateways/meta-data-params/
 *
 * @return array
 */
function jovepay_MetaData()
{
    return [
        'DisplayName' => 'JOVEpay Crypto Payments',
        'APIVersion' => '1.1',
        'DisableLocalCreditCardInput' => true,
        'TokenisedStorage' => false,
    ];
}

/**
 * Define gateway configuration options.
 *
 * @see https://developers.whmcs.com/payment-gateways/configuration/
 *
 * @return array
 */
function jovepay_config()
{
    return [
        'FriendlyName' => [
            'Type' => 'System',
            'Value' => 'JOVEpay',
        ],
        'UsageNotes' => [
            'Type' => 'System',
            'Value' => 'Accept Bitcoin, Ethereum, stablecoins, and other cryptocurrencies via JOVEpay. '
                . 'A JOVEpay merchant account is required. '
                . 'Get your API Key and IPN Secret from the JOVEpay dashboard at '
                . '<a href="https://app.jovepay.com/" target="_blank" rel="noopener noreferrer">app.jovepay.com</a>. '
                . 'New merchants can sign up at '
                . '<a href="https://www.jovepay.com/" target="_blank" rel="noopener noreferrer">jovepay.com</a>.',
        ],
        'apiKey' => [
            'FriendlyName' => 'API Key',
            'Type' => 'text',
            'Size' => '64',
            'Default' => '',
            'Description' => 'Your JOVEpay Merchant ID / API key from Payment Settings → API',
        ],
        'ipnSecret' => [
            'FriendlyName' => 'IPN Secret',
            'Type' => 'password',
            'Size' => '64',
            'Default' => '',
            'Description' => 'Your JOVEpay IPN / webhook secret from Payment Settings → Webhooks',
        ],
        'payNowLabel' => [
            'FriendlyName' => 'Pay Now Button Text',
            'Type' => 'text',
            'Size' => '40',
            'Default' => 'Pay with JOVEpay',
            'Description' => 'Label shown on the invoice payment button',
        ],
        'theme' => [
            'FriendlyName' => 'Checkout Theme',
            'Type' => 'dropdown',
            'Options' => [
                'light' => 'Light',
                'dark' => 'Dark',
            ],
            'Default' => 'light',
            'Description' => 'Theme for the JOVEpay hosted payment widget',
        ],
        'locale' => [
            'FriendlyName' => 'Checkout Language',
            'Type' => 'dropdown',
            'Options' => [
                'en' => 'English',
                'es' => 'Spanish',
                'fr' => 'French',
                'de' => 'German',
                'it' => 'Italian',
                'pt' => 'Portuguese',
                'ru' => 'Russian',
                'zh' => 'Chinese',
                'ja' => 'Japanese',
                'ko' => 'Korean',
                'ar' => 'Arabic',
                'hi' => 'Hindi',
                'bn' => 'Bengali',
            ],
            'Default' => 'en',
            'Description' => 'Language for the JOVEpay hosted payment widget',
        ],
        'isTestnet' => [
            'FriendlyName' => 'Testnet Mode',
            'Type' => 'yesno',
            'Description' => 'Tick to enable JOVEpay testnet mode for end-to-end testing',
        ],
    ];
}

/**
 * Payment link.
 *
 * Required by third party payment gateway modules only.
 *
 * @param array $params Payment Gateway Module Parameters
 *
 * @see https://developers.whmcs.com/payment-gateways/third-party-gateway/
 *
 * @return string
 */
function jovepay_link($params)
{
    $apiKey = trim((string) ($params['apiKey'] ?? ''));
    if ($apiKey === '') {
        return '<p>JOVEpay is not configured. Please contact the store administrator.</p>';
    }

    $invoiceId = $params['invoiceid'];
    $amount = $params['amount'];
    $currencyCode = $params['currency'];
    $systemUrl = rtrim($params['systemurl'], '/');
    $moduleName = $params['paymentmethod'];
    $returnUrl = !empty($params['returnurl'])
        ? $params['returnurl']
        : ($systemUrl . '/viewinvoice.php?id=' . urlencode((string) $invoiceId));
    $langPayNow = !empty($params['payNowLabel'])
        ? $params['payNowLabel']
        : ($params['langpaynow'] ?? 'Pay with JOVEpay');
    $theme = $params['theme'] ?? 'light';
    $locale = $params['locale'] ?? 'en';
    $isTestnet = (($params['isTestnet'] ?? '') === 'on' || ($params['isTestnet'] ?? '') === 'yes');

    $imagesBase = $systemUrl . '/modules/gateways/jovepay/images';
    $logoUrl = $imagesBase . '/logo.svg';
    $coinUrls = [
        'btc' => $imagesBase . '/coins/btc.png',
        'eth' => $imagesBase . '/coins/eth.png',
        'usdt' => $imagesBase . '/coins/usdt.png',
        'bnb' => $imagesBase . '/coins/bnb.png',
    ];
    $ipnUrl = $systemUrl . '/modules/gateways/callback/' . $moduleName . '.php';

    $jovepayArgs = [
        'isTestnet' => $isTestnet,
        'ipnCallbackUrl' => $ipnUrl,
        'successUrl' => $returnUrl,
        'cancelUrl' => $returnUrl,
        'dataSource' => 'whmcs',
        'priceCurrency' => mb_strtoupper($currencyCode),
        'apiKey' => $apiKey,
        'customerName' => trim(
            ($params['clientdetails']['firstname'] ?? '') . ' ' . ($params['clientdetails']['lastname'] ?? '')
        ),
        'customerEmail' => $params['clientdetails']['email'] ?? '',
        'priceAmount' => $amount,
        'orderId' => 'WHMCS-' . $invoiceId,
    ];

    $paymentParams = base64_encode(json_encode($jovepayArgs));
    $isTestnetValue = $isTestnet ? 'true' : 'false';

    $safeAction = htmlspecialchars('https://www.jovepay.com/pay/payment', ENT_QUOTES, 'UTF-8');
    $safeParams = htmlspecialchars($paymentParams, ENT_QUOTES, 'UTF-8');
    $safeLocale = htmlspecialchars($locale, ENT_QUOTES, 'UTF-8');
    $safeTheme = htmlspecialchars($theme, ENT_QUOTES, 'UTF-8');
    $safeIsTestnet = htmlspecialchars($isTestnetValue, ENT_QUOTES, 'UTF-8');
    $safeLogoUrl = htmlspecialchars($logoUrl, ENT_QUOTES, 'UTF-8');
    $safePayNow = htmlspecialchars($langPayNow, ENT_QUOTES, 'UTF-8');
    $safeBtc = htmlspecialchars($coinUrls['btc'], ENT_QUOTES, 'UTF-8');
    $safeEth = htmlspecialchars($coinUrls['eth'], ENT_QUOTES, 'UTF-8');
    $safeUsdt = htmlspecialchars($coinUrls['usdt'], ENT_QUOTES, 'UTF-8');
    $safeBnb = htmlspecialchars($coinUrls['bnb'], ENT_QUOTES, 'UTF-8');

    return <<<HTML
<style>
.jovepay-pay-button {
    display: inline-flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 0.5rem;
    width: 100%;
    max-width: 28rem;
    padding: 0.65rem 0.9rem;
    border: 1px solid #d0d5dd;
    border-radius: 0.5rem;
    background: #fff;
    color: #101828;
    text-decoration: none;
    box-sizing: border-box;
    cursor: pointer;
    font: inherit;
    line-height: 1.2;
    text-align: left;
    appearance: none;
    -webkit-appearance: none;
}
.jovepay-pay-button:hover,
.jovepay-pay-button:focus {
    border-color: #2F6DE0;
    text-decoration: none;
    color: #101828;
}
.jovepay-pay-button__logo {
    display: block;
    width: auto;
    height: 1.75rem;
    max-height: 28px;
    object-fit: contain;
    flex-shrink: 0;
}
.jovepay-pay-button__text {
    flex: 0 1 auto;
    font-weight: 600;
}
.jovepay-pay-button__icons {
    display: inline-flex;
    align-items: center;
    margin-inline-start: auto;
    line-height: 1;
}
.jovepay-pay-button__stack {
    display: inline-flex;
    align-items: center;
    flex-direction: row;
}
.jovepay-pay-button__coin {
    display: block;
    width: 1.5rem;
    height: 1.5rem;
    margin-inline-start: -0.45rem;
    border-radius: 50%;
    border: 2px solid #fff;
    box-shadow: 0 0 0 1px rgba(15, 23, 42, 0.08);
    object-fit: cover;
    background: #fff;
    position: relative;
}
.jovepay-pay-button__stack .jovepay-pay-button__coin:first-child {
    margin-inline-start: 0;
}
@media (prefers-color-scheme: dark) {
    .jovepay-pay-button {
        background: #1e1e1e;
        border-color: #3f3f46;
        color: #f4f4f5;
    }
    .jovepay-pay-button:hover,
    .jovepay-pay-button:focus {
        border-color: #2F6DE0;
        color: #f4f4f5;
    }
    .jovepay-pay-button__coin {
        border-color: #1e1e1e;
        box-shadow: 0 0 0 1px rgba(255, 255, 255, 0.12);
    }
}
</style>
<form method="get" action="{$safeAction}" accept-charset="UTF-8">
    <input type="hidden" name="params" value="{$safeParams}" />
    <input type="hidden" name="locale" value="{$safeLocale}" />
    <input type="hidden" name="theme" value="{$safeTheme}" />
    <input type="hidden" name="isTestnet" value="{$safeIsTestnet}" />
    <button type="submit" class="jovepay-pay-button">
        <img class="jovepay-pay-button__logo" src="{$safeLogoUrl}" alt="JOVEpay" />
        <span class="jovepay-pay-button__text">{$safePayNow}</span>
        <span class="jovepay-pay-button__icons" aria-hidden="true">
            <span class="jovepay-pay-button__stack">
                <img class="jovepay-pay-button__coin" src="{$safeBtc}" alt="" style="z-index: 4" />
                <img class="jovepay-pay-button__coin" src="{$safeEth}" alt="" style="z-index: 3" />
                <img class="jovepay-pay-button__coin" src="{$safeUsdt}" alt="" style="z-index: 2" />
                <img class="jovepay-pay-button__coin" src="{$safeBnb}" alt="" style="z-index: 1" />
            </span>
        </span>
    </button>
</form>
HTML;
}

