<?php

namespace Paynl\Payment\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Model\Order;
use Paynl\Payment\Helper\PayHelper;
use Paynl\Payment\Model\Config;
use Paynl\Result\Transaction\Transaction;

class OrderCancelAfter implements ObserverInterface
{
    /**
     *
     * @var Config
     */
    private $config;

    /**
     * @param Config $config
     */
    public function __construct(
        Config $config
    ) {
        $this->config = $config;
    }

    /**
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        $order = $observer->getEvent()->getOrder();
        $this->config->setStore($order->getStore());

        if ($this->config->autoVoidEnabled()) {
            if ($order->getState() == Order::STATE_CANCELED && !$order->hasInvoices()) {
                $data = $order->getPayment()->getData();
                $payOrderId = $data['last_trans_id'] ?? null;
                $payOrderId = str_replace('-void', '', $payOrderId);
                if (!empty($payOrderId)) {
                    $bHasAmountAuthorized = !empty($data['base_amount_authorized']);
                    $amountPaid = isset($data['amount_paid']) ? $data['amount_paid'] : null;
                    $amountRefunded = isset($data['amount_refunded']) ? $data['amount_refunded'] : null;

                    if ($bHasAmountAuthorized && $amountPaid === null && $amountRefunded === null) {
                        payHelper::logDebug('AUTO-VOIDING(order-cancel-after) ' . $payOrderId, [], $order->getStore());
                        $bVoidResult = false;
                        try {
                            $this->config->configureSDK();
                            $bVoidResult = \Paynl\Transaction::void($payOrderId);
                            if (!$bVoidResult) {
                                throw new \Exception('Void failed');
                            }
                        } catch (\Exception $e) {
                            $strMessage = $e->getMessage();
                            payHelper::logDebug('Order PAY error: ' . $strMessage . ' EntityId: ' . $order->getEntityId(), [], $order->getStore());
                            $strFriendlyMessage = 'Failed. Errorcode: PAY-MAGENTO2-005. See docs.pay.nl for more information';
                            if (stripos($strMessage, 'Transaction not found') !== false) {
                                $strFriendlyMessage = 'Transaction seems to be already voided/paid';
                            }
                        }
                        $order->addStatusHistoryComment(
                            __('PAY. - Performed auto-void. Result: ') . ($bVoidResult ? 'Success' : 'Failed') . (empty($strFriendlyMessage) ? '' : '. ' . $strFriendlyMessage)
                        )->save();
                    } else {
                        payHelper::logDebug(
                            'Auto-Void conditions not met (yet). Amountpaid:' . $amountPaid . ' bHasAmountAuthorized: ' . ($bHasAmountAuthorized ? '1' : '0'),
                            [],
                            $order->getStore()
                        );
                    }
                } else {
                    payHelper::logDebug('Auto-Void conditions not met (yet). No PAY-Order-id.', [], $order->getStore());
                }
            } else {
                payHelper::logDebug('Auto-Void conditions not met (yet). State: ' . $order->getState(), [], $order->getStore());
            }
        }
    }
}
