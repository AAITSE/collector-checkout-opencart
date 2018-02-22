<?php

class ModelCollectorApi extends Model
{
    const BACKEND_API_URL_LIVE = 'https://checkout-api.collector.se';
    const BACKEND_API_URL_TEST = 'https://checkout-api-uat.collector.se';
    const COLLECTOR_BANK_SOAP_LIVE = 'https://ecommerce.collector.se/v3.0/InvoiceServiceV33.svc?wsdl';
    const COLLECTOR_BANK_SOAP_TEST = 'https://ecommercetest.collector.se/v3.0/InvoiceServiceV33.svc?wsdl';

    /**
     * Get Settings
     * @return array
     */
    public function getSettings()
    {
        $default = [
            'collector_status' => 0,
            'collector_store_id' => '',
            'collector_username' => '',
            'collector_sharedkey' => '',
            'collector_mode' => 'live',
            'collector_order_status_accepted_id' => 0,
            'collector_order_status_preliminary_id' => 0,
            'collector_order_status_pending_id' => 0,
            'collector_order_status_rejected_id' => 0,
            'collector_merchant_terms_url' => '',
            'collector_url_token' => '',
            'collector_invoice_fee_b2c' => 0,
            'collector_invoice_fee_vat_b2c' => 0,
            'collector_invoice_fee_b2b' => 0,
            'collector_invoice_fee_vat_b2b' => 0,
            'collector_ic_status' => 0,
            'collector_ic_store_id' => '',
            'collector_ic_country_code' => 'SE'
        ];

        $this->load->model('setting/setting');
        $store_id = $this->config->get('config_store_id');
        $settings = $this->model_setting_setting->getSetting('collector', $store_id);

        return array_merge($default, $settings);
    }

    public function send($correlation_Id, $store_id, $country_code, $invoice_no, $email, $sType)
    {
        $settings = $this->getSettings();
        $soap = new SoapClient($settings['collector_mode'] === 'live' ? self::COLLECTOR_BANK_SOAP_LIVE : self::COLLECTOR_BANK_SOAP_TEST);
        $args = [
            'CorrelationId' => $correlation_Id,
            'CountryCode' => $country_code,
            'Email' => $email,
            'InvoiceDeliveryMethod' => (int)$sType,
            'InvoiceNo'   => $invoice_no,
            'StoreId' => $store_id
        ];

        $headers = [];
        $headers[] = new SoapHeader('http://schemas.ecommerce.collector.se/v30/InvoiceService', 'Username', $settings['collector_username']);
        $headers[] = new SoapHeader('http://schemas.ecommerce.collector.se/v30/InvoiceService', 'Password', $settings['collector_sharedkey']);
        $soap->__setSoapHeaders($headers);
        $request = $soap->SendInvoice($args);
        return $request;
    }

    public function activate($store_id, $country_code, $invoice_no)
    {
        $settings = $this->getSettings();
        $soap = new SoapClient($settings['collector_mode'] === 'live' ? self::COLLECTOR_BANK_SOAP_LIVE : self::COLLECTOR_BANK_SOAP_TEST);
        $args = [
            'StoreId'     => $store_id,
            'CountryCode' => $country_code,
            'InvoiceNo'   => $invoice_no,
        ];
        $headers = [];
        $headers[] = new SoapHeader('http://schemas.ecommerce.collector.se/v30/InvoiceService', 'Username', $settings['collector_username']);
        $headers[] = new SoapHeader('http://schemas.ecommerce.collector.se/v30/InvoiceService', 'Password', $settings['collector_sharedkey']);
        $soap->__setSoapHeaders($headers);
        $request = $soap->ActivateInvoice($args);
        return $request;
    }

    public function cancel($store_id, $country_code, $invoice_no)
    {
        $settings = $this->getSettings();
        $soap = new SoapClient($settings['collector_mode'] === 'live' ? self::COLLECTOR_BANK_SOAP_LIVE : self::COLLECTOR_BANK_SOAP_TEST);
        $args = [
            'StoreId'     => $store_id,
            'CountryCode' => $country_code,
            'InvoiceNo'   => $invoice_no,
        ];
        $headers = [];
        $headers[] = new SoapHeader('http://schemas.ecommerce.collector.se/v30/InvoiceService', 'Username', $settings['collector_username']);
        $headers[] = new SoapHeader('http://schemas.ecommerce.collector.se/v30/InvoiceService', 'Password', $settings['collector_sharedkey']);
        $soap->__setSoapHeaders($headers);
        $request = $soap->CancelInvoice($args);
        return $request;
    }

    public function credit($store_id, $country_code, $invoice_no)
    {
        $settings = $this->getSettings();
        $soap = new SoapClient($settings['collector_mode'] === 'live' ? self::COLLECTOR_BANK_SOAP_LIVE : self::COLLECTOR_BANK_SOAP_TEST);
        $args = [
            'StoreId'     => $store_id,
            'CountryCode' => $country_code,
            'InvoiceNo'   => $invoice_no,
            'CreditDate'  => time()
        ];
        $headers = [];
        $headers[] = new SoapHeader('http://schemas.ecommerce.collector.se/v30/InvoiceService', 'Username', $settings['collector_username']);
        $headers[] = new SoapHeader('http://schemas.ecommerce.collector.se/v30/InvoiceService', 'Password', $settings['collector_sharedkey']);
        $soap->__setSoapHeaders($headers);
        $request = $soap->CreditInvoice($args);
        return $request;
    }

    public function extend($correlation_id, $store_id, $country_code, $invoice_no)
    {
        $settings = $this->getSettings();
        $soap = new SoapClient($settings['collector_mode'] === 'live' ? self::COLLECTOR_BANK_SOAP_LIVE : self::COLLECTOR_BANK_SOAP_TEST);
        $args = [
            'StoreId'     => $store_id,
            'CorrelationId'  => $correlation_id,
            'CountryCode' => $country_code,
            'InvoiceNo'   => $invoice_no
        ];
        $headers = [];
        $headers[] = new SoapHeader('http://schemas.ecommerce.collector.se/v30/InvoiceService', 'Username', $settings['collector_username']);
        $headers[] = new SoapHeader('http://schemas.ecommerce.collector.se/v30/InvoiceService', 'Password', $settings['collector_sharedkey']);
        $soap->__setSoapHeaders($headers);
        $request = $soap->ExtendDueDate($args);
        return $request;
    }

}
