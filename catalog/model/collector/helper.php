<?php

class ModelCollectorHelper extends Model {
    /**
     * @return ModelCollectorView
     */
    protected function getView()
    {
        $this->load->model('collector/view');
        return $this->model_collector_view;
    }

    /**
     * @param $key
     * @param $default
     *
     * @return mixed
     */
    protected function _session($key, $default = null)
    {
        if (!isset($this->session->data[$key])) {
            return $default;
        }

        return $this->session->data[$key];
    }

    /**
     * Get Product Data
     * @see ModelCatalogProduct::getProduct()
     * @param $product_id
     *
     * @return bool|array
     */
    public function getProductData($product_id)
    {
        $this->load->model('catalog/product');
        $this->load->model('tool/image');

        $product = $this->model_catalog_product->getProduct($product_id);
        if (!$product) {
            return false;
        }

        // Prepare thumb
        $product['thumb'] = '';
        if (!empty($product['image'])) {
            $product['thumb'] = $this->model_tool_image->resize(
                $product['image'],
                $this->config->get($this->config->get('config_theme') . '_image_cart_width'),
                $this->config->get($this->config->get('config_theme') . '_image_cart_height')
            );
        }

        // Prepare href
        $product['href'] = html_entity_decode($this->url->link('product/product', 'product_id=' . $product['product_id']));
        return $product;
    }

    /**
     * Get Cart Products
     * @return array
     */
    public function getCartItems() {
        //$this->load->model('tool/image');
        $currency_code = $this->_session('currency');
        $currency_value = $this->currency->getValue($currency_code);

        // @todo Define shipping/payment address
        //$this->tax->setShippingAddress($country_id, $zone_id);

        $lines = [];

        // Get Products
        foreach ($this->cart->getProducts() as $product) {
            // @todo Check $product['stock']
            // : !(!$this->config->get('config_stock_checkout') || $this->config->get('config_stock_warning'))
            //if (!$product['stock']) {
            //    continue;
            //}

            // Prepare name
            $product_name = $product['name'];
            if ($product['model']) {
                $product_name .= ' (' . $product['model'] . ')';
            }
            if ($product['option']) {
                $options = '';
                foreach ($product['option'] as $option) {
                    $options .= sprintf('%s: %s', $option['name'], $option['value']);
                }

                $product_name .= ' (' . $options . ')';
            }

            $qty = $product['quantity'];
            $price = $this->currency->format($product['price'] * $qty, $currency_code, $currency_value, false);
            $priceWithTax = $this->tax->calculate($price, $product['tax_class_id'], 1);
            $taxPrice = $priceWithTax - $price;
            $taxPercent = ($taxPrice > 0) ? round(100 / (($priceWithTax - $taxPrice) / $taxPrice)) : 0;

            $cart_id = $product['cart_id'];
            $lines[] = [
                'type' => 'product',
                'cart_id' => $cart_id,
                'product_id' => $product['product_id'],
                'name' => $product_name,
                'qty' => $qty,
                'price_with_tax' => sprintf("%.2f", $priceWithTax),
                'price_without_tax' => sprintf("%.2f", $price),
                'tax_price' => sprintf("%.2f", $taxPrice),
                'tax_percent' => sprintf("%.2f", $taxPercent)
            ];
        }

        $averageTax = [];
        if ($this->cart->hasShipping() && isset($this->session->data['shipping_method'])) {
            $shipping_method = $this->_session('shipping_method');

            $shipping = $this->currency->format($shipping_method['cost'], $currency_code, $currency_value, false);
            $shippingWithTax = $this->tax->calculate($shipping, $shipping_method['tax_class_id'], 1);
            $shippingTax = $shippingWithTax - $shipping;
            $shippingTaxPercent = $shipping != 0 ? (int)((100 * ($shippingTax) / $shipping)) : 0;
            $averageTax[] = $shippingTaxPercent;

            $lines[] = [
                'type' => 'shipping',
                'cart_id' => '',
                'product_id' => '',
                'name' => $shipping_method['title'],
                'qty' => 1,
                'price_with_tax' => sprintf("%.2f", $shippingWithTax),
                'price_without_tax' => sprintf("%.2f", $shipping),
                'tax_price' => sprintf("%.2f", $shippingTax),
                'tax_percent' => sprintf("%.2f", $shippingTaxPercent)
            ];
        }

        // @todo Add vouchers
        // @todo Add coupons
        // @todo Add fees

        return $lines;
    }

