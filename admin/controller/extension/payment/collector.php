<?php

class ControllerExtensionPaymentCollector extends Controller
{
    private $error = [];

    public function index()
    {
        // Load languages
        $this->load->language('extension/payment/collector');

        $languages = [
            'button_save', 'button_cancel', 'heading_title',
            'text_success', 'text_edit', 'text_enabled', 'text_disabled', 'entry_store_id', 'entry_status',
            'entry_username', 'entry_sharedkey', 'entry_mode', 'entry_order_status_preliminary',
            'entry_order_status_accepted', 'entry_order_status_pending', 'entry_order_status_rejected',
	        'entry_order_status_credited', 'entry_merchant_terms_url', 'entry_url_token', 'entry_invoice_status_url',
            'entry_invoice_fee_b2c', 'entry_invoice_fee_vat_b2c', 'entry_invoice_fee_b2b', 'entry_invoice_fee_vat_b2b'
        ];
        foreach ($languages as $language) {
            $data[$language] = $this->language->get($language);
        }

        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('setting/setting');

        // Save settings
        if (($this->request->server['REQUEST_METHOD'] === 'POST') && $this->validate()) {
            $this->model_setting_setting->editSetting('collector', $this->request->post);
            $this->session->data['success'] = $this->language->get('text_success');
            $this->response->redirect(
                $this->url->link('extension/extension', 'token=' . $this->session->data['token'] . '&type=payment', true)
            );
        }

        /* $data['error_warning'] = '';
        if (isset($this->error['warning'])) {
            $data['error_warning'] = $this->error['warning'];
        }

        $data['error_key'] = '';
        if (isset($this->error['key'])) {
            $data['error_key'] = $this->error['key'];
        }

        $data['error_secret'] = '';
        if (isset($this->error['secret'])) {
            $data['error_secret'] = $this->error['secret'];
        } */

        // Breadcrumbs
        $data['breadcrumbs'] = [
            [
                'text' => $this->language->get('text_home'),
                'href' => $this->url->link('common/dashboard', 'token=' . $this->session->data['token'], true)
            ],
            [
                'text' => $this->language->get('text_extension'),
                'href' => $this->url->link('extension/extension',
                    'token=' . $this->session->data['token'] . '&type=payment', true
                )
            ],
            [
                'text' => $this->language->get('heading_title'),
                'href' => $this->url->link('extension/payment/collector', 'token=' . $this->session->data['token'], true)
            ]
        ];

        $data['action'] = $this->url->link('extension/payment/collector', 'token=' . $this->session->data['token'], true);
        $data['cancel'] = $this->url->link('extension/extension', 'token=' . $this->session->data['token'] . '&type=payment', true);

        // Load settings
        $settings = [
            'collector_status',
	        'collector_country',
	        'collector_store_mode',
            'collector_store_id_b2c_se',
            'collector_store_id_b2b_se',
            'collector_store_id_b2c_no',
            'collector_store_id_b2b_no',
            'collector_username',
            'collector_sharedkey',
            'collector_mode',
            'collector_order_status_accepted_id',
	        'collector_order_status_preliminary_id',
            'collector_order_status_pending_id',
            'collector_order_status_rejected_id',
	        'collector_order_status_credited_id',
            'collector_merchant_terms_url',
            'collector_url_token',
            'collector_invoice_fee_b2c',
            'collector_invoice_fee_vat_b2c',
            'collector_invoice_fee_b2b',
            'collector_invoice_fee_vat_b2b',
            'collector_ic_status',
            'collector_ic_store_id',
            'collector_ic_country_code'
        ];
        foreach ($settings as $setting) {
            if (isset($this->request->post[$setting])) {
                $data[$setting] = $this->request->post[$setting];
            } else {
                $data[$setting] = $this->config->get($setting);
            }
        }

        $data['collector_invoice_status_url'] = str_replace(
            '/admin/',
            '/',
            $this->url->link('checkout/collector/invoicestatus', 'token=' . $data['collector_url_token'], true)
        );

        // Load order statuses
        $this->load->model('localisation/order_status');
        $data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();

        $data['header']      = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer']      = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('extension/payment/collector', $data));
    }

    protected function validate()
    {
        $this->load->model('extension/payment/collector');
        /* if (version_compare(phpversion(), '5.4.0', '<')) {
            $this->error['warning'] = $this->language->get('error_php_version');
        }

        if ( ! $this->user->hasPermission('modify', 'extension/payment/collector')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }

        if ($this->error && ! isset($this->error['warning'])) {
            $this->error['warning'] = $this->language->get('error_warning');
        }

        return ! $this->error; */

        return true;
    }

    public function install()
    {
        $this->load->model('user/user_group');
        $this->model_user_user_group->addPermission($this->user->getId(), 'access', 'collector/action');
        $this->model_user_user_group->addPermission($this->user->getId(), 'modify', 'collector/action');

        $this->load->model('extension/payment/collector');

        $this->model_extension_payment_collector->install();
    }
}
