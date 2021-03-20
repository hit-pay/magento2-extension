<?php

namespace SoftBuild\HitPay\Controller\Webhook;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\View\Result\Page;

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
     * @var LoggerInterface
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
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $pageFactory,
        \SoftBuild\HitPay\Services\HitPay $hitPayService,
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
            if (($this->getRequest()->getParam('order_id', false) == false)
                || ($this->getRequest()->getParam('hmac', false) == false)) {
                return false;
            }
            $this->hitPayService->checkData();

            exit;
        } catch (\Error | \Exception $e) {
            $this->logger->error($e->getMessage());
        }

        return $this->_pageFactory->create();
    }
}