    public function getCartShipping()
    {
        if ($this->cart->hasShipping() && isset($this->session->data['shipping_method'])) {
            $shipping_method = $this->_session('shipping_method');

            $currency_code = $this->_session('currency');
            $currency_value = $this->currency->getValue($currency_code);

            $price = $this->currency->format($shipping_method['cost'], $currency_code, $currency_value, false);
            $priceWithTax = $this->tax->calculate($price, $shipping_method['tax_class_id'], 1);
            $taxPrice = $priceWithTax - $price;
            $taxPercent = ($taxPrice > 0) ? round(100 / (($priceWithTax - $taxPrice) / $taxPrice)) : 0;

            return [
                'code' => $shipping_method['code'],
                'name' => $shipping_method['title'],
                'unit_price' => $priceWithTax,
                'price' => $priceWithTax,
                'vat' => $taxPercent,
            ];
        }

        return false;
    }

    public function getCartTotals($address)
    {
        $this->tax->setShippingAddress($address['country_id'], $address['zone_id']);

        $currency_code = $this->_session('currency');
        $currency_value = $this->currency->getValue($currency_code);

        $totals = array();
        $taxes = $this->cart->getTaxes();
        $total = 0;

        // Because __call can not keep var references so we put them into an array.
        $total_data = array(
            'totals' => &$totals,
            'taxes'  => &$taxes,
            'total'  => &$total
        );

        $this->load->model('extension/extension');

        $sort_order = array();

        $results = $this->model_extension_extension->getExtensions('total');

        foreach ($results as $key => $value) {
            $sort_order[$key] = $this->config->get($value['code'] . '_sort_order');
        }
        array_multisort($sort_order, SORT_ASC, $results);

        foreach ($results as $result) {
            if ($this->config->get($result['code'] . '_status')) {
                $this->load->model('extension/total/' . $result['code']);

                // We have to put the totals in an array so that they pass by reference.
                $this->{'model_extension_total_' . $result['code']}->getTotal($total_data);
            }
        }

        $sort_order = array();
        foreach ($totals as $key => &$value) {
            $value['total'] = $this->currency->format($value['value'], $currency_code, $currency_value, false);
            $sort_order[$key] = $value['sort_order'];
        }
        array_multisort($sort_order, SORT_ASC, $totals);

        return $totals;
    }

    public function getShippingMethods($address)
    {
        // Override $_POST values
        $this->request->post['country_id'] = $address['country_id'];
        $this->request->post['zone_id'] = $address['zone_id'];

        // Shipping Methods
        $method_data = array();

        $this->load->model('extension/extension');

        $results = $this->model_extension_extension->getExtensions('shipping');

        foreach ($results as $result) {
            if ($this->config->get($result['code'] . '_status')) {
                $this->load->model('extension/shipping/' . $result['code']);

                $quote = $this->{'model_extension_shipping_' . $result['code']}->getQuote($address);

                if ($quote) {
                    $method_data[$result['code']] = array(
                        'title'      => $quote['title'],
                        'quote'      => $quote['quote'],
                        'sort_order' => $quote['sort_order'],
                        'error'      => $quote['error']
                    );
                }
            }
        }

        $sort_order = array();
        foreach ($method_data as $key => $value) {
            $sort_order[$key] = $value['sort_order'];
        }
        array_multisort($sort_order, SORT_ASC, $method_data);

        $this->session->data['shipping_methods'] = $method_data;

        return $method_data;
    }

    public function getCountries()
    {
        $this->load->model('localisation/country');
        return $this->model_localisation_country->getCountries();
    }

    public function getZonesByCountry($country_id)
    {
        $this->load->model('localisation/zone');
        return $this->model_localisation_zone->getZonesByCountryId($country_id);
    }

