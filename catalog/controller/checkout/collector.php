<?php

require_once DIR_SYSTEM . '../vendors/guzzle/vendor/autoload.php';

class ControllerCheckoutCollector extends Controller
{
    const FRONTEND_URL_LIVE = '	https://checkout.collector.se';
    const FRONTEND_URL_TEST = 'https://checkout-uat.collector.se';

    /**
     * @return ModelCollectorView
     */
    protected function getView()
    {
        $this->load->model('collector/view');
        return $this->model_collector_view;
    }

    /**
     * @return ModelCollectorHelper
     */
    protected function getHelper()
    {
        $this->load->model('collector/helper');
        return $this->model_collector_helper;
    }

    /**
     * @return ModelCollectorApi
     */
    protected function getApi()
    {
        $this->load->model('collector/api');
        return $this->model_collector_api;
    }

    /**
     * @return ModelCollectorVisitors
     */
    protected function getVisitors()
    {
        $this->load->model('collector/visitors');
        return $this->model_collector_visitors;
    }

    /**
     * @return ModelCollectorQuote
     */
    protected function getQuote()
    {
        $this->load->model('collector/quote');
        return $this->model_collector_quote;
    }

    /**
     * @return ModelCollectorPayments
     */
    protected function getPayments()
    {
        $this->load->model('collector/payments');
        return $this->model_collector_payments;
    }

    /**
     * @return array
     */
    protected function getSettings()
    {
        return $this->getApi()->getSettings();
    }

    /**
     * @return string
     */
    protected function getCustomerType()
    {
    	$settings = $this->getSettings();
    	switch ($settings['collector_store_mode']) {
		    case 'b2c':
		    	return 'private';
		    case 'b2b':
		    	return 'company';
		    default:
			    return isset($this->session->data['collector_customer_type']) ?
				    $this->session->data['collector_customer_type'] : 'private';
	    }
    }

    /**
     * Get Merchant Store Id
     * @param $customer_type
     * @param string $country_code
     *
     * @return mixed
     */
    protected function getMerchantStoreId($customer_type, $country_code = 'se')
    {
        $customer_type = $customer_type === 'private' ? 'b2c' : 'b2b';
        $country_code = strtolower($country_code);
        if (!in_array($country_code, ['se', 'no'])) {
            $country_code = 'se';
        }

        $settings = $this->getSettings();
        $key = "collector_store_id_{$customer_type}_{$country_code}";
        return isset($settings[$key]) ? $settings[$key] : '';
    }

