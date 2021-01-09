<?php

namespace SoftBuild\HitPay\Controller\Confirmation;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\View\Result\Page;
use Magento\Framework\View\Result\PageFactory;
use SoftBuild\HitPay\Services\HitPay;

/**
 * Class Index
 * @package SoftBuild\HitPay\Controller\Confirmation
 */
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
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @var \Magento\Framework\App\ResponseFactory
     */
    private $responseFactory;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    private $url;

    /**
     * Index constructor.
     * @param Context $context
     * @param PageFactory $pageFactory
     * @param HitPay $hitPayService
     */
    public function __construct(
        Context $context,
        PageFactory $pageFactory,
        HitPay $hitPayService,
        \Magento\Framework\App\ResponseFactory $responseFactory,
        \Magento\Framework\UrlInterface $url
    ) {
        $this->_pageFactory = $pageFactory;
        $this->hitPayService = $hitPayService;
        $this->responseFactory = $responseFactory;
        $this->url = $url;

        return parent::__construct($context);
    }

    /**
     * @return ResponseInterface|ResultInterface|Page
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute()
    {
        if ($this->getRequest()->getParam('status') == 'canceled') {
            $cartUrl = $this->_url->getUrl('checkout/index');
            $this->responseFactory->create()->setRedirect($cartUrl)->sendResponse();
            exit;
        }

        try {
            if (!$this->hitPayService->checkPayment()) {
                $this->_forward('unsuccess');
            }
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
            $this->_forward('unsuccess');
        }

        return $this->_pageFactory->create();
    }
}
