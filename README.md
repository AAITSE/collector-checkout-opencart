# Collector Checkout for OpenCart

Collector Checkout Extension for OpenCart, allowing you to accept payments via Collector Banks payment method Collector Checkout. A contract with Collector Bank is required to use this payment solution. This Extension is commisioned by Collector Bank AB and maintained by Aight Konsult. Support questions is preferably directed to info@aait.se 

## Installation

### Extension Installer
1. Login to OpenCart admin panel
2. Go to System->Settings->FTP and configure ftp.
3. Go to Extensions->Extensions Installer. Upload module package [collector.ocmod.zip][package] and click Continue to install.
4. Go to Extensions->Modifications and click Refresh button.
5. Go to Extensions->Extensions. Select "Payments" extension type. Find "Collector Checkout" and click Install button.
6. Click Edit button after install to enable and configure Collector Checkout.

### Manually
1. Clone repository or download as ZIP archive and unzip files
2. Upload files to root of your OpenCart site using FTP/SFTP, the root is usually the public_html folder.
3. Login to OpenCart admin panel
4. Install modification XML. Go to Extensions->Extensions Installer. Upload install.xml from ocmod directory and click Continue to install.
5. Go to Extensions->Extensions. Select "Payments" extension type. Find "Collector Checkout" and click Install button.
6. Click Edit button after install to enable and configure Collector Checkout.

[package]: https://github.com/AAITSE/collector-checkout-opencart/raw/master/ocmod/collector.ocmod.zip

## Manual

### Configuration
Navigate to Extensions -> Payments -> Collector Bank -> Edit Mode

### Legend

**Status** Choose if you want the module be active or note
**Store mode** If your contract with Collector supports both "Business to Business" (B2B) and "Business to Consumer" (B2C) payments choose the "B2C and B2B" option, otherwise choose the option that is supported by your contract. 
**Merchant ID** This ID will be given to you by your Collector Bank sales representative
**Username** This username will be given to you by your Collector Bank sales representative
**Shared Key** This shared key will be given to you by your Collector Bank sales representative
**Mode** Choose Test mode for testing and Production mode for real payments
Testpersons can be found here: https://merchant.collectorbank.se/integration/b2c/general-information/test-persons/
**Order Status for preliminary orders**  Decide which status should be displayed on an order that has been reported as "Preliminary" by Collector.  This status tells the Merchant that the Invoice has been accepted but need confirmation by the Merchant (Capture).
**Order Status for accepted payments** Decide which status should be displayed on an order that has been accepted for payment buy Merchant(aka "Captured" or "Activated").
**Order Status for pending payments** Decide which status should be displayed on an order that has been made but not payed in Collector yet or is under investigation by Collector.
**Order Status for rejected payments** Decide which status should be displayed on an order that has been rejected by Collector.
**Merchant Terms Url** Specify the web address where your Terms for orders are declared.
**Token for Invoice Status URL** Just make up any string of characters (at least 5) to individualize your Invoice status URL. It will not be visible for your customer.
**Invoice Status URL** This URL should be submitted in Collector Merchant Admin panel. It will be used by the API to communicate with OpenCart
**Invoice fee (incl. tax) B2C** The invoice fee that will be added for Invoice payments (B2C)
**Invoice VAT rate B2C** Invoice VAT rate to be added for the invoice fee (B2C)
**Invoice fee (incl. tax) B2B** The invoice fee that will be added for Invoice payments (B2B)
**Invoice VAT rate B2B** Invoice VAT rate to be added for the invoice fee (B2B)
**Instant Checkout Status** Enable if you want Collector method "Instant Checkout" to be active. Please contact your Collector Bank sales representative for more information.
**Instant Checkout: Merchant ID** Please decide what kind of payment your Instant Checkout should work with (usually B2C) and input the corresponding store ID
**Instant Checkout: Country** Please decide which country your Instant Checkout should work with.