    public function getCollectorItems($cart_items, $customer_type = 'private')
    {
        $items = [];
        $fees = [];
        foreach ($cart_items as $item) {
            if ($item['type'] === 'product') {
                $items[] = [
                    'id' => $item['cart_id'],
                    'description' => $item['name'],
                    'unitPrice' => sprintf("%.2f", $item['price_with_tax'] / $item['qty']),
                    'quantity' => $item['qty'],
                    'vat' => sprintf("%.2f",$item['tax_percent']),
                ];
            } elseif ($item['type'] === 'shipping') {
                // One one shipping
                $fees = [
                    'shipping' => [
                        'id' => 'shipping',
                        'description' => $item['name'],
                        'unitPrice' => sprintf("%.2f",$item['price_with_tax'] / $item['qty']),
                        'quantity' => $item['qty'],
                        'vat' => sprintf("%.2f", $item['tax_percent']),
                    ]
                ];
            } else {
                // @todo Add fees
            }
        }

        $settings = $this->getSettings();
        $suffix = $customer_type === 'private' ? 'b2c' : 'b2b';
        if ($settings['collector_invoice_fee_' . $suffix] > 0) {
            $fees['directinvoicenotification'] = [
                'id' => 'directinvoice001',
                'description' => $this->getView()->__('Invoice fee (incl. VAT)'),
                'unitPrice' => sprintf("%.2f", $settings['collector_invoice_fee_' . $suffix]),
                'quantity' => 1,
                'vat' => sprintf("%.2f", $settings['collector_invoice_fee_vat_' . $suffix]),
            ];
        }

        return ['items' => $items, 'fees' => $fees];
    }

    /**
     * Get Quote Data for Order Creation
     * @see addOrder()
     * @return array
     */
    public function getQuoteData()
    {

        $data = [
            // Currency
            'currency_id' => $this->currency->getId($this->_session('currency')),
            'currency_code' => $this->_session('currency'),
            'currency_value' => $this->currency->getValue($this->_session('currency')),

            // Customer Details
            //'customer' => (array) $this->_session('customer'),
            'customer' => [
                'customer_id' => $this->customer->isLogged() ? $this->customer->getId() : 0,
                'customer_group_id' => $this->customer->isLogged() ? $this->customer->getGroupId() : 0,
                'firstname' => $this->customer->isLogged() ? $this->customer->getFirstName() : '',
                'lastname' => $this->customer->isLogged() ? $this->customer->getLastName() : '',
                'email' => $this->customer->isLogged() ? $this->customer->getEmail() : '',
                'telephone' => $this->customer->isLogged() ? $this->customer->getTelephone() : '',
                'fax' => $this->customer->isLogged() ? $this->customer->getFax() : '',
                'custom_field' => '',
            ],

            // Payment Details
            'payment_address' => (array) $this->_session('payment_address'),
            'payment_method' => (array) $this->_session('payment_method'),

            // Taxes
            'taxes' => $this->cart->getTaxes()
        ];

        // Shipping Details
        if ($this->cart->hasShipping()) {
            $data = array_merge($data, [
                'shipping_address' => (array) $this->_session('shipping_address'),
                'shipping_method' => (array) $this->_session('shipping_method'),
            ]);
        }

        // Products
        $data['products'] = [];
        foreach ($this->cart->getProducts() as $product) {
            $option_data = [];
            foreach ($product['option'] as $option) {
                $option_data[] = [
                    'product_option_id'       => $option['product_option_id'],
                    'product_option_value_id' => $option['product_option_value_id'],
                    'option_id'               => $option['option_id'],
                    'option_value_id'         => $option['option_value_id'],
                    'name'                    => $option['name'],
                    'value'                   => $option['value'],
                    'type'                    => $option['type']
                ];
            }

            $data['products'][] = [
                'product_id' => $product['product_id'],
                'name'       => $product['name'],
                'model'      => $product['model'],
                'option'     => $option_data,
                'download'   => $product['download'],
                'quantity'   => $product['quantity'],
                'subtract'   => $product['subtract'],
                'price'      => $product['price'],
                'total'      => $product['total'],
                'tax'        => $this->tax->getTax($product['price'], $product['tax_class_id']),
                'reward'     => $product['reward']
            ];
        }

        // Gift Voucher
        $data['vouchers'] = [];
        foreach ((array) $this->_session('vouchers') as $voucher) {
            $data['vouchers'][] = [
                'description'      => $voucher['description'],
                'code'             => token(10),
                'to_name'          => $voucher['to_name'],
                'to_email'         => $voucher['to_email'],
                'from_name'        => $voucher['from_name'],
                'from_email'       => $voucher['from_email'],
                'voucher_theme_id' => $voucher['voucher_theme_id'],
                'message'          => $voucher['message'],
                'amount'           => $voucher['amount']
            ];
        }

        return $data;
    }

