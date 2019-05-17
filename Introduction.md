# Collector Checkout – Introduction
## Introduction
Collector Checkout for OpenCart is a plugin that extends OpenCart, allowing you to take payments via Collector.

## Prerequisites
* Opencart 2.*
* Installing HTTPS (a SSL/TLS certificate) in your web store is strongly recommended.

## Limitations in functionality
Collector Checkout is an embedded checkout, a checkout solution that replaces the standard OpenCart checkout form. This setup provides an easy way for the customer to complete the purchase and a convenient way for the merchant to offer multiple payment options in the same solution.

An embedded checkout also implies certain limitations in functionality. Compatibility with other plugins that extend the functionality of the standard checkout might be limited. Examples of features that might not work out of the box:
* Extra checkout fields (besides the standard billing and shipping fields).
* Newsletter subscription signup.
* Create an account during checkout process where the customer gets the opportunity to select user name and password.
* Sign up with Collector
* To get started with Collector Checkout, you need to create an account with Collector. If you’re not a customer yet you can register here.

## Testing
To make test purchases you also need customer test data. This information can be found [here][test-persons].

## Installation
* Navigate to the OpenCart Extension Store and download the [Collector Checkout for OpenCart Extension][download-oc-catalog].
* Login to your OpenCart Admin. Click on Extensions | Extension Installer from the left hand menu
* Click on the Upload option, then click Browse to select the zip file from your computer. Once selected, press OK and press the Install Now button.
* Make sure the extension is activated.

## Configuration
Navigate to → Extensions → Payments → Collector Checkout.

## Settings on the configuration page 
* Status Enable / Disable – Enable if you want the payment method should be available at checkout.
* Store Mode – Depending on your customer group and your contract with Collector you can decide if payment methods should be offered to consumers, businesses or both.
* Country – Depending on your customer group and your contract with Collector you can decide for which countries your payment methods should be valid.
* Merchant ID Sweden B2C – Your Merchant ID for B2C purchases in Sweden received from Collector.
* Merchant ID Sweden B2B – Your Merchant ID for B2B purchases in Sweden received from Collector.
* Merchant ID Norway B2C – Your Merchant ID for B2C purchases in Norway received from Collector.
* Merchant ID Sweden B2B – Your Merchant ID for B2B purchases in Sweden received from Collector.
* Username – Your Username received from Collector.
* Shared key – Your Shared Key received from Collector.
* Mode – Tell Collector if you are running in Test-mode or Production-mode.
* Order Status for accepted payments – Decide which Status should be displayed in Opencart for order status “Accepted” in Collector 
* Order Status for preliminary orders – Decide which Status should be displayed in Opencart for order status “Preliminary” in Collector 
* Order Status for pending payments – Decide which Status should be displayed in Opencart for order status “Pending” in Collector 
* Order Status for rejected payments payments – Decide which Status should be displayed in Opencart for order status “Rejected” in Collector 
* Order Status for credited payments payments – Decide which Status should be displayed in Opencart for order status “Credited” in Collector 
* Merchant Terms Url –The URL for your Terms and Conditions to be hyperlinked from the Collector Checkout

## Order Management
When an order is created in OpenCart and a reservation number exists in Collector’s system, you have the possibility to handle the order management in Collector directly from OpenCart. This way you can save time and don’t have to work in both systems simultaneously.


### Cancel an order
* Navigate to → OpenCart → Orders and click on the order you want to view.https://aaitse.github.io/collector-checkout-opencart/docs/image1.png
* In the Order details box there is a section named Collector. Click on the Cancel submit button and the order will be cancelled. 
![](https://aaitse.github.io/collector-checkout-opencart/docs/image1.jpg)
* Note that activated orders cannot be cancelled.

### Activate an order
* Navigate to → OpenCart → Orders and click on the order you want to view.
* In the Order details box there is a section named Collector. Click on the Activate submit button and the order will be Activated.
![](https://aaitse.github.io/collector-checkout-opencart/docs/image2.jpg)

### Refund an order
* Navigate to → OpenCart → Orders and click on the order you want to view.
* In the Order details box there is a section named Collector. Click on the Credit submit button and the order will be refunded.
* Note that only orders with the Collector order status “Purchase completed” Can be refunded.


[test-persons]: https://merchant.collectorbank.se/integration/b2c/general-information/test-persons/
[download-oc-catalog]: https://www.opencart.com/index.php?route=marketplace/extension/info&extension_id=33259