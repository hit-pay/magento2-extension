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

    protected $quoteFactory;
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
        \Magento\Framework\UrlInterface $url,
        \Magento\Quote\Model\QuoteFactory $quoteFactory,
        \Magento\Sales\Model\OrderFactory $orderFactory
    ) {
        $this->_pageFactory = $pageFactory;
        $this->hitPayService = $hitPayService;
        $this->quoteFactory = $quoteFactory;
        $this->orderFactory = $orderFactory;
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
        $quoteId = $this->getRequest()->getParam('cart_id');
        if ($this->getRequest()->getParam('status') == 'canceled') {
            $cartUrl = $this->_url->getUrl('checkout/index');
            $this->responseFactory->create()->setRedirect($cartUrl)->sendResponse();
            exit;
        }
        try {
            $i = 1;
            while ($i <= 3){
                $order =  $this->orderFactory->create()->load($quoteId,'quote_id');
                
                if(!empty($order->getIncrementId()))
                {
                    $quote = $this->quoteFactory->create()->load($quoteId);
                    $quote->setIsActive(0);
                    $quote->save();
                    break;
                }  
                     
            }
            sleep(3);
            if ($this->hitPayService->checkPayment()) {
                $this->_forward('success');
            }
            else {
                $this->_forward('unsuccess');
            }
        } catch (\Exception $e) {
            $this->logger->error('Unsuccess'.$e->getMessage());
            $this->_forward('unsuccess');
        }

        return $this->_pageFactory->create();
    }
}