    /**
     * Create Order
     * @param $data
     * @param bool $order_status_id
     *
     * @return mixed
     */
    public function addOrder($data, $order_status_id = false) {
        // Default data
        $default = [
            // Store Details
            'invoice_prefix' => $this->config->get('config_invoice_prefix'),
            'store_id' => $this->config->get('config_store_id'),
            'store_name' => $this->config->get('config_name'),
            'store_url' => $this->config->get('config_url'),
            'language_id' => $this->config->get('config_language_id'),

            // Currency
            'currency_id' => $this->currency->getId($data['currency']),
            'currency_code' => $data['currency'],
            'currency_value' => $this->currency->getValue($data['currency']),

            // Customer Browser Data
            'ip' => $this->request->server['REMOTE_ADDR'],
            'forwarded_ip' => '', // @todo
            'user_agent' => $this->request->server['HTTP_USER_AGENT'],
            'accept_language' => $this->request->server['HTTP_ACCEPT_LANGUAGE'],

            // Customer Details
            'customer' => [
                'customer_id' => 0,
                'customer_group_id' => 0,
                'firstname' => '',
                'lastname' => '',
                'email' => '',
                'telephone' => '',
                'fax' => '',
                'custom_field' => '',
            ],

            // Payment Method
            'payment_method' => [
                'title' => '',
                'code' => ''
            ],

            // Shipping Method
            'shipping_method' => [
                'title' => '',
                'code' => ''
            ],

            // Payment Details
            'payment_address' => [
                'firstname' => '',
                'lastname' => '',
                'company' => '',
                'address_1' => '',
                'address_2' => '',
                'city' => '',
                'postcode' => '',
                'zone' => '',
                'zone_id' => 0,
                'country' => '',
                'country_id' => 0,
                'address_format' => '',
                'custom_field' => []
            ],

            // Shipping Details
            'shipping_address' => [
                'firstname' => '',
                'lastname' => '',
                'company' => '',
                'address_1' => '',
                'address_2' => '',
                'city' => '',
                'postcode' => '',
                'zone' => '',
                'zone_id' => 0,
                'country' => '',
                'country_id' => 0,
                'address_format' => '',
                'custom_field' => []
            ],

            'products' => [],
            'vouchers' => [],
            'taxes' => [],
            'comment' => '',

            // Marketing
            'affiliate_id' => 0,
            'commission' => 0,
            'marketing_id' => 0,
            'tracking' => '',
        ];
        $data = array_merge($default, $data);

        $order_data = [
            // Store Details
            'invoice_prefix' => $data['invoice_prefix'],
            'store_id' => $data['store_id'],
            'store_name' => $data['store_name'],
            'store_url' => $data['store_url'],
            'language_id' => $data['language_id'],

            // Currency
            'currency_id' => $data['currency_id'],
            'currency_code' => $data['currency_code'],
            'currency_value' => $data['currency_value'],

            // Customer Browser Data
            'ip' => $data['ip'],
            'forwarded_ip' => $data['forwarded_ip'],
            'user_agent' => $data['user_agent'],
            'accept_language' => $data['accept_language'],

            // Customer Details
            'customer_id' => $data['customer']['customer_id'], // @todo Notice
            'customer_group_id' => $data['customer']['customer_group_id'], // @todo Notice
            'firstname' => $data['customer']['firstname'],
            'lastname' => $data['customer']['lastname'],
            'email' => $data['customer']['email'],
            'telephone' => $data['customer']['telephone'],
            'fax' => $data['customer']['fax'],
            'custom_field' => $data['customer']['custom_field'],

            // Payment method
            'payment_method' => $data['payment_method']['title'],
            'payment_code' => $data['payment_method']['code'],

            // Shipping method
            'shipping_method' => $data['shipping_method']['title'],
            'shipping_code' => $data['shipping_method']['code'], // @todo Notice

            // Products
            'products' => $data['products'],
            'vouchers' => $data['vouchers'],

            // Payment Details
            'payment_firstname' => $data['payment_address']['firstname'],
            'payment_lastname' => $data['payment_address']['lastname'],
            'payment_company' => $data['payment_address']['company'],
            'payment_address_1' => $data['payment_address']['address_1'],
            'payment_address_2' => $data['payment_address']['address_2'],
            'payment_city' => $data['payment_address']['city'],
            'payment_postcode' => $data['payment_address']['postcode'],
            'payment_zone' => $data['payment_address']['zone'],
            'payment_zone_id' => $data['payment_address']['zone_id'],
            'payment_country' => $data['payment_address']['country'],
            'payment_country_id' => $data['payment_address']['country_id'],
            'payment_address_format' => $data['payment_address']['address_format'],
            'payment_custom_field' => $data['payment_address']['custom_field'],

            // Shipping Details
            'shipping_firstname' => $data['shipping_address']['firstname'],
            'shipping_lastname' => $data['shipping_address']['lastname'],
            'shipping_company' => $data['shipping_address']['company'],
            'shipping_address_1' => $data['shipping_address']['address_1'],
            'shipping_address_2' => $data['shipping_address']['address_2'],
            'shipping_city' => $data['shipping_address']['city'],
            'shipping_postcode' => $data['shipping_address']['postcode'],
            'shipping_zone' => $data['shipping_address']['zone'],
            'shipping_zone_id' => $data['shipping_address']['zone_id'],
            'shipping_country' => $data['shipping_address']['country'],
            'shipping_country_id' => $data['shipping_address']['country_id'],
            'shipping_address_format' => $data['shipping_address']['address_format'],
            'shipping_custom_field' => $data['shipping_address']['custom_field'],

            // Order Comment
            'comment' => $data['comment'],

            'total' => $data['total'],

            // Marketing
            'affiliate_id' => 0,
            'commission' => 0,
            'marketing_id' => 0,
            'tracking' => '',
        ];

        $this->load->model('checkout/order');
        $this->load->model('extension/extension');

        // @todo Add vouchers


        // Create Order
        //var_dump($order_data); exit();
        $order_id = $this->model_checkout_order->addOrder($order_data);

        return $order_id;
    }

