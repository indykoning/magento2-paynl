<?php

namespace Paynl\Payment\Plugin;

use Magento\Sales\Model\Order\Payment;

/**
 * Class OrderPaymentAdditionalInformation
 * @package Paynl\Payment\Plugin
 */
class OrderPaymentAdditionalInformation
{
    /**
     * @param Payment $subject
     * @param $result
     * @return array
     * @throws \Exception
     */
    public function afterGetAdditionalInformation(Payment $subject, $result)
    {
        /** @var Payment $subject */
        if (is_array($result)) {
            $order = $subject->getOrder();
            if (empty($result['dob']) && $order->getCustomerDob()) {
                $result['dob'] = (new \DateTime($order->getCustomerDob()))->format('Y-m-d');
            }
            if (empty($result['gender']) && ($order->getCustomerGender() || !empty($order->getCustomerId()))) {
                $gender = $order->getCustomerGender();
                if (empty($gender) && !empty($order->getCustomerId())) {
                    $customer = $this->customerRepository->getById($order->getCustomerId());
                    $gender = $customer->getGender();
                }
                switch ($gender) {
                    case '1':
                        $gender = 'M';
                        break;
                    case '2':
                        $gender = 'F';
                        break;
                    default:
                        $gender = null;
                        break;
                }
                if (!empty($gender)) {
                    $result['gender'] = $gender;
                }
            }
        }
        return $result;
    }
}
