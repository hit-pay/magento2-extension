<?php

namespace SoftBuild\HitPay\Block;

use Magento\Framework\View\Element\Template;
use Magento\Sales\Model\OrderFactory;


class Success extends Template
{
    protected $_storeManager;
    protected $_session;
    protected $_orderFactory; 
    protected $customerFactory;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Checkout\Model\Session $session,
         \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderFactory,
         \Magento\Customer\Model\CustomerFactory $customerFactory,
         OrderFactory $orderCollectionFactory,
        array $data = []
    ) {
        $this->_storeManager = $storeManager;
        $this->_session = $session;
        $this->_orderFactory = $orderFactory;
        $this->orderCollectionFactory = $orderCollectionFactory;
        $this->customerFactory = $customerFactory;
        parent::__construct($context, $data);
    }
    public function getBaseUrl() {

        return $this->_storeManager->getStore()->getBaseUrl();
    }
    public function getOrderDetails(){
        
        $incerementIdArray = [];
        
        $orderCollection =$this->_orderFactory->create(); 
        foreach($orderCollection as $orders){
            $incerementIdArray = $orders->getData('increment_id');
        }
        return $incerementIdArray;

    }

    public function getCustomerEmail($lastOrderId){
        $order = $this->orderCollectionFactory->create()->loadByIncrementId($lastOrderId);
        return $order->getCustomerEmail();
    }

    public function getGuestCustomer($customerEmail){

        $customerId = null;
        $websiteId = $this->_storeManager->getStore()->getWebsiteId();
        $customer = $this->customerFactory->create();
        $customer->setWebsiteId($websiteId);
        $customer->loadByEmail($customerEmail);
        if($customer->getId())
        {
            $customerId = $customer->getId();
            return $customerId;
        }

        return $customerId;
    }


    
}
