<?php

class ModelCollectorView extends Model {
    /**
     * Translate string
     * @param $string
     *
     * @return mixed
     */
    public function __($string)
    {
        //$language = $this->language->get('code');
        return $string;
    }

    /**
     * Format Price
     * @param $price
     * @param string $currency_code
     *
     * @return mixed
     */
    public function format($price, $currency_code = '')
    {
        if (empty($currency_code)) {
            $currency_code = $this->session->data['currency'];
        }

        return $this->currency->format($price, $currency_code, 1, true);
    }

    /**
     * Get URL
     * @param $route
     * @param string $args
     * @param bool $secure
     *
     * @return string
     */
    public function url($route, $args = '', $secure = true)
    {
        return html_entity_decode($this->url->link($route, $args, $secure));
    }

    /**
     * Render View
     * @param $route
     * @param $data
     *
     * @return mixed
     */
    public function render($route, $data)
    {
        $data['data'] = $data;
        return $this->load->view($route, $data);
    }

    /**
     * Get Value From Session
     * @param $key
     * @param null $default
     *
     * @return null
     */
    public function session($key, $default = null)
    {
        if (!isset($this->session->data[$key])) {
            return $default;
        }

        return $this->session->data[$key];
    }
}
