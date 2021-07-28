<?php
namespace SoftBuild\HitPay\Controller\Confirmation;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\View\Result\Page;
use Magento\Framework\View\Result\PageFactory;
use SoftBuild\HitPay\Services\HitPay;

class Success extends \Magento\Framework\App\Action\Action
{
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
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $_pageFactory;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     */
    public function __construct(
       \Magento\Framework\App\Action\Context $context,
       \Magento\Framework\View\Result\PageFactory $pageFactory,
       HitPay $hitPayService,
       \Magento\Framework\App\ResponseFactory $responseFactory,
       \Magento\Framework\UrlInterface $url
    )
    {
        $this->_pageFactory = $pageFactory;
        $this->hitPayService = $hitPayService;
        $this->responseFactory = $responseFactory;
        $this->url = $url;
        return parent::__construct($context);
    }
    /**
     * View page action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $cartObject = $objectManager->create('Magento\Checkout\Model\Cart')->truncate();
        $cartObject->saveQuote();

        try {
            $this->hitPayService->checkPayment();
        } catch (\Exception $e) {
            $this->logger->debug($e->getMessage());
        }

        return $this->_pageFactory->create();
    }
}