<?php

class ModelCollectorPayments extends Model {
    /**
     * Get By Id
     * @param $id
     *
     * @return bool|mixed
     */
    public function getById($id)
    {
        $query = sprintf('SELECT * FROM `' . DB_PREFIX . 'collector_payments` WHERE id=%d;',
            $this->db->escape((int)$id)
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
    public function update($id, $data)
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
