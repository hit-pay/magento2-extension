<?php

namespace SoftBuild\HitPay\Block;

use Magento\Framework\View\Element\Template;


class Success extends Template
{
    protected $_storeManager;
  
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Checkout\Model\Session $session,
        array $data = []
    ) {
        $this->_storeManager = $storeManager;
        $this->_session = $session;
        parent::__construct($context, $data);
    }
    public function getBaseUrl() {

        return $this->_storeManager->getStore()->getBaseUrl();
    }
    
}
