<?php
namespace SoftBuild\HitPay\Block\Adminhtml\Sales\Order;

use SoftBuild\HitPay\Services\Client;

class View extends \Magento\Backend\Block\Template
{
    protected $helper;
    protected $request;
    protected $orderFactory;
    protected $priceHelper;


    public function __construct(
        \SoftBuild\HitPay\Helper\Data $helper,
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Framework\Pricing\Helper\Data $priceHelper,
        \Magento\Backend\Block\Template\Context $context,
        array $data = []
    ) {
        $this->helper = $helper;
        $this->request = $request;
        $this->orderFactory = $orderFactory;
        $this->priceHelper = $priceHelper;
        parent::__construct($context, $data);
    }

    public function getResponseValues()
    {
        $order_id = (int)$this->request->getParam('order_id');
        $order = $this->orderFactory->create()->load($order_id);
        $payment = $order->getPayment();
        $model = $payment->getMethodInstance();
        $method = $model->getCode();
        if ($method == 'hitpay') {
            $payment_request_id = $this->helper->getPaymentResponseSingle($order->getId(), 'payment_request_id');
            if (!empty($payment_request_id)) {
                $payment_method = $this->helper->getPaymentResponseSingle($order->getId(), 'payment_type');
                $fees = $this->helper->getPaymentResponseSingle($order->getId(), 'fees');
                $fees_currency = $this->helper->getPaymentResponseSingle($order->getId(), 'fees_currency');
                if (empty($payment_method) || empty($fees) || empty($fees_currency)) {
                    $client = new Client(
                        $model->getConfigValue("api_key", true, $order->getStoreId()),
                        $model->getConfigValue("mode", true, $order->getStoreId())
                    );
                    
                    try {
                        $paymentStatus = $client->getPaymentStatus($payment_request_id);
                        if ($paymentStatus) {
                            $payments = $paymentStatus->payments;
                            if (isset($payments[0])) {
                                $payment = $payments[0];
                                $payment_method = $payment->payment_type;
                                $fees = $payment->fees;
                                $fees_currency = $payment->fees_currency;
                                $this->helper->updatePaymentData($order->getId(), 'payment_type', $payment_method);
                                $this->helper->updatePaymentData($order->getId(), 'fees', $fees);
                                $this->helper->updatePaymentData($order->getId(), 'fees_currency', $fees_currency);
                            }
                        }
                    } catch (\Exception $e) {
                        $payment_method = $e->getMessage();
                    }
                }
                $response['payment_method'] = ucwords(str_replace("_", " ", $payment_method));
                $response['hitpay_fee'] = $fees;
                $response['hitpay_fee_currency'] = strtoupper($fees_currency);
                return $response;
            }
        }
        return false;
    }
}
