<?php

namespace SoftBuild\HitPay\Plugin;

use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderManagementInterface;
 
/**
 * Class OrderManagement
 */
class OrderManagement
{
    /**
     * @var \Magento\Sales\Model\Service\InvoiceService
     */
    protected $invoiceService;

    /**
     * @var \Magento\Framework\DB\TransactionFactory
     */
    protected $transaction;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;

    /**
     * @var \Magento\Sales\Model\Convert\Order
     */
    protected $convertOrder;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var ScopeConfigInterface
     */
    protected $orderFactory;

    public function __construct(
        \Magento\Sales\Model\Service\InvoiceService $invoiceService,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Framework\DB\TransactionFactory $transaction,
        \Magento\Sales\Model\Convert\Order $convertOrder,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \SoftBuild\HitPay\Model\PaymentsFactory $paymentsFactory
    ) {
        $this->invoiceService = $invoiceService;
        $this->transaction = $transaction;
        $this->scopeConfig = $scopeConfig;
        $this->messageManager = $messageManager;
        $this->convertOrder = $convertOrder;
        $this->orderFactory = $orderFactory;
        $this->paymentsFactory = $paymentsFactory;
    }
    public function afterPlace(
        OrderManagementInterface $subject,
        OrderInterface $result
    ) {

        $orderId = $result->getIncrementId();
        
        if ($orderId) {

            $autoInvoice = $this->scopeConfig->getValue('payment/hitpay_gateway/auto_invoice');
           
            $order = $this->orderFactory->create()->loadByIncrementId($orderId);
            $payment = $order->getPayment()->getMethodInstance();
            if ($payment->getCode() == 'hitpay_gateway') {
 
                // Check option createinvoice
                $this->createInvoice($payment, $order, $autoInvoice);
                //create notified invoice
                $this->displayNotified($order, $payment, $autoInvoice);
            }
        }
        return $result;
    }
    private function createInvoice($payment, $order, $autoInvoice)
    {
        if ($autoInvoice) {
            try {
                if (!$order->canInvoice()) {
                    throw new \Magento\Framework\Exception\LocalizedException(
                        __('You cant create the Invoice of this order.')
                    );
                }

                $invoice = $this->invoiceService->prepareInvoice($order);
                $invoice->setRequestedCaptureCase(\Magento\Sales\Model\Order\Invoice::CAPTURE_ONLINE);
                $invoice->register();
                $invoice->getOrder()->setIsInProcess(true);
                $transaction = $this->transaction->create()->addObject($invoice)->addObject($invoice->getOrder());
                $transaction->save();
                //Show message create invoice
                $this->messageManager->addSuccessMessage(__("Automatically generated Invoice."));
            } catch (\Exception $e) {
                $order->addStatusHistoryComment('Exception message: ' . $e->getMessage(), false);
                $order->save();
            }
        }
    }

    
    private function displayNotified($order, $payment, $autoInvoice)
    {
        try {
            if ($autoInvoice) {
                return $order->addStatusHistoryComment(__('Automatically Invoice Generated'))->save();
            }
            return null;
        } catch (\Exception $e) {
            $order->addStatusHistoryComment('Exception message: ' . $e->getMessage(), false);
            $order->save();
            return null;
        }
    }
}
