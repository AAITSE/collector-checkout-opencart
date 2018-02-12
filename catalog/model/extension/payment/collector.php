<?php

class ModelExtensionPaymentCollector extends Model
{
    public function getMethod($address, $total)
    {
        // Only Checkout Module supported
        return [];
    }

}
