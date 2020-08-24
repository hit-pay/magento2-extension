<?php

namespace SoftBuild\HitPay\Controller\Webhook;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\View\Result\Page;
use Magento\Framework\View\Result\PageFactory;
use SoftBuild\HItPay\Services\HitPay;

class Index extends Action
{

    /**
     * @var PageFactory
     */
    protected $_pageFactory;
    /**
     * @var HitPay
     */
    protected $hitPayService;
    /**
     * @var \Magento\Quote\Model\QuoteFactory
     */
    private $quoteFactory;
    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * Index constructor.
     * @param Context $context
     * @param PageFactory $pageFactory
     * @param HitPay $hitPayService
     * @param \Magento\Quote\Model\QuoteFactory $quoteFactory
     */
    public function __construct(
        Context $context,
        PageFactory $pageFactory,
        HitPay $hitPayService,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->_pageFactory = $pageFactory;
        $this->hitPayService = $hitPayService;
        $this->logger = $logger;

        return parent::__construct($context);
    }

    /**
     * @return ResponseInterface|ResultInterface|Page
     * @throws \Exception
     */
    public function execute()
    {
        try {
            if (($this->getRequest()->getParam('cart_id', false) == false)
                || ($this->getRequest()->getParam('hmac', false) == false)) {
                return false;
            }

            $this->hitPayService->checkData();

            exit;
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
        }
        
        return $this->_pageFactory->create();
    }
}