<?php

class ModelCollectorQuote extends Model {
    /**
     * Add Quote
     * @param $visitor_id
     * @param $quote_data
     *
     * @return int|bool
     */
    public function add($visitor_id, $quote_data)
    {
        $quote_data = json_encode($quote_data);
        $token = token(50);
        $date = date('Y-m-d H:i:s');
        $query = sprintf('INSERT INTO `' . DB_PREFIX . 'collector_quote` (visitor_id, token, quote_data, created_at) VALUES (%d, "%s", "%s", "%s");',
            $this->db->escape((int)$visitor_id),
            $this->db->escape($token),
            $this->db->escape($quote_data),
            $date
        );

        try {
            $this->db->query($query);
            return $this->db->getLastId();
        } catch(Exception $e) {
            return false;
        }
    }

    /**
     * Update Quote
     * @param $quote_id
     * @param $quote_data
     *
     * @return bool
     */
    public function update($quote_id, $quote_data)
    {
        $quote_data = json_encode($quote_data);
        $query = sprintf('UPDATE `' . DB_PREFIX . 'collector_quote` SET quote_data = "%s" WHERE quote_id=%d;',
            $this->db->escape($quote_data),
            $this->db->escape((int)$quote_id)
        );

        try {
            return $this->db->query($query);
        } catch(Exception $e) {
            return false;
        }
    }

    /**
     * Get Quote By Visitor Id
     * @param $visitor_id
     *
     * @return bool|mixed
     */
    public function getByVisitorId($visitor_id)
    {
        $query = sprintf('SELECT * FROM `' . DB_PREFIX . 'collector_quote` WHERE visitor_id=%d;',
            $this->db->escape((int)$visitor_id)
        );
        $result = $this->db->query($query);
        if ($result->num_rows === 0) {
            return false;
        }
        return array_shift($result->rows);
    }

    /**
     * Get Quote by Id
     * @param int $quote_id
     *
     * @return bool|mixed
     */
    public function getById($quote_id)
    {
        $query = sprintf('SELECT * FROM `' . DB_PREFIX . 'collector_quote` WHERE quote_id=%d;',
            $this->db->escape((int)$quote_id)
        );
        $result = $this->db->query($query);
        if ($result->num_rows === 0) {
            return false;
        }
        return array_shift($result->rows);
    }

    /**
     * Get Quote by Token
     * @param string $token
     *
     * @return bool|mixed
     */
    public function getByToken($token)
    {
        $query = sprintf('SELECT * FROM `' . DB_PREFIX . 'collector_quote` WHERE token="%s";',
            $this->db->escape($token)
        );
        $result = $this->db->query($query);
        if ($result->num_rows === 0) {
            return false;
        }
        return array_shift($result->rows);
    }

    /**
     * Remove Quote By Visitor Id
     * @param $visitor_id
     *
     * @return mixed
     */
    public function removeByVisitorId($visitor_id)
    {
        $query = sprintf("DELETE FROM `' . DB_PREFIX . 'collector_quote` WHERE visitor_id=%d",
            $this->db->escape((int)$visitor_id)
        );

        return $this->db->query($query);
    }
}