    /**
     * Get Country Info by Code
     * @param $code
     *
     * @return bool|mixed
     */
    public function getCountryByCode($code)
    {
        $query = sprintf('SELECT * FROM `' . DB_PREFIX . 'country` WHERE iso_code_2="%s";',
            $this->db->escape($code)
        );
        $cart = $this->db->query($query);
        if ($cart->num_rows === 0) {
            return false;
        }
        return array_shift($cart->rows);
    }

    /**
     * Get Locale
     * @param $lang
     *
     * @return mixed|string
     */
    public function getLocale($lang)
    {
        $allowedLangs = array(
            'en' => 'en-US',
            'sv' => 'sv-SE',
            'nb' => 'nb-NO',
            'da' => 'da-DK',
            'es' => 'es-ES',
            'de' => 'de-DE',
            'fi' => 'fi-FI',
            'fr' => 'fr-FR',
            'pl' => 'pl-PL',
            'cs' => 'cs-CZ',
            'hu' => 'hu-HU'
        );

        if (isset($allowedLangs[$lang])) {
            return $allowedLangs[$lang];
        }

        return 'en-US';
    }

    /**
     * @return array
     */
    public function getSettings()
    {
        $this->load->model('collector/api');
        return $this->model_collector_api->getSettings();
    }

    /**
     * @return bool
     */
    public function isInstantCheckoutEnabled()
    {
        $settings = $this->getSettings();
        return (bool) $settings['collector_ic_status'];
    }
}
