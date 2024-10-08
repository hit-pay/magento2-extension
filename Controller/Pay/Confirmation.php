<?php
namespace SoftBuild\HitPay\Controller\Pay;

use Magento\Framework\Controller\ResultFactory;

class Confirmation extends \Magento\Framework\App\Action\Action
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
        try {
            $model = $this->_objectManager->get('SoftBuild\HitPay\Model\Pay');
            $session = $this->_objectManager->get('Magento\Checkout\Model\Session');
        
            $params = $this->getRequest()->getParams();
            $model->log('Return From Gateway:');
            $model->log(json_encode($params));
            
            if (isset($params['order_id']) && !empty($params['order_id'])) {
                $orderId = trim($params['order_id']);
                $order = $this->orderFactory->create()->loadByIncrementId($orderId);
                 
                if ($order->getId() > 0) {
                    $state = $order->getState();
                    
                    if ($state == 'processing' || $state == 'complete' || $state == 'closed') {
                       return $this->getResponse()->setRedirect($model->getCheckoutSuccessUrl(['order_id' => $orderId])); 
                    }
                    
                    if (isset($params['status']) && $params['status'] == 'canceled') {
                        $error_message = __('Transaction canceled by customer/gateway. ');
                        $model->log('Return:'.$error_message);
                        $message = __('HitPay Payment is failed. '.$error_message);
            
                        if ($order && $order->getId() > 0) {
                            $order->cancel();
                            $order->addStatusHistoryComment($message, \Magento\Sales\Model\Order::STATE_CANCELED);
                            $order->save();
                            $session->restoreQuote();
                        }
                        $this->messageManager->addError($message);
                        return $this->getResponse()->setRedirect($model->getCheckoutCartUrl()); 
                    }
                    
                    return $this->getResponse()->setRedirect($model->getCheckoutSuccessUrl(['order_id' => $orderId]));

                } else {
                    $error_message = __('No relation found with this transaction in the store. ');
                    $model->log('Return:'.$error_message);
                    return $this->getResponse()->setRedirect($model->getCheckoutSuccessUrl()); 
                }
            } else {
                $error_message = __('Empty response received from gateway. ');
                $model->log('Return:'.$error_message);
                return $this->getResponse()->setRedirect($model->getCheckoutSuccessUrl()); 
            }
        } catch (\Exception $e) {
            $error_message = $e->getMessage();
            $model->log('Return from Gateway Catch');
            $model->log('Exception:'.$e->getMessage());
            return $this->getResponse()->setRedirect($model->getCheckoutSuccessUrl()); 
        }
        exit;
    }
}
