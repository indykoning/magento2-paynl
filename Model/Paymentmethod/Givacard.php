<?php

namespace Paynl\Payment\Model\Paymentmethod;

class Givacard extends PaymentMethod
{
    protected $_code = 'paynl_payment_givacard';

    /**
     * @return integer
     */
    protected function getDefaultPaymentOptionId()
    {
        return 1657;
    }
}
