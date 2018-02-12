<?php

class ModelCollectorPayments extends Model {
    /**
     * Add Payment Record
     * @param array $data
     *
     * @return bool
     * @throws Exception
     */
    public function add($data = [])
    {
        if (!isset($data['quote_id']) || !isset($data['private_id'])) {
            throw new Exception('Required fields');
        }

        if ($checkout = $this->getByQuoteId($data['quote_id'])) {
            $this->remove($checkout['id']);
        }

        if ($checkout = $this->getByPrivateId($data['private_id'])) {
            $this->remove($checkout['id']);
        }

        if (empty($data['created_at'])) {
            $data['created_at'] = date('Y-m-d H:i:s', time());
        }

        if (isset($data['expiresAt'])) {
            $data['expiresAt'] = date('Y-m-d H:i:s', strtotime($data['expiresAt']));
        }

        $fields = array_keys($data);
        $fields = array_map(function($value) {
            $value = '`' . $value . '`';
            $value = $this->db->escape($value);
            return $value;
        }, $fields);

        $values = array_values($data);
        $values = array_map(function($value) {
            $value = $this->db->escape($value);
            $value = '"' . $value . '"';
            return $value;
        }, $values);

        $query = 'INSERT INTO `' . DB_PREFIX . 'collector_payments` ('. implode(', ', $fields). ') VALUES ('. implode(', ', $values) . ');';
        try {
            $this->db->query($query);
            return $this->db->getLastId();
        } catch(Exception $e) {
            throw $e;
        }
    }

    /**
     * Remove
     * @param $id
     *
     * @return mixed
     */
    public function remove($id)
    {
        $query = sprintf('DELETE FROM `' . DB_PREFIX . 'collector_payments` WHERE id=%d',
            (int)$id
        );

        return $this->db->query($query);
    }

    /**
     * Get By Private Id
     * @param $private_id
     *
     * @return bool|mixed
     */
    public function getByPrivateId($private_id)
    {
        $query = sprintf('SELECT * FROM `' . DB_PREFIX . 'collector_payments` WHERE private_id="%s";',
            $this->db->escape($private_id)
        );
        $result = $this->db->query($query);
        if ($result->num_rows === 0) {
            return false;
        }
        return array_shift($result->rows);
    }

    /**
     * Get By Quote Id
     * @param $quote_id
     *
     * @return bool|mixed
     */
    public function getByQuoteId($quote_id)
    {
        $query = sprintf('SELECT * FROM `' . DB_PREFIX . 'collector_payments` WHERE quote_id=%d;',
            $this->db->escape((int)$quote_id)
        );
        $cart = $this->db->query($query);
        if ($cart->num_rows === 0) {
            return false;
        }
        return array_shift($cart->rows);
    }

    /**
     * Get By Order Id
     * @param $order_id
     *
     * @return bool|mixed
     */
    public function getByOrderId($order_id)
    {
        $query = sprintf('SELECT * FROM `' . DB_PREFIX . 'collector_payments` WHERE order_id=%d;',
            $this->db->escape((int)$order_id)
        );
        $cart = $this->db->query($query);
        if ($cart->num_rows === 0) {
            return false;
        }
        return array_shift($cart->rows);
    }

    /**
     * Update
     * @param $id
     * @param array $data
     *
     * @return bool
     */
    public function update($id, $data = [])
    {
        $fields = [];
        foreach ($data as $key => $value) {
            if (is_null($value)) {
                $fields[] = $key . ' = NULL';
                continue;
            } elseif (is_string($value)) {
                $value = $this->db->escape($value);
            } else {
                $value = $this->db->escape((int)$value);
            }

            $fields[] = $key . ' = ' . '"' . $value . '"';
        }


        $query = sprintf('UPDATE `' . DB_PREFIX . 'collector_payments` SET %s WHERE id = %d;',
            implode(', ', $fields),
            $this->db->escape((int) $id)
        );

        try {
            return $this->db->query($query);
        } catch(Exception $e) {
            return false;
        }
    }
}
