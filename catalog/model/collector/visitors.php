<?php

class ModelCollectorVisitors extends Model {
    /**
     * Add Visitor
     * @param $api_id
     * @param $customer_id
     * @param $session_id
     *
     * @return int|bool
     */
    public function add($api_id, $customer_id, $session_id)
    {
        $date = date('Y-m-d H:i:s');
        $query = sprintf('INSERT INTO `' . DB_PREFIX . 'collector_visitors` (api_id, customer_id, session_id, created_at) VALUES (%d, %d, "%s", "%s");',
            $this->db->escape((int)$api_id),
            $this->db->escape((int)$customer_id),
            $this->db->escape($session_id),
            $this->db->escape($date)
        );

        try {
            $this->db->query($query);
            return $this->db->getLastId();
        } catch(Exception $e) {
            return false;
        }
    }

    /**
     * Get Visitor by Session Data
     * @param $api_id
     * @param $customer_id
     * @param $session_id
     *
     * @return bool|mixed
     */
    public function getBySession($api_id, $customer_id, $session_id) {
        $query = sprintf('SELECT * FROM `' . DB_PREFIX . 'collector_visitors` WHERE api_id=%d AND customer_id=%d AND api_id="%s";',
            $this->db->escape((int)$api_id),
            $this->db->escape((int)$customer_id),
            $this->db->escape($session_id)
        );
        $result = $this->db->query($query);
        if ($result->num_rows === 0) {
            return false;
        }
        return array_shift($result->rows);
    }

    /**
     * Get Visitor By Id
     * @param $visitor_id
     *
     * @return bool|mixed
     */
    public function getById($visitor_id) {
        $query = sprintf('SELECT * FROM `' . DB_PREFIX . 'collector_visitors` WHERE visitor_id=%d;',
            $this->db->escape((int)$visitor_id)
        );
        $result = $this->db->query($query);
        if ($result->num_rows === 0) {
            return false;
        }
        return array_shift($result->rows);
    }

    /**
     * Get Current Visitor
     * @return array
     */
    public function getCurrentVisitor()
    {
        $api_id = isset($this->session->data['api_id']) ? (int)$this->session->data['api_id'] : 0;
        $customer_id = $this->customer->getId();
        $session_id = $this->session->getId();

        if (!$visitor = $this->getBySession($api_id, $customer_id, $session_id)) {
            $visitor_id = $this->add($api_id, $customer_id, $session_id);
            return $this->getById($visitor_id);
        }

        return $visitor;
    }

    /**
     * Get Current Visitor Id
     * @return int
     */
    public function getCurrentVisitorId()
    {
        $visitor = $this->getCurrentVisitor();
        return $visitor['visitor_id'];
    }
}
