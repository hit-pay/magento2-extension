<?php
namespace SoftBuild\HitPay\Controller\Pay;

use SoftBuild\HitPay\Services\Client;
use SoftBuild\HitPay\Services\Request\CreatePayment;

class Create extends \Magento\Framework\App\Action\Action
{
    protected $helper;
    protected $payment;
    protected $orderFactory;

    public function __construct(
        \SoftBuild\HitPay\Helper\Data $helper,
        \SoftBuild\HitPay\Model\Pay $payment,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Framework\App\Action\Context $context
    ) {
        $this->helper = $helper;
        $this->payment = $payment;
        $this->orderFactory = $orderFactory;
        parent::__construct($context);
    }

    public function execute()
    {
        $response = array();
        $dropInAjax = 0;
        
        try {
            $dropInAjax = (int)$this->getRequest()->getParam('drop_in_ajax');
            
            $model = $this->_objectManager->get('SoftBuild\HitPay\Model\Pay');
            $session = $this->_objectManager->get('Magento\Checkout\Model\Session');
            $order = $model->getOrder();
            if ($order && $order->getId() > 0) {

                $payment = $order->getPayment();

                $client = new Client(
                    $model->getConfigValue("api_key"),
                    $model->getConfigValue("mode")
                );
                
                $redirectUrl = $model->getReturnUrl(array('order_id' => $order->getIncrementId()) );
                $webhook = $model->getWebhookUrl(array('order_id' => $order->getIncrementId()) );
                
                $createPaymentRequest = new CreatePayment();
                $createPaymentRequest->setAmount(number_format($order->getGrandTotal(), 2, '.', ''))
                    ->setCurrency($order->getOrderCurrencyCode())
                    ->setReferenceNumber($order->getIncrementId())
                    ->setWebhook($webhook)
                    ->setRedirectUrl($redirectUrl)
                    ->setChannel('api_magento');
           
                $createPaymentRequest->setName($order->getCustomerFirstname() . ' ' . $order->getCustomerLastname());
                $createPaymentRequest->setEmail($order->getCustomerEmail());
                
                $createPaymentRequest->setPurpose($model->getStoreName());

                $enable_pos = $model->getConfigValueByCode('hitpay_pos','active');
                if ($enable_pos) {
                    $hitpay_payment_option = $payment->getAdditionalInformation('hitpay_payment_option');
                    
                    if (!empty($hitpay_payment_option) && $hitpay_payment_option != 'onlinepayment') {
                        $hitpay_payment_option  = trim($hitpay_payment_option);
                        $terminal_id = $hitpay_payment_option;
                        $createPaymentRequest->setPaymentMethod('wifi_card_reader');
                        $createPaymentRequest->setWifiTerminalId($terminal_id);
                    }
                }
                
                $model->log('Create Payment Request:');
                $model->log((array)$createPaymentRequest);

                $result = $client->createPayment($createPaymentRequest);
                
                $model->log('Create Payment Response:');
                $model->log((array)$result);
                
                $savePayment = [
                    'payment_id' => $result->getId(),
                    'amount' => number_format($order->getGrandTotal(), 2, '.', ''),
                    'currency_id' => $order->getOrderCurrencyCode(),
                    'status' => $result->getStatus(),
                    'increment_id' => $order->getIncrementId(),
                ];
                $this->helper->addPaymentResponse($order->getId(), json_encode($savePayment));
                                
                if ($result->getStatus() == 'pending') {
                    if ($dropInAjax) {
                        $response['redirect_url'] = $redirectUrl;
                        $response['cart_url'] = $model->getCheckoutCartUrl();
                        $response['status'] = 'success';
                        $response['payment_request_id'] = $result->getId();
                        $response['payment_url'] = $result->getUrl();
                        $response['order_id'] = $result->getUrl();
                        
                        $domain = 'sandbox.hit-pay.com';
                        if ($model->getConfigValue("mode")) {
                            $domain = 'hit-pay.com';
                        }
                        $response['domain'] = $domain;
                        $response['apiDomain'] = $domain;
                        
                        echo json_encode($response);
                        exit;
                    } else {
                        echo '<script>window.top.location.href = "'.$result->getUrl().'";</script>';
                    }
                } else {
                    throw new \Exception(sprintf(__('Status from gateway is %s .'), $result->getStatus()));
                }
            } else {
                throw new \Exception(sprintf(__('Checkout session expired it seems')));
            }
        } catch (\Exception $e) {
            $error_message = $e->getMessage();
            $message = __('HitPay Create Payment Request failed. '.$error_message);
            if ($order && $order->getId() > 0) {
                $order->cancel();
                $order->addStatusHistoryComment($message, \Magento\Sales\Model\Order::STATE_CANCELED);
                $order->save();
                $session->restoreQuote();
            }
            $this->messageManager->addError($message);
            
            if ($dropInAjax) {
                $response['status'] = 'error';
                $response['message'] = $message;
                $response['redirect_url'] = $model->getCheckoutCartUrl();
                echo json_encode($response);
                exit;
            } else {
                echo '<script>window.top.location.href = "'.$model->getCheckoutCartUrl().'";</script>';
            }
        }
        exit;
    }
}
