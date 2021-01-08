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
     * @var Cart
     */
    protected $cart;
    /**
     * @var Session
     */
    private $customerSession;
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
     * @var \Magento\Quote\Model\QuoteFactory
     */
    private $quoteFactory;
    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    private $request;
    /**
     * @var \Magento\Quote\Model\QuoteManagement
     */
    private $quoteManagement;
    /**
     * @var \SoftBuild\HitPay\Model\ResourceModel\ResourcePaymentsFactory
     */
    private $resourcePaymentsFactory;
    /**
     * @var \Magento\Quote\Api\CartRepositoryInterface
     */
    private $quoteRepository;
    /**
     * @var \Magento\Quote\Api\CartManagementInterface
     */
    private $cartManagementInterface;
    /**
     * @var \Magento\Sales\Model\Order
     */
    private $order;
    /**
     * @var \Magento\Checkout\Model\Session
     */
    private $checkoutSession;
    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    private $customerFactory;
    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var \Magento\Framework\Webapi\Rest\Request
     */
    private $webRequest;

    /**
     * HitPay constructor.
     * @param ScopeConfigInterface $scopeConfig
     * @param StoreManagerInterface $storeManager
     * @param Cart $cart
     * @param Session $customerSession
     * @param LoggerInterface $logger
     * @param \SoftBuild\HitPay\Model\PaymentsFactory $paymentsFactory
     * @param \Magento\Quote\Model\QuoteFactory $quoteFactory
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Magento\Quote\Model\QuoteManagement $quoteManagement
     * @param \SoftBuild\HitPay\Model\ResourceModel\PaymentsFactory $resourcePaymentsFactory
     * @param \Magento\Quote\Api\CartRepositoryInterface $quoteRepository
     * @param \Magento\Quote\Api\CartManagementInterface $cartManagementInterface
     * @param \Magento\Sales\Model\Order $order
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Customer\Model\CustomerFactory $customerFactory
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Checkout\Model\Cart $cart,
        \Magento\Customer\Model\Session $customerSession,
        \Psr\Log\LoggerInterface $logger,
        \SoftBuild\HitPay\Model\PaymentsFactory $paymentsFactory,
        \Magento\Quote\Model\QuoteFactory $quoteFactory,
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Framework\Webapi\Rest\Request $webRequest,
        \Magento\Quote\Model\QuoteManagement $quoteManagement,
        \SoftBuild\HitPay\Model\ResourceModel\PaymentsFactory $resourcePaymentsFactory,
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        \Magento\Quote\Api\CartManagementInterface $cartManagementInterface,
        \Magento\Sales\Model\Order $order,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
        $this->cart = $cart;
        $this->customerSession = $customerSession;
        $this->logger = $logger;
        $this->paymentsFactory = $paymentsFactory;
        $this->quoteFactory = $quoteFactory;
        $this->request = $request;
        $this->quoteManagement = $quoteManagement;
        $this->resourcePaymentsFactory = $resourcePaymentsFactory;
        $this->quoteRepository = $quoteRepository;
        $this->cartManagementInterface = $cartManagementInterface;
        $this->order = $order;
        $this->checkoutSession = $checkoutSession;
        $this->customerFactory = $customerFactory;
        $this->customerRepository = $customerRepository;
        $this->webRequest = $webRequest;
    }

    /**
     *
     */
    public function createRequest()
    {
        try {
            $api_key = $this->scopeConfig->getValue('payment/hitpay_gateway/api_key');
            $mode = (bool)$this->scopeConfig->getValue('payment/hitpay_gateway/mode');

            $hitpay_client = new Client($api_key, $mode);

            $redirect_url = $this->storeManager->getStore()->getUrl(
                'hitpay/confirmation',
                [
                    'cart_id' => $this->cart->getQuote()->getId()
                ]
            );

            $webhook = $this->storeManager->getStore()->getUrl(
                'rest/V1/hitpay-webhook',
                [
                    'cart_id' => $this->cart->getQuote()->getId()
                ]
            );

            file_put_contents(
                '/var/www/html/log.txt',
                "\n" . print_r($redirect_url, true) .
                "\n" . print_r($webhook, true) .
                "\n\nfile: " . __FILE__ .
                "\n\nline: " . __LINE__ .
                "\n\ntime: " . date('d-m-Y H:i:s'), 8
            );

            $create_payment_request = new CreatePayment();
            $create_payment_request->setAmount($this->cart->getQuote()->getGrandTotal())
                ->setCurrency($this->storeManager->getStore()->getCurrentCurrency()->getCode())
                ->setReferenceNumber($this->checkoutSession->getQuote()->getId())
                ->setWebhook($webhook)
                ->setRedirectUrl($redirect_url)
                ->setChannel('api_magento');

            $create_payment_request->setName($this->customerSession->getName());
            $create_payment_request->setEmail($this->checkoutSession->getQuote()->getBillingAddress()->getEmail());

            $result = $hitpay_client->createPayment($create_payment_request);
            
            /**
             * @var Payments $payment
             */
            $payment = $this->paymentsFactory->create();
            $payment->setData('payment_id', $result->getId());
            $payment->setData('amount', $this->checkoutSession->getQuote()->getGrandTotal());
//            $payment->setData('currency_id', $this->storeManager->getStore()->getCurrentCurrency()->getId());
            $payment->setData('status', $result->getStatus());
            $payment->setData('cart_id', $this->checkoutSession->getQuote()->getId());
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
        $quoteId = $this->webRequest->getParam('cart_id', false);
        $quote = $this->quoteRepository->get($quoteId);

        if (empty($quote)) {
            throw new \Exception('HitPay: quote not found');
        }

        try {
            $data = $_POST;
            unset($data['hmac']);
            $paymentStatus = Order::STATE_PENDING_PAYMENT;
            $salt = $this->scopeConfig->getValue('payment/hitpay_gateway/salt');
            if (Client::generateSignatureArray($salt, $data) == $this->webRequest->getParam('hmac', false)) {
                $payment_request_id = $this->webRequest->getParam('payment_request_id', false);

                $id = $this->resourcePaymentsFactory->create()->getIdByPaymentId($payment_request_id);
                $saved_payment = $this->paymentsFactory->create()->load($id);
                if ($saved_payment && !$saved_payment->getData('is_paid')) {
                    if ($this->webRequest->getParam('status', false) == 'completed'
                        && $saved_payment->getData('amount') == $this->webRequest->getParam('amount', false)
                        && $saved_payment->getData('cart_id') == $this->webRequest->getParam('reference_number', false)) {
                        $paymentStatus = Order::STATE_COMPLETE;
                        $saved_payment->setData('is_paid', true);
                    } elseif ($this->request->getParam('status', false) == 'failed') {
                        $paymentStatus = Order::STATE_CANCELED;
                    } elseif ($this->request->getParam('status', false) == 'pending') {
                        $paymentStatus = Order::STATE_PENDING_PAYMENT;
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

                if ($quote->getIsActive()) {
                    $store = $this->storeManager->getStore();
                    $websiteId = $this->storeManager->getStore()->getWebsiteId();
                    $customer = $this->customerFactory->create();
                    $customer->setWebsiteId($websiteId);
                    $customer->loadByEmail($quote->getBillingAddress()->getEmail());
                    if (!$customer->getId()) {
                        $customer->setWebsiteId($websiteId)
                            ->setStore($store)
                            ->setFirstname($quote->getBillingAddress()->getFirstname())
                            ->setLastname($quote->getBillingAddress()->getLastname())
                            ->setEmail($quote->getBillingAddress()->getEmail())
                            ->setPassword($quote->getBillingAddress()->getEmail());
                        $customer->save();
                    }
                    $quote->setCustomerFirstname($quote->getBillingAddress()->getFirstname());
                    $quote->setCustomerLastname($quote->getBillingAddress()->getLastname());
                    $quote->setCustomerEmail($quote->getBillingAddress()->getEmail());
                    $quote->setCustomerIsGuest(true);
                    $quote->setStore($store);

                    $customer = $this->customerRepository->getById($customer->getId());
                    /*$quote->assignCustomer($customer);*/

                    $orderId = $this->quoteManagement->placeOrder($quote->getId());

                    $quote->setOrigOrderId($orderId);
                    $quote->save();
                } else {
                    $orderId = $quote->getOrigOrderId();
                }

                $order = $this->order->load($orderId);
                $order->setState($paymentStatus)->setStatus($paymentStatus);
                $order->save();

                $saved_payment->setData('status', $this->request->getParam('status', false));
                $saved_payment->setData('order_id', $order->getRealOrderId());

                $saved_payment->save();

                /*$api_key = $this->scopeConfig->getValue('payment/hitpay_gateway/api_key');
                $mode = (bool)$this->scopeConfig->getValue('payment/hitpay_gateway/mode');

                $hitpay_client = new Client($api_key, $mode);

                $result = $hitpay_client->getPaymentStatus($payment_request_id);*/

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

        if ($savedPayment->getData('status') == 'completed'
            /*&& $savedPayment->getData('is_paid')*/) {
            $order = $this->order->load($savedPayment->getData('order_id'));

            $this->checkoutSession->setLastSuccessQuoteId($savedPayment->getData('cart_id'));
            $this->checkoutSession->setLastOrderId($savedPayment->getData('order_id'));
            $this->checkoutSession->setLastRealOrderId($savedPayment->getData('order_id'));
            $this->checkoutSession->setLastRealOrder($order);

            return true;
        }

        return false;
    }
}