    /**
     * Index Action
     */
    public function index()
    {
        $this->load->language('checkout/checkout');
        $this->load->model('extension/payment/collector');

        // Load module settings
        $settings = $this->getSettings();

        // Validate cart has products and has stock.
        if (
            (!$this->cart->hasProducts() && empty($this->session->data['vouchers'])) ||
            (!$this->cart->hasStock() && !$this->config->get('config_stock_checkout')))
        {
            $this->response->redirect($this->url->link('checkout/cart'));
        }

        $this->document->setTitle($this->getView()->__('Collector Checkout'));
        $this->document->addScript('catalog/view/javascript/collector/async/async.min.js');
        $this->document->addScript('catalog/view/javascript/collector/checkout.js');

        $data = [
            'view' => $this->getView()
        ];

        $data['breadcrumbs'] = array();
        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/home')
        );
        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_cart'),
            'href' => $this->url->link('checkout/cart')
        );
        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('checkout/checkout', '', true)
        );
        $data['heading_title'] = $this->getView()->__('Collector Checkout');

        $data['column_left'] = $this->load->controller('common/column_left');
        $data['column_right'] = $this->load->controller('common/column_right');
        $data['content_top'] = $this->load->controller('common/content_top');
        $data['content_bottom'] = $this->load->controller('common/content_bottom');
        $data['footer'] = $this->load->controller('common/footer');
        $data['header'] = $this->load->controller('common/header');

        $data['is_logged'] = $this->customer->isLogged();
        $data['shipping_required'] = $this->cart->hasShipping();

        $data['countries'] = array_filter($this->getHelper()->getCountries(), function($value, $key) {
            return in_array($value['iso_code_2'], ['SE', 'NO']);
        }, ARRAY_FILTER_USE_BOTH);

        $data['country_id'] = isset($this->session->data['payment_address']) ?
            $this->session->data['payment_address']['country_id'] : $this->config->get('config_country_id');

        // Load Country info
        $country = $this->model_localisation_country->getCountry($data['country_id']);

        // Verify that selected country_id is possible for use
        if (!in_array($country['iso_code_2'], ['SE', 'NO'])) {
            // Set Sweden as default
            $country = array_filter($this->getHelper()->getCountries(), function($value, $key) {
                return in_array($value['iso_code_2'], ['SE']);
            }, ARRAY_FILTER_USE_BOTH);
            $country = array_shift($country);

            // Force country selection
            $this->session->data['payment_address']['country_id'] = $country['country_id'];
            $this->session->data['shipping_address']['country_id'] = $country['country_id'];
        }

        $data['store_mode'] = $settings['collector_store_mode'];
        $data['customer_type'] = $this->getCustomerType();
        $data['store_id'] = $this->getMerchantStoreId($data['customer_type'], $country['iso_code_2']);
        $data['locale'] = $country['iso_code_2'] === 'NO' ? 'nb-NO' : 'sv-SE';

        // Init Collector
        $cart_items = $this->getHelper()->getCartItems();
        $collector_items = $this->getHelper()->getCollectorItems($cart_items, $data['customer_type']);
	    if (count($collector_items['items']) === 0) {
		    // No items in cart
		    $this->response->redirect($this->url->link('checkout/cart'));
	    }

        // Update Quote
        $quote = $this->_makeQuote();

	    $private_id = isset($this->session->data['collector_private_id']) ? $this->session->data['collector_private_id'] : null;
	    if (!$private_id) {
	    	// Init Checkout
		    $params = [
			    'storeId' => $data['store_id'],
			    'countryCode' => $country['iso_code_2'],
			    'redirectPageUri' => $this->getView()->url('checkout/collector/success'),
			    'merchantTermsUri' => $settings['collector_merchant_terms_url'],
			    'notificationUri' => $this->getView()->url('checkout/collector/ipn', ['token' => $quote['token']]),
			    'cart' => [
				    'items' => $collector_items['items']
			    ]
		    ];
		    if (count($collector_items['fees']) > 0) {
			    $params['fees'] = $collector_items['fees'];
		    }

		    try {
			    $result = $this->getApi()->request('POST', '/checkout', $params);
		    } catch (Exception $e) {
			    $message = $e->getMessage();
			    echo 'Error: ' . $message;
			    exit();
		    }

		    // Store privateId in session
		    $this->session->data['collector_private_id'] = $result['data']['privateId'];

		    // Save in db
		    $this->getPayments()->add([
			    'quote_id' => $quote['quote_id'],
			    'private_id' => $result['data']['privateId'],
			    'public_token' => $result['data']['publicToken'],
			    'cart_items' => json_encode($cart_items),
			    'expiresAt' => $result['data']['expiresAt'],
			    'country_code' => $country['iso_code_2'],
			    'store_id' => $data['store_id']
		    ]);

		    $public_token = $result['data']['publicToken'];
	    } else {
	    	// Update Checkout

		    // Update Collector
		    // Update items
		    $params = [
			    'items' => $collector_items['items'],
		    ];
		    $result = $this->getApi()->request('PUT', sprintf('/merchants/%s/checkouts/%s/cart', $data['store_id'], $private_id), $params);

		    // Update fees
		    $params = $collector_items['fees'];
		    if ($this->cart->hasShipping()) {
			    $shipping = $this->getHelper()->getCartShipping();
			    if ($shipping['unit_price'] < 0.1) {
				    $params['shipping'] = null;
			    }
		    } else {
			    $params['shipping'] = null;
		    }

		    $result = $this->getApi()->request('PUT', sprintf('/merchants/%s/checkouts/%s/fees', $data['store_id'], $private_id), $params);

		    // Update Quote
		    $this->_makeQuote();

		    // Update cart items
		    $quote_id = isset($this->session->data['collector_quote_id']) ? $this->session->data['collector_quote_id'] : null;
		    $payment = $this->getPayments()->getByQuoteId($quote_id);
		    $this->getPayments()->update($payment['id'], [
			    'cart_items' => json_encode($cart_items),
		    ]);

		    $public_token = $payment['public_token'];
	    }

        $data['collector'] = [
            'frontend_api_url' => $settings['collector_mode'] === 'live' ? self::FRONTEND_URL_LIVE : self::FRONTEND_URL_TEST,
            'token' => $public_token
        ];

        $this->response->setOutput($this->getView()->render('checkout/collector', $data));
    }

    /**
     * Login Action
     */
    public function login()
    {
        if ($this->request->server['REQUEST_METHOD'] !== 'POST') {
            $this->response->addHeader('Content-Type: application/json');
            $this->response->setOutput(json_encode(
                [
                    'success' => false,
                    'message' => 'Login failed'
                ]
            ));
            return;
        }

        $this->load->model('account/customer');

        $email = $this->request->post['email'];
        $password = $this->request->post['password'];

        try {
            // Check how many login attempts have been made.
            $login_info = $this->model_account_customer->getLoginAttempts($email);
            if ($login_info && ($login_info['total'] >= $this->config->get('config_login_attempts')) && strtotime('-1 hour') < strtotime($login_info['date_modified'])) {
                throw new Exception($this->getView()->__('Your account has exceeded allowed number of login attempts. Please try again in 1 hour.'));
            }

            // Check if customer has been approved.
            $customer_info = $this->model_account_customer->getCustomerByEmail($email);
            if ($customer_info && !$customer_info['approved']) {
                throw new Exception($this->getView()->__('Your account requires approval before you can login.'));
            }

            // Login
            if (!$this->customer->login($email, $password)) {
                $this->model_account_customer->addLoginAttempt($email);
                throw new Exception($this->getView()->__('No match for E-Mail Address and/or Password.'));
            }

            $this->model_account_customer->deleteLoginAttempts($email);
        } catch (\Exception $e) {
            $this->response->addHeader('Content-Type: application/json');
            $this->response->setOutput(json_encode(
                [
                    'success' => false,
                    'message' => $e->getMessage()
                ]
            ));
            return;
        }

        // Unset guest
        unset($this->session->data['guest']);

        // Default Addresses
        $this->load->model('account/address');

        if ($this->config->get('config_tax_customer') == 'payment') {
            $this->session->data['payment_address'] = $this->model_account_address->getAddress($this->customer->getAddressId());
        }

        if ($this->config->get('config_tax_customer') == 'shipping') {
            $this->session->data['shipping_address'] = $this->model_account_address->getAddress($this->customer->getAddressId());
        }

        // Wishlist
        if (isset($this->session->data['wishlist']) && is_array($this->session->data['wishlist'])) {
            $this->load->model('account/wishlist');

            foreach ($this->session->data['wishlist'] as $key => $product_id) {
                $this->model_account_wishlist->addWishlist($product_id);

                unset($this->session->data['wishlist'][$key]);
            }
        }

        // Unset quote data
        unset($this->session->data['collector_quote_id']);

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode(['success' => true]));
    }

    /**
     * Success Action
     */
    public function success()
    {
        $this->_clearCart();

        unset($this->session->data['shipping_method']);
        unset($this->session->data['shipping_methods']);
        unset($this->session->data['payment_method']);
        unset($this->session->data['payment_methods']);
        unset($this->session->data['guest']);
        unset($this->session->data['comment']);
        unset($this->session->data['order_id']);
        unset($this->session->data['coupon']);
        unset($this->session->data['reward']);
        unset($this->session->data['voucher']);
        unset($this->session->data['vouchers']);
        unset($this->session->data['totals']);

        $this->load->language('checkout/success');
        $languages = [
            'heading_title',
            'text_home',
            'text_basket',
            'text_checkout',
            'text_success',
            'text_customer',
            'text_guest',
            'button_continue'
        ];
        foreach ($languages as $language) {
            $data[$language] = $this->language->get($language);
        }

        $data['breadcrumbs'] = [
            [
                'text' => $this->language->get('text_home'),
                'href' => $this->url->link('common/home')
            ],
            [
                'text' => $this->language->get('text_basket'),
                'href' => $this->url->link('checkout/cart')
            ],
            [
                'text' => $this->language->get('text_checkout'),
                'href' => $this->url->link('checkout/checkout', '', true)
            ],
            [
                'text' => $this->language->get('text_success'),
                'href' => $this->url->link('checkout/success')
            ]
        ];

        $this->document->setTitle($data['heading_title']);

        if ($this->customer->isLogged()) {
            $data['text_message'] = sprintf($this->language->get('text_customer'), $this->url->link('account/account', '', true), $this->url->link('account/order', '', true), $this->url->link('account/download', '', true), $this->url->link('information/contact'));
        } else {
            $data['text_message'] = sprintf($this->language->get('text_guest'), $this->url->link('information/contact'));
        }

        $data['continue'] = $this->url->link('common/home');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['column_right'] = $this->load->controller('common/column_right');
        $data['content_top'] = $this->load->controller('common/content_top');
        $data['content_bottom'] = $this->load->controller('common/content_bottom');
        $data['footer'] = $this->load->controller('common/footer');
        $data['header'] = $this->load->controller('common/header');

        $this->response->setOutput($this->getView()->render('checkout/collector/success', $data));
    }

    /**
     * Ipn Action
     */
    public function ipn()
    {
        $log = new Log('collector_ipn.log');
        $this->load->model('localisation/country');
	    $this->load->model('checkout/order');

        try {
            $token = isset($this->request->get['token']) ? $this->request->get['token'] : null;
            $quote = $this->getQuote()->getByToken($token);
            if (!$quote) {
                throw new Exception('Failed to get quote');
            }

            $log->write(sprintf('Incoming request for IPN %s. Token: %s. Quote ID: %s', $_SERVER['REQUEST_URI'], $token, $quote['quote_id']));

            $quote_data = @json_decode($quote['quote_data'], true);
            if (!$quote_data) {
                throw new Exception('Failed to get quote data');
            }

            $log->write(sprintf('Quote Data: %s', var_export($quote_data, true)));

            $payment = $this->getPayments()->getByQuoteId($quote['quote_id']);
            if (!$payment) {
                throw new Exception('Failed to get payment data');
            }

            $store_id = $payment['store_id'];
            $private_id = $payment['private_id'];

            // Get Information
            // See https://checkout-documentation.collector.se/#4-acquire-information-about-a-checkout-session
            $info = $this->getApi()->request('GET', sprintf('/merchants/%s/checkouts/%s', $store_id, $private_id));
            $log->write(sprintf('Checkout Info: %s', var_export($info, true)));

            // Check Order ID
            if (isset($info['data']['reference'])) {
                $order_id = $info['data']['reference'];
                throw new Exception('This order already assigned to order_id: ' . $order_id);
            }

            // Check status
            if (!in_array($info['data']['status'], ['PurchaseCompleted'])) {
                throw new Exception('Purchase not Completed: ' . $info['data']['status']);
            }

            $quote_data['payment_method'] = [
                'title' => 'Collector Checkout',
                'code' => 'collector'
            ];

            // Get Country Info
            $country_code = $info['data']['countryCode'];
            $country_info = $this->getHelper()->getCountryByCode($country_code);

            // Fill customer data
            if ($info['data']['customerType'] === 'PrivateCustomer') {
                // Personal Customer
                $billing_address = !empty($info['data']['customer']['billingAddress'])
                    ? $info['data']['customer']['billingAddress'] : $info['data']['customer']['deliveryAddress'];
                $shipping_address = $info['data']['customer']['deliveryAddress'];

                // Customer
                $quote_data['customer'] = [
                    //'customer_id' => 0,
                    //'customer_group_id' => 0,
                    'firstname' => $billing_address['firstName'],
                    'lastname' => $billing_address['lastName'],
                    'email' => $info['data']['customer']['email'],
                    'telephone' => $info['data']['customer']['mobilePhoneNumber'],
                    'fax' => '',
                    'custom_field' => '',
                ];

                // Payment Details
                $quote_data['payment_address'] = [
                    'firstname' => $billing_address['firstName'],
                    'lastname' => $billing_address['lastName'],
                    'company' => '',
                    'address_1' => $billing_address['address'],
                    'address_2' => $billing_address['address2'],
                    'city' => $billing_address['city'],
                    'postcode' => $billing_address['postalCode'],
                    'zone' => '',
                    'zone_id' => 0,
                    'country' => $billing_address['country'],
                    'country_id' => $country_info['country_id'],
                    'address_format' => $country_info['address_format'],
                    'custom_field' => []
                ];

                // Shipping Details
                $quote_data['shipping_address'] = [
                    'firstname' => $shipping_address['firstName'],
                    'lastname' => $shipping_address['lastName'],
                    'company' => '',
                    'address_1' => $shipping_address['address'],
                    'address_2' => $shipping_address['address2'],
                    'city' => $shipping_address['city'],
                    'postcode' => $shipping_address['postalCode'],
                    'zone' => '',
                    'zone_id' => 0,
                    'country' => $shipping_address['country'],
                    'country_id' => $country_info['country_id'],
                    'address_format' => $country_info['address_format'],
                    'custom_field' => []
                ];
            } else {
                // Corporate Customer
                $billing_address = !empty($info['data']['businessCustomer']['invoiceAddress'])
                    ? $info['data']['businessCustomer']['invoiceAddress'] : $info['data']['businessCustomer']['deliveryAddress'];
                $shipping_address = $info['data']['businessCustomer']['deliveryAddress'];

                // Customer
                $quote_data['customer'] = [
                    //'customer_id' => 0,
                    //'customer_group_id' => 0,
                    'firstname' => $info['data']['businessCustomer']['firstName'],
                    'lastname' => $info['data']['businessCustomer']['lastName'],
                    'email' => $info['data']['businessCustomer']['email'],
                    'telephone' => $info['data']['businessCustomer']['mobilePhoneNumber'],
                    'fax' => '',
                    'custom_field' => '',
                ];

                // Payment Details
                $quote_data['payment_address'] = [
                    'firstname' => $info['data']['businessCustomer']['firstName'],
                    'lastname' => $info['data']['businessCustomer']['lastName'],
                    'company' => $billing_address['companyName'],
                    'address_1' => $billing_address['address'],
                    'address_2' => $billing_address['address2'],
                    'city' => $billing_address['city'],
                    'postcode' => $billing_address['postalCode'],
                    'zone' => '',
                    'zone_id' => 0,
                    'country' => $billing_address['country'],
                    'country_id' => $country_info['country_id'],
                    'address_format' => $country_info['address_format'],
                    'custom_field' => []
                ];

                // Shipping Details
                $quote_data['shipping_address'] = [
                    'firstname' => $info['data']['businessCustomer']['firstName'],
                    'lastname' => $info['data']['businessCustomer']['lastName'],
                    'company' => $shipping_address['companyName'],
                    'address_1' => $shipping_address['address'],
                    'address_2' => $shipping_address['address2'],
                    'city' => $shipping_address['city'],
                    'postcode' => $shipping_address['postalCode'],
                    'zone' => '',
                    'zone_id' => 0,
                    'country' => $shipping_address['country'],
                    'country_id' => $country_info['country_id'],
                    'address_format' => $country_info['address_format'],
                    'custom_field' => []
                ];
            }

            // Email Required for addOrderHistory()
            if (empty($quote_data['customer']['email'])) {
                $quote_data['customer']['email'] = token(10) . '@noemail.fake';
            }

            // Get order total
            $value = $this->currency->getValue($quote_data['currency_code']);
            if (!$value) {
                $value = 1;
            }
            $quote_data['total'] = $info['data']['order']['totalAmount'] / $value;

            // Create Order
            unset($quote_data['products']);
            $order_id = $this->getHelper()->addOrder($quote_data);
            if (!$order_id) {
                $log->write('Failed to place order');
            }

            $log->write(sprintf('Placed Order: %s', $order_id));

            // @todo Add items in order_product table
            // @todo Add fees/shipping in order_total table
            // @todo Add vouchers in order_voucher

            // Assign Order Reference
            $params = [
                'Reference' => $order_id
            ];
            $this->getApi()->request('PUT', sprintf('/merchants/%s/checkouts/%s/reference', $store_id, $private_id), $params);

            $settings = $this->getSettings();
            $purchaseStatus = isset($info['data']['purchase']) ? $info['data']['purchase']['result'] : null;

            // Update
            $this->getPayments()->update($payment['id'], [
                'order_id' => $order_id,
                'status' => (string) $info['data']['status'],
                'paymentName' => (string) $info['data']['paymentName'],
                'info' => json_encode($info),
                'purchaseIdentifier' => isset($info['data']['purchase']) ? $info['data']['purchase']['purchaseIdentifier'] : null,
                'purchaseStatus' => $purchaseStatus
            ]);

	        // B2B: Add delivery Contact Information
	        if (isset($info['data']['businessCustomer']) && isset($info['data']['businessCustomer']['deliveryContactInformation'])) {
		        $order_status_id = $this->config->get('config_order_status_id');
		        $email = $info['data']['businessCustomer']['deliveryContactInformation']['email'];
		        $phone = $info['data']['businessCustomer']['deliveryContactInformation']['mobilePhoneNumber'];
		        $message = sprintf('Delivery Contact Information. Email: %s Phone: %s', $email, $phone);
		        $this->model_checkout_order->addOrderHistory($order_id, $order_status_id, $message, false);
	        }

            switch ($purchaseStatus) {
                case 'Preliminary':
                    // The invoice is pending and waiting for activation by Merchant.
                    $order_status_id = $settings['collector_order_status_preliminary_id'];
                    if (empty($order_status_id)) {
                        $order_status_id = $this->config->get('config_order_status_id');
                    }

                    $this->model_checkout_order->addOrderHistory($order_id, $order_status_id, 'The invoice is pending and waiting for activation by Merchant.', false);
                    break;
                case 'OnHold':
                    // The invoice is waiting for the anti-fraud callback.
                    $order_status_id = $settings['collector_order_status_pending_id'];
                    if (empty($order_status_id)) {
                        $order_status_id = $this->config->get('config_order_status_id');
                    }

                    $this->model_checkout_order->addOrderHistory($order_id, $order_status_id, 'The invoice is waiting for the anti-fraud callback.', false);
                    break;
                case 'Signing':
                    // The invoice is waiting for electronic signing by customer(eg. by Mobile-BankId).
                    $order_status_id = $settings['collector_order_status_pending_id'];
                    if (empty($order_status_id)) {
                        $order_status_id = $this->config->get('config_order_status_id');
                    }

                    $this->model_checkout_order->addOrderHistory($order_id, $order_status_id, 'The invoice is waiting for electronic signing by customer', false);
                    break;
                default:
                    // Add Order History
                    $order_status_id = $settings['collector_order_status_rejected_id'];
                    if (empty($order_status_id)) {
                        $order_status_id = $this->config->get('config_order_status_id');
                    }

                    $this->model_checkout_order->addOrderHistory($order_id, $order_status_id, 'Purchase status: ' . $purchaseStatus, true);
                    break;
            }
        } catch (Exception $e) {
            $log->write(sprintf('Error: %s', $e->getMessage()));

            http_response_code(500);
            $this->response->setOutput('FAILURE');
            return;
        }

        http_response_code(200);
        $this->response->setOutput('OK');
    }

    /**
     * Invoice Status Action.
     */
    public function invoicestatus()
    {
        $settings = $this->getSettings();
        $log = new Log('collector_invoice_status.log');
        $token = isset($this->request->get['token']) ? $this->request->get['token'] : null;
        $InvoiceNo = isset($this->request->get['InvoiceNo']) ? $this->request->get['InvoiceNo'] : null;
        $OrderNo = isset($this->request->get['OrderNo']) ? $this->request->get['OrderNo'] : null;
        $InvoiceStatus = isset($this->request->get['InvoiceNo']) ? (int)$this->request->get['InvoiceStatus'] : null;

        try {
            if ($token !== $settings['collector_url_token']) {
                throw new Exception('Token verification failed');
            }

            if (empty($InvoiceNo) || empty($OrderNo) || empty($InvoiceStatus)) {
                throw new Exception('Request failed');
            }

            // Wait for order place
            $time = 0;
            do {
                $payment = $this->getPayments()->getByOrderId($OrderNo);
                $time++;
                sleep(1);
                if ($time > 30) {
                    break;
                }
            } while (!$payment || empty($payment['order_id']));

            if (!$payment) {
                throw new Exception('Failed to get payment data');
            }

            if (!in_array($InvoiceStatus, [0, 1, 5])) {
                throw new Exception('Invalid invoice status: ' . $InvoiceStatus);
            }


            $order_id = $payment['order_id'];
            $new_status_str = '';
            if ($InvoiceStatus == 1) {
                $order_status_id = $settings['collector_order_status_preliminary_id'];
                $new_status_str = 'Preliminary';

	            $this->getPayments()->update($payment['id'], [
		            'purchaseStatus' => 'Preliminary'
	            ]);
            } elseif ($InvoiceStatus == 0) {
                $order_status_id = $settings['collector_order_status_pending_id'];
                $new_status_str = 'OnHold';

	            $this->getPayments()->update($payment['id'], [
		            'purchaseStatus' => 'OnHold'
	            ]);
            } elseif ($InvoiceStatus == 5) {
                $order_status_id = $settings['collector_order_status_rejected_id'];
                $new_status_str = 'Rejected';

	            $this->getPayments()->update($payment['id'], [
		            'purchaseStatus' => 'Rejected'
	            ]);
            }

            if (empty($order_status_id)) {
                $order_status_id = $this->config->get('config_order_status_id');
            }

	        $this->load->model('checkout/order');
            $this->model_checkout_order->addOrderHistory($order_id, $order_status_id, sprintf('Order is now %s', $new_status_str), true);
            $log->write('Anti Fraud callback by Collector: Internal (' . $InvoiceNo . ') Status changed to ID: ' . $new_status_str);
        } catch (Exception $e) {
            $log->write(sprintf('Error: %s', $e->getMessage()));

            http_response_code(500);
            $this->response->setOutput('FAILURE');
            return;
        }

        http_response_code(200);
        $this->response->setOutput('OK');
    }

    /**
     * Cart Action
     */
    public function cart()
    {
        // Get products
        $cart_items = $this->getHelper()->getCartItems();
        $products = array_filter($cart_items, function ($value, $key) {
            return $value['type'] === 'product';
        }, ARRAY_FILTER_USE_BOTH);
        foreach ($products as &$product) {
            $_data = $this->getHelper()->getProductData($product['product_id']);
            $product = array_merge($_data, $product);
        }

        $data = [
            'view' => $this->getView(),
            'products' => $products
        ];
        $this->response->setOutput($this->load->view('checkout/collector/cart', $data));
    }

    /**
     * Totals Action
     */
    public function totals()
    {
        if (isset($this->session->data['payment_address']['country_id'])) {
            $country_id = $this->session->data['payment_address']['country_id'];
        } else {
            $country_id = $this->config->get('config_country_id');
        }

        $data = [
            'view' => $this->getView(),
            'totals' => $this->getHelper()->getCartTotals([
                'country_id' => $country_id,
                'zone_id' => 0
            ])
        ];

        $this->response->setOutput($this->load->view('checkout/collector/totals', $data));
    }

    /**
     * Set Customer Type Action
     */
    public function set_customer_type()
    {
	    $customer_type = isset($this->request->post['customer_type']) ? $this->request->post['customer_type'] : 'private';

	    // Get current Country
	    $this->load->model('localisation/country');
	    $country_id = isset($this->session->data['payment_address']) ?
		    $this->session->data['payment_address']['country_id'] : $this->config->get('config_country_id');

	    // Load Country info
	    $country = $this->model_localisation_country->getCountry($country_id);

	    // Verify that selected country_id is possible for use
	    if (!in_array($country['iso_code_2'], ['SE', 'NO'])) {
		    // Get Sweden as default
		    $country = array_filter($this->getHelper()->getCountries(), function($value, $key) {
			    return in_array($value['iso_code_2'], ['SE']);
		    }, ARRAY_FILTER_USE_BOTH);
		    $country = array_shift($country);
	    }

	    // Check Store ID is defined
	    $store_id = $this->getMerchantStoreId($customer_type, strtolower($country['iso_code_2']));
	    if (empty($store_id)) {
		    $this->response->addHeader('Content-Type: application/json');
		    $this->response->setOutput(json_encode(['success' => false, 'message' => $this->getView()->__('Unable to switch customer type')]));
		    return;
	    }

	    // Unset quote
	    unset($this->session->data['collector_quote_id']);
	    unset($this->session->data['collector_private_id']);

        $this->session->data['collector_customer_type'] = $customer_type;
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode(['success' => true]));
    }

    /**
     * Shipping Methods Action
     */
    public function shipping_methods()
    {
        $this->load->model('localisation/country');
        $country_id = isset($this->request->get['country_id']) ? $this->request->get['country_id'] : null;
        if (!$country_id) {
            $country_id = $this->config->get('config_country_id');
        }

        // Verify Country Id
        $country = $this->model_localisation_country->getCountry($country_id);
        if (!$country) {
            $country_id = $this->config->get('config_country_id');
        }

        // Set Country Id
        $this->session->data['payment_address']['country_id'] = $country_id;
        $this->session->data['shipping_address']['country_id'] = $country_id;

        $shipping_method_code = false;
        if (isset($this->session->data['shipping_method'])) {
            $shipping_method_code = $this->session->data['shipping_method']['code'];
        }

        // Fetch shipping methods
        $address = [
            'country_id' => $country_id,
            'zone_id' => 0
        ];
        $methods = $this->getHelper()->getShippingMethods($address);



        $data = [
            'view' => $this->getView(),
            'methods' => $methods,
            'code' => $shipping_method_code
        ];

        $this->response->setOutput($this->load->view('checkout/collector/shipping_methods', $data));
    }

    /**
     * Cart Update Action
     */
    public function cart_update()
    {
        $cart_id = $this->request->post['cart_id'];
        $qty = (int) $this->request->post['qty'];

        if ($qty > 0) {
            $this->cart->update($cart_id, $qty);

            $products = $this->getHelper()->getCartItems();
            $products = array_filter($products, function ($value, $key) use ($cart_id) {
                return $value['cart_id'] === $cart_id;
            }, ARRAY_FILTER_USE_BOTH);
            $product = array_shift($products);

            $json = [
                'action' => 'update',
                'cart_id' => $cart_id,
                'qty' => $qty,
                'unit_price' => $this->getView()->format($product['price_with_tax'] / $product['qty']),
                'total_price' => $this->getView()->format($product['price_with_tax'])
            ];
        } else {
            $this->cart->remove($cart_id);
            $json = [
                'action' => 'remove',
                'cart_id' => $cart_id
            ];
        }

        unset($this->session->data['shipping_method']);
        unset($this->session->data['shipping_methods']);
        unset($this->session->data['payment_method']);
        unset($this->session->data['payment_methods']);
        unset($this->session->data['reward']);

        // Update Quote
        $this->_makeQuote();

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    /**
     * Set Shipping Method Action
     */
    public function set_shipping_method()
    {
        // Get Country Id
        if (isset($this->session->data['shipping_address']['country_id'])) {
            $country_id = $this->session->data['shipping_address']['country_id'];
        } elseif (isset($this->session->data['payment_address']['country_id'])) {
            $country_id = $this->session->data['payment_address']['country_id'];
        } else {
            $country_id = $this->config->get('config_country_id');
        }

        // Verify Country Id
        $this->load->model('localisation/country');
        $country = $this->model_localisation_country->getCountry($country_id);
        if (!$country) {
            $country_id = $this->config->get('config_country_id');
        }

        // Fetch shipping methods
        $address = [
            'country_id' => $country_id,
            'zone_id' => 0
        ];
        $methods = $this->getHelper()->getShippingMethods($address);

        // Apply Shipping Method
        $shipping_method = htmlentities($this->request->post['shipping_method']);
        $shipping = explode('.', $shipping_method);

        unset($this->session->data['shipping_method']);
        if (isset($shipping[0]) && isset($shipping[1])) {
            $this->session->data['shipping_method'] = $this->session->data['shipping_methods'][$shipping[0]]['quote'][$shipping[1]];
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode(['success' => true]));
    }

    /**
     * Collector Update Action
     */
    public function collector_update()
    {
        $quote_id = isset($this->session->data['collector_quote_id']) ? $this->session->data['collector_quote_id'] : null;

        $payment = $this->getPayments()->getByQuoteId($quote_id);
        if (!$payment) {
            $this->response->addHeader('Content-Type: application/json');
            $this->response->setOutput(json_encode(['success' => false, 'message' => 'Invalid Quote Id']));
            return;
        }

        $private_id = $payment['private_id'];
        $store_id = $payment['store_id'];
        $cart_items = $this->getHelper()->getCartItems();
        $collector_items = $this->getHelper()->getCollectorItems($cart_items, $this->getCustomerType());
        if (count($collector_items['items']) === 0) {
            $this->response->addHeader('Content-Type: application/json');
            $this->response->setOutput(json_encode(['success' => true, 'redirect' => $this->url->link('checkout/cart')]));
            return;
        }

        // Update Collector
        try {
            // Update items
            $params = [
                'items' => $collector_items['items'],
            ];
            $result = $this->getApi()->request('PUT', sprintf('/merchants/%s/checkouts/%s/cart', $store_id, $private_id), $params);

            // Update fees
            $params = $collector_items['fees'];

            // See https://checkout-documentation.collector.se/#update-fees
            // To set the shipping fee, add a shipping object to the fees object of the request.
            // The shipping object can be null to remove the shipping fee for the current checkout.
            //$params = [
            //    'shipping' => null
            //];
            //$result = $this->getApi()->request('PUT', sprintf('/merchants/%s/checkouts/%s/fees', $store_id, $private_id), $params);
            if ($this->cart->hasShipping()) {
                $shipping = $this->getHelper()->getCartShipping();
                if ($shipping['unit_price'] < 0.1) {
                    $params['shipping'] = null;
                }
            } else {
                $params['shipping'] = null;
            }

            $result = $this->getApi()->request('PUT', sprintf('/merchants/%s/checkouts/%s/fees', $store_id, $private_id), $params);
        } catch (Exception $e) {
            $this->response->addHeader('Content-Type: application/json');
            $this->response->setOutput(json_encode(['success' => false, 'message' => $e->getMessage()]));
            return;
        }

        // Update Quote
        $this->_makeQuote();

        // Update cart items
        $this->getPayments()->update($payment['id'], [
            'cart_items' => json_encode($cart_items),
        ]);

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode(['success' => true]));
    }

    /**
     * Instant Purchase Action
     */
    public function instant_purchase()
    {
        $settings = $this->getSettings();
        $customer_token = $this->request->post['customer_token'];
        $product_id = $this->request->post['product_id'];
        $qty = $this->request->post['qty'];

        // Update Cart
        $this->cart->clear();
        $this->cart->add($product_id, $qty);

        // Settings
        $country_code = $settings['collector_ic_country_code'];
        $store_id = $settings['collector_ic_store_id'];

        // Init Collector
        $cart_items = $this->getHelper()->getCartItems();
        $collector_items = $this->getHelper()->getCollectorItems($cart_items, 'private');

        // Update Quote
        $quote = $this->_makeQuote();

        $params = [
            'customerToken' => $customer_token,
            'storeId' => $store_id,
            'countryCode' => $country_code,
            'redirectPageUri' => $this->getView()->url('checkout/collector/success'),
            'merchantTermsUri' => $settings['collector_merchant_terms_url'],
            'notificationUri' => $this->getView()->url('checkout/collector/ipn', ['token' => $quote['token']]),
            'cart' => [
                'items' => $collector_items['items']
            ]
        ];
        if (count($collector_items['fees']) > 0) {
            $params['fees'] = $collector_items['fees'];
        }

        // Request
        try {
            $result = $this->getApi()->request('POST', '/instantpurchase', $params);
        } catch (Exception $e) {
            $this->response->addHeader('Content-Type: application/json');
            $this->response->setOutput(json_encode(
                [
                    'success' => false,
                    'message' => $e->getMessage()
                ]
            ));
            return;
        }

        // Store privateId in session
        $this->session->data['collector_private_id'] = $result['data']['privateId'];

        // Save in db
        $this->getPayments()->add([
            'quote_id' => $quote['quote_id'],
            'private_id' => $result['data']['privateId'],
            'public_token' => $result['data']['publicToken'],
            'cart_items' => json_encode($cart_items),
            'expiresAt' => $result['data']['expiresAt'],
            'country_code' => $country_code,
            'store_id' => $store_id
        ]);

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode(
            [
                'success' => true,
                'public_token' => $result['data']['publicToken']
            ]
        ));
    }

    /**
     * Instant Purchase Update Action
     */
    public function instant_purchase_update()
    {
        $product_id = $this->request->post['product_id'];
        $qty = $this->request->post['qty'];

        // Update Cart
        $this->cart->clear();
        $this->cart->add($product_id, $qty);

        $this->collector_update();
    }

    /**
     * Make Quote record
     * @return array
     */
    protected function _makeQuote()
    {
        $quote_id = isset($this->session->data['collector_quote_id']) ? $this->session->data['collector_quote_id'] : null;
        $visitor_id = $this->getVisitors()->getCurrentVisitorId();
        $quote_data = $this->getHelper()->getQuoteData();
        if (empty($quote_id)) {
            $quote_id = $this->getQuote()->add($visitor_id, $quote_data);
            $this->session->data['collector_quote_id'] = $quote_id;
        } else {
            $this->getQuote()->update($quote_id, $quote_data);
        }

        return $this->getQuote()->getById($quote_id);
    }

    /**
     * Clear Cart
     */
    protected function _clearCart()
    {
        $this->cart->clear();
        unset($this->session->data['collector_quote_id']);
	    unset($this->session->data['collector_private_id']);
    }
}
