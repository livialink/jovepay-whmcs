# Accept Bitcoin and cryptocurrency payments - JOVEpay for WHMCS

Paste the content below into the WHMCS Marketplace listing description. Marketplace listing descriptions must be self-contained and must not depend on external “learn more” links for required product information.

---

Accept cryptocurrency payments in your WHMCS billing system with JOVEpay. Customers pay invoices with Bitcoin, Ethereum, stablecoins, and other supported digital assets on JOVEpay’s hosted checkout widget, while WHMCS continues to manage clients, invoices, and provisioning.

After a customer opens an unpaid invoice and chooses JOVEpay, they are redirected to the secure JOVEpay payment widget. When payment is confirmed, JOVEpay sends a signed Instant Payment Notification (IPN) to WHMCS so the invoice can be marked paid automatically—no manual blockchain verification required.

Settlement goes to the merchant’s configured receiving wallet. Merchants manage API credentials, webhooks, and payment settings from the JOVEpay merchant dashboard.

**An additional JOVEpay merchant account is required, and JOVEpay service / transaction fees may apply. The account is not created during module installation.**

### Features

- Accept cryptocurrency payments on WHMCS invoices
- Multi-chain support across networks such as Bitcoin, Ethereum, BNB Smart Chain, Polygon, Solana, Tron, and other JOVEpay-supported networks and assets
- Direct settlement to the merchant’s configured receiving wallet
- Hosted JOVEpay payment widget with light and dark themes
- Configurable payment button label and multi-language widget locale
- Branded invoice payment button with JOVEpay logo and overlapping crypto coin icons
- Redirect-based checkout to JOVEpay’s secure payment environment
- HMAC-signed Instant Payment Notifications (IPN) with automatic invoice payment recording
- Partial payment handling
- Duplicate transaction protection through WHMCS callback validation
- Optional testnet mode for safe end-to-end testing
- No chargebacks for completed cryptocurrency payments

### How it works

1. A client opens an unpaid invoice in the WHMCS client area and chooses JOVEpay.
2. The client clicks the payment button and is redirected to the JOVEpay hosted payment widget.
3. The client pays with a supported cryptocurrency on a supported network.
4. JOVEpay monitors the payment and sends a signed IPN to WHMCS.
5. WHMCS verifies the HMAC signature and records the invoice payment.
6. The client is returned to the WHMCS invoice return URL when payment is complete.

Invoice references sent to JOVEpay use the format WHMCS-{invoiceId}.

### Requirements

- WHMCS 8.0 or later
- PHP 7.2 or later (match your WHMCS installation requirements)
- An open and activated JOVEpay merchant account
- API Key and IPN Secret from the JOVEpay dashboard

### Installation

1. Copy the module files into your WHMCS root so you have modules/gateways/jovepay.php, modules/gateways/callback/jovepay.php, and modules/gateways/jovepay/ (including whmcs.json, logo.svg, and images).
2. In WHMCS admin, open Configuration > System Settings > Apps & Integrations.
3. Find JOVEpay Crypto Payments and click Activate, or activate JOVEpay under Payment Gateways.
4. Enter your API Key and IPN Secret.
5. Optionally set Pay Now Button Text, Checkout Theme, Checkout Language, and Testnet Mode.
6. Confirm System URL under General Settings is your public WHMCS client area URL.
7. Save the gateway configuration.

### IPN callback

WHMCS receives payment notifications at:

https://your-domain.com/whmcs/modules/gateways/callback/jovepay.php

The callback URL is sent automatically when a customer starts checkout. Requests are verified with the X-JOVEPAY-SIG HMAC header using your configured IPN secret.

### Account and pricing

Create and activate a merchant account at www.jovepay.com, then copy your API key and IPN secret from the JOVEpay dashboard (API and Webhooks settings). For current pricing and commercial terms, visit www.jovepay.com or contact support@jovepay.com.

### Security

Customers complete cryptocurrency payment on the JOVEpay hosted widget. Wallet-signing and payment confirmation do not occur inside WHMCS admin or as card-form fields on your client area. No cryptocurrency private keys or card payment data are entered into or stored on the WHMCS server by this module.

JOVEpay communicates payment results through signed Instant Payment Notifications. WHMCS stores standard invoice and client data already collected by WHMCS, plus merchant configuration (API key and IPN secret) and payment status updates received via IPN.

### FAQ

**Do I need a JOVEpay account?**
Yes. A JOVEpay merchant account is required and is not created during module installation.

**Does this module store card data?**
No. This is a cryptocurrency third-party gateway. Customers pay on JOVEpay’s hosted widget.

**Can I test before going live?**
Yes. Enable Testnet Mode in the gateway settings and use JOVEpay testnet credentials.

**What WHMCS versions are supported?**
WHMCS 8.0 or later.

### Support

Website: www.jovepay.com
Merchant dashboard: app.jovepay.com
Email: support@jovepay.com
