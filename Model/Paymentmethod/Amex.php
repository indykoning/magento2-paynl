<?php

namespace Paynl\Payment\Model\Paymentmethod;

class Amex extends PaymentMethod
{
    protected $_code = 'paynl_payment_amex';

    protected function getDefaultPaymentOptionId()
    {
        return 1705;
    }
}
