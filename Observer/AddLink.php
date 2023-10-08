<?php
namespace SoftBuild\HitPay\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\View\Page\Config;

class AddLink implements ObserverInterface
{
    protected $pageConfig;

    public function __construct(
        \Magento\Framework\View\Page\Config $pageConfig
    ) {
        $this->pageConfig = $pageConfig;
    }
 
    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $model = $objectManager->get('SoftBuild\HitPay\Model\Pay');
        if ($this->pageConfig) {
            if ($model->getConfigValue('checkout_mode')) {
                $dropin_js = 'https://sandbox.hit-pay.com/hitpay.js';
                if ($model->getConfigValue('mode')) {
                    $dropin_js = 'https://hit-pay.com/hitpay.js';
                }
                $this->pageConfig->addRemotePageAsset(
                    $dropin_js,
                    'js',
                    ['attributes' => ['async' => true]]
                );
            }
        }
    }
}
