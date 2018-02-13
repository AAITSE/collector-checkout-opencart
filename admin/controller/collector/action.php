<?php

class ControllerCollectorAction extends Controller {
    public function index()
    {
        $id = isset($this->request->post['id']) ? $this->request->post['id'] : null;
        $action = isset($this->request->post['action']) ? $this->request->post['action'] : null;


        $this->load->model('collector/api');
        $this->load->model('collector/payments');

        $settings = $this->model_collector_api->getSettings($id);
        $payment = $this->model_collector_payments->getById($id);

        try {
            switch ($action) {
                case 'send':
                    $info = json_decode($payment['info'], true);
                    // @todo Corporate customer
                    $email = $info['data']['customer']['email'];
                    $result = $this->model_collector_api->send($payment['order_id'], $payment['store_id'], $payment['country_code'], $payment['purchaseIdentifier'], $email, 2);
                    break;
                case 'activate':
                    $result = $this->model_collector_api->activate($payment['store_id'], $payment['country_code'], $payment['purchaseIdentifier']);
                    if ($result) {
                        $this->model_collector_payments->update($payment['id'], [
                            'activated' => 1
                        ]);

                        $order_status_id = $settings['collector_order_status_accepted_id'];
                        $this->_update_status($payment['order_id'], $order_status_id, 'Order has been paid');
                    }
                    break;
                case 'cancel':
                    $result = $this->model_collector_api->cancel($payment['store_id'], $payment['country_code'], $payment['purchaseIdentifier']);
                    if ($result) {
                        $this->model_collector_payments->update($payment['id'], [
                            'canceled' => 1
                        ]);

                        $order_status_id = $settings['collector_order_status_rejected_id'];
                        $this->_update_status($payment['order_id'], $order_status_id, 'Order has been canceled');
                    }
                    break;
                case 'credit':
                    $result = $this->model_collector_api->credit($payment['store_id'], $payment['country_code'], $payment['purchaseIdentifier']);
                    if ($result) {
                        $this->model_collector_payments->update($payment['id'], [
                            'credited' => 1
                        ]);
                    }
                    break;
                case 'extend':
                    $result = $this->model_collector_api->extend($payment['order_id'], $payment['store_id'], $payment['country_code'], $payment['purchaseIdentifier']);
	                if ($result) {
		                $this->model_collector_payments->update($payment['id'], [
			                'extended' => 1
		                ]);
	                }
                    break;
            }
        } catch (Exception $e) {
            $this->response->addHeader('Content-Type: application/json');
            $this->response->setOutput(json_encode(['success' => false, 'message' => $e->getMessage()]));
            return;
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode(['success' => true]));
    }

    /**
     * Update Status
     * @param $order_id
     * @param $order_status_id
     * @param string $comment
     * @param bool $notify
     */
    protected function _update_status($order_id, $order_status_id, $comment = '', $notify = true)
    {
        $this->load->model('sale/order');
        $order_info = $this->model_sale_order->getOrder($order_id);
        if ($order_info) {
            // Update the DB with the new statuses
            $this->db->query("UPDATE `" . DB_PREFIX . "order` SET order_status_id = '" . (int)$order_status_id . "', date_modified = NOW() WHERE order_id = '" . (int)$order_id . "'");

            $this->db->query("INSERT INTO " . DB_PREFIX . "order_history SET order_id = '" . (int)$order_id . "', order_status_id = '" . (int)$order_status_id . "', notify = '" . (int)$notify . "', comment = '" . $this->db->escape($comment) . "', date_added = NOW()");
        }
    }
}
