# JOVEpay WHMCS Payment Gateway

Third-party payment gateway module for WHMCS 8.x. Accept Bitcoin, Ethereum, stablecoins, and other cryptocurrencies on WHMCS invoices via the JOVEpay hosted payment widget.

## Requirements

- WHMCS 8.0 or later
- PHP 7.2 or later (match your WHMCS installation requirements)
- An activated JOVEpay merchant account
- API Key and IPN Secret from the JOVEpay dashboard

## Package contents

```
modules/gateways/jovepay.php
modules/gateways/callback/jovepay.php
modules/gateways/jovepay/whmcs.json
modules/gateways/jovepay/logo.svg
modules/gateways/jovepay/images/logo.svg
modules/gateways/jovepay/images/coins/
```

## Installation

1. Copy the contents of `modules/` into your WHMCS root directory so the files above exist under your WHMCS installation.

2. In the WHMCS admin area, go to **Configuration () > System Settings > Apps & Integrations**.

3. Find **JOVEpay Crypto Payments** and click **Activate**, or go to **Configuration () > System Settings > Payment Gateways**, select **JOVEpay** from **Activate New Gateway**, and click **Save Changes**.

4. Enter your **API Key** and **IPN Secret** from your JOVEpay account (Payment Settings → API and Webhooks).

5. Optionally configure **Pay Now Button Text**, **Checkout Theme**, **Checkout Language**, and **Testnet Mode**.

6. In **Configuration () > System Settings > General Settings**, set **Domain** / **System URL** to the public URL of your WHMCS client area (for example `https://example.com/whmcs`). This URL is used to build the IPN callback and payment button image paths.

7. Save the gateway configuration.

## How payments work

1. The client opens an unpaid invoice and chooses JOVEpay.
2. The client submits the payment button and is redirected to the JOVEpay hosted payment widget.
3. The client pays with a supported cryptocurrency.
4. JOVEpay sends a signed Instant Payment Notification to WHMCS.
5. WHMCS verifies the `X-JOVEPAY-SIG` HMAC header and records the invoice payment.
6. The client returns to the WHMCS invoice return URL.

Invoice references sent to JOVEpay use the format `WHMCS-{invoiceId}`.

## IPN callback

WHMCS receives payment notifications at:

`https://your-domain.com/whmcs/modules/gateways/callback/jovepay.php`

The callback URL is sent automatically when a customer starts checkout. Requests are verified with the `X-JOVEPAY-SIG` HMAC header using your configured IPN secret.

## Account setup

1. Create and activate a merchant account at https://www.jovepay.com
2. Sign in at https://app.jovepay.com
3. Copy the API key from API settings and the IPN secret from Webhooks settings
4. Paste both values into the JOVEpay gateway configuration in WHMCS

An additional JOVEpay merchant account is required. JOVEpay service or transaction fees may apply. The account is not created during module installation.

## Marketplace listing notes

- Listing descriptions on the WHMCS Marketplace must be self-contained. Do not rely on external “learn more” pages for required product information.
- Marketplace listing icons should be GIF, PNG, or JPEG, ideally 200x200 pixels.
- Screenshots should use the Six or Twenty-One client theme at high resolution (up to three on a standard listing).
- Paste the content from `marketing.md` into the Marketplace long description field.

## Support

- Website: https://www.jovepay.com
- Merchant dashboard: https://app.jovepay.com
- Email: support@jovepay.com

## License

GPL-2.0-or-later. See `LICENSE`.
