<?php

namespace SoftBuild\HitPay\Services;

use HitPay\Client;
use HitPay\Request\CreatePayment;
use Magento\Checkout\Model\Cart;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;
use SoftBuild\HitPay\Model\Payments;
use Magento\Sales\Model\Order;

/**
 * Class HitPay
 * @package SoftBuild\HItPay\Services
 */
class HitPay
{
    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var PaymentsFactory
     */
    private $paymentsFactory;

    /**
     * @var \SoftBuild\HitPay\Model\ResourceModel\ResourcePaymentsFactory
     */
    private $resourcePaymentsFactory;

    /**
     * @var \Magento\Sales\Model\OrderFactory
     */
    private $orderFactory;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    private $checkoutSession;

    /**
     * @var \Magento\Framework\Webapi\Rest\Request
     */
    private $webRequest;

    /**
     * @var \Magento\Sales\Model\Service\InvoiceService
     */
    protected $invoiceService;

    /**
     * @var \Magento\Framework\DB\Transaction
     */
    protected $transaction;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $request;

    /**
     * HitPay constructor.
     * @param ScopeConfigInterface $scopeConfig
     * @param StoreManagerInterface $storeManager
     * @param LoggerInterface $logger
     * @param \SoftBuild\HitPay\Model\PaymentsFactory $paymentsFactory
     * @param \Magento\Framework\Webapi\Rest\Request $webRequest
     * @param \SoftBuild\HitPay\Model\ResourceModel\PaymentsFactory $resourcePaymentsFactory
     * @param \Magento\Sales\Model\OrderFactory $orderFactory
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Sales\Model\Service\InvoiceService $invoiceService
     * @param \Magento\Framework\DB\Transaction $transaction
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Psr\Log\LoggerInterface $logger,
        \SoftBuild\HitPay\Model\PaymentsFactory $paymentsFactory,
        \Magento\Framework\Webapi\Rest\Request $webRequest,
        \SoftBuild\HitPay\Model\ResourceModel\PaymentsFactory $resourcePaymentsFactory,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Sales\Model\Service\InvoiceService $invoiceService,
        \Magento\Framework\DB\Transaction $transaction,
        \Magento\Framework\App\RequestInterface $request
    ) {
        $this->scopeConfig      = $scopeConfig;
        $this->storeManager     = $storeManager;
        $this->logger           = $logger;
        $this->paymentsFactory  = $paymentsFactory;
        $this->resourcePaymentsFactory = $resourcePaymentsFactory;
        $this->orderFactory     = $orderFactory;
        $this->checkoutSession  = $checkoutSession;
        $this->webRequest       = $webRequest;
        $this->invoiceService   = $invoiceService;
        $this->transaction      = $transaction;
        $this->request          = $request;
    }

    /**
     *
     */
    public function createRequest()
    {
        try {

            $order = $this->orderFactory->create()->loadByIncrementId($this->checkoutSession->getLastRealOrderId());            

            if (!$order->getId())
            {
                $message = __('Order not found');
                throw new \Exception($message);
            }

            $api_key = $this->scopeConfig->getValue('payment/hitpay_gateway/api_key');
            $mode = (bool)$this->scopeConfig->getValue('payment/hitpay_gateway/mode');

            $hitpay_client = new Client($api_key, $mode);

            $redirect_url = $this->storeManager->getStore()->getUrl(
                'hitpay/confirmation',
                [
                    'order_id' => $order->getId()
                ]
            );

            $webhook = $this->storeManager->getStore()->getUrl(
                'rest/V1/hitpay-webhook',
                [
                    'order_id' => $order->getId()
                ]
            );

            $create_payment_request = new CreatePayment();
            $create_payment_request->setAmount($order->getGrandTotal())
                ->setCurrency($this->storeManager->getStore()->getCurrentCurrency()->getCode())
                ->setReferenceNumber($order->getId())
                ->setWebhook($webhook)
                ->setRedirectUrl($redirect_url)
                ->setChannel('api_magento');

            $create_payment_request->setName($order->getCustomerFirstname().' '.$order->getCustomerLastname());
            $create_payment_request->setEmail($order->getCustomerEmail());

            $result = $hitpay_client->createPayment($create_payment_request);

            $payment = $this->paymentsFactory->create();
            $payment->setData('payment_id', $result->getId());
            $payment->setData('amount', $order->getGrandTotal());
            $payment->setData('order_id', $order->getId());
            $payment->setData('status', $result->getStatus());
            $payment->save();

            if ($result->getStatus() == 'pending') {
                return $result->getUrl();
            } else {
                $message = sprintf('HitPay: sent status is %s', $result->getStatus());
                throw new \Exception($message);
            }
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
        }
    }

