# Collector Checkout for OpenCart

Collector Checkout Extension for OpenCart, allowing you to take payments via Collector Banks payment method Collector Checkout. A contract with Collector Bank is required to use this payment solution.

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
