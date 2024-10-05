<?php
namespace SoftBuild\HitPay\Observer;

use Magento\Framework\Event\Observer;
use Magento\Payment\Observer\AbstractDataAssignObserver;
use Magento\Quote\Api\Data\PaymentInterface;
use Magento\Framework\DataObject;

class AddAdditionalData extends AbstractDataAssignObserver
{
   /**
     * @param Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $dataObject = $this->readDataArgument($observer);

        $additionalData = $dataObject->getData(PaymentInterface::KEY_ADDITIONAL_DATA);

        if (!is_array($additionalData)) {
            return;
        }

        $additionalData = new DataObject($additionalData);
        
        $hitpay_payment_option = $additionalData->getData('hitpay_payment_option');

        $paymentModel = $this->readPaymentModelArgument($observer);
    
        $paymentModel->setAdditionalInformation(
            'hitpay_payment_option',
            $hitpay_payment_option
        );
    }
}
