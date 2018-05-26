<?php

class ModelCollectorApi extends Model {
    const BACKEND_API_URL_LIVE = 'https://checkout-api.collector.se';
    const BACKEND_API_URL_TEST = 'https://checkout-api-uat.collector.se';

    /**
     * Get Settings
     * @return array
     */
    public function getSettings()
    {
        $default = [
            'collector_status' => 0,
	        'collector_country' => '',
	        'collector_store_mode' => '',
            'collector_store_id' => '',
            'collector_username' => '',
            'collector_sharedkey' => '',
            'collector_mode' => 'live',
            'collector_order_status_accepted_id' => 0,
            'collector_order_status_preliminary_id' => 0,
            'collector_order_status_pending_id' => 0,
            'collector_order_status_rejected_id' => 0,
	        'collector_order_status_credited_id' => 0,
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

        if (empty($settings['collector_merchant_terms_url'])) {
            $settings['collector_merchant_terms_url'] = html_entity_decode($this->url->link('/', '', true));
        }
        return array_merge($default, $settings);
    }

    /**
     * Collector Request
     * @param $method
     * @param $path
     * @param array $params
     *
     * @return mixed|null
     * @throws Exception
     */
    public function request($method, $path, $params = [])
    {
        $settings = $this->getSettings();
        $user_name = $settings['collector_username'];
        $shared_key = $settings['collector_sharedkey'];
        $backend_api_url = $settings['collector_mode'] === 'live' ? self::BACKEND_API_URL_LIVE : self::BACKEND_API_URL_TEST;

        // Request body
        $request_body = '';
        if (count($params) > 0) {
            $request_body = json_encode($params);
        }

        // Hash
        $hash = $user_name . ':' . hash('sha256', $request_body . $path . $shared_key);

        $url = $backend_api_url . $path;

        // Logger
	    $log = new Log('collector_api.log');

        // Request
        try {
            $client = new GuzzleHttp\Client();
            $headers = array(
                'Accept' => 'application/json',
                'Authorization' => 'SharedKey ' . base64_encode($hash)
            );

            $response = $client->request($method, $url, count($params) > 0 ? [
                'headers' => $headers,
                'body' => $request_body,
                //'debug' => true
            ] : ['headers' => $headers]);

	        $log->write(sprintf('%s %s %s', $method, $url, 'Data: ' . var_export($params, true)));
        } catch (GuzzleHttp\Exception\ClientException $e) {
            $response = $e->getResponse();
            $responseBodyAsString = $response->getBody()->getContents();
	        $log->write(sprintf('ClientException: %s. URL: %s, Params: %s', $responseBodyAsString, $url, var_export($params, true)));
            throw new Exception('Error: ' . $responseBodyAsString);
        } catch (GuzzleHttp\Exception\ServerException $e) {
            $response = $e->getResponse();
            $responseBodyAsString = $response->getBody()->getContents();
	        $log->write(sprintf('ServerException: %s. URL: %s, Params: %s', $responseBodyAsString, $url, var_export($params, true)));
            throw new Exception('Error: ' . $responseBodyAsString);
        } catch (Exception $e) {
	        $log->write(sprintf('ServerException: %s. URL: %s, Params: %s', $e->getMessage(), $url, var_export($params, true)));
	        throw $e;
        }

        if (floor($response->getStatusCode() / 100) != 2) {
            throw new Exception('Request with with code: ' . $response->getStatusCode());
        }

        return json_decode($response->getBody()->getContents(), true);
    }
}