    public function checkData()
    {       
        $orderId = $this->webRequest->getParam('order_id', false);        
        $order   = $this->orderFactory->create()->load($orderId);

        if (!$order->getId())
        {
            throw new \Exception('HitPay: quote not found');
        }

        try {
            $data = $_POST;
            unset($data['hmac']);
            $orderStatus = \Magento\Sales\Model\Order::STATE_PENDING_PAYMENT;
            $salt = $this->scopeConfig->getValue('payment/hitpay_gateway/salt');

            if (Client::generateSignatureArray($salt, $data) == $this->webRequest->getParam('hmac', false)) {
                $payment_request_id = $this->webRequest->getParam('payment_request_id', false);
                
                $id = $this->resourcePaymentsFactory->create()->getIdByPaymentId($payment_request_id);
                $saved_payment = $this->paymentsFactory->create()->load($id);                

                if ($saved_payment && !$saved_payment->getData('is_paid')) {
                    if ($this->webRequest->getParam('status', false) == 'completed'
                        && $saved_payment->getData('amount') == $this->webRequest->getParam('amount', false)
                        && $saved_payment->getData('order_id') == $this->webRequest->getParam('reference_number', false)) {
                        $orderStatus = \Magento\Sales\Model\Order::STATE_PROCESSING;
                        $saved_payment->setData('is_paid', true);
                    } elseif ($this->webRequest->getParam('status', false) == 'failed') {
                        $orderStatus = \Magento\Sales\Model\Order::STATE_CANCELED;
                    } elseif ($this->webRequest->getParam('status', false) == 'pending') {
                        $orderStatus = \Magento\Sales\Model\Order::STATE_PENDING_PAYMENT;
                    } else {
                        throw new \Exception(
                            sprintf(
                                'HitPay: payment id: %s, amount is %s, status is %s, is paid: %s',
                                $saved_payment->getData('payment_request_id'),
                                $saved_payment->getData('amount'),
                                $saved_payment->getData('status'),
                                $saved_payment->getData('is_paid') ? 'yes' : 'no'
                            )
                        );
                    }
                }
                
                $order->setState($orderStatus)->setStatus($orderStatus);
                $order->save();

                $saved_payment->setData('status', $this->webRequest->getParam('status', false));
                $saved_payment->save();

                if ($order->canInvoice()) {
                    $invoice = $this->invoiceService->prepareInvoice($order);
                    $invoice->setRequestedCaptureCase(\Magento\Sales\Model\Order\Invoice::CAPTURE_OFFLINE);
                    $invoice->register();                    
                    $transaction = $this->transaction->addObject($invoice)->addObject($invoice->getOrder());                
                    $transaction->save();
                }
            } else {
                throw new \Exception(sprintf('HitPay: hmac is not the same like generated'));
            }
        } catch (\Exeption $e) {            
            throw new \Exception(sprintf('HitPay: %s', $e->getMessage()));
        }
    }
    /**
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function checkPayment()
    {
        $id = $this->resourcePaymentsFactory->create()->getIdByPaymentId(
            $this->request->getParam('reference', false)
        );
        $savedPayment = $this->paymentsFactory->create()->load($id);

        if ($savedPayment->getData('status') == 'completed') {          
            return true;
        }
        return false;
    }    
}
