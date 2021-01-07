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
            if (($this->getRequest()->getParam('cart_id', false) == false)
                || ($this->getRequest()->getParam('hmac', false) == false)) {
                return false;
            }

            file_put_contents(
                '/var/www/html/log.txt',
                "\n" . print_r($this->getRequest()->getParam('cart_id', false), true) .
                "\n" . print_r($this->getRequest()->getParam('hmac', false), true) .
                "\n\nfile: " . __FILE__ .
                "\n\nline: " . __LINE__ .
                "\n\ntime: " . date('d-m-Y H:i:s'), 8
            );

            $this->hitPayService->checkData();

            exit;
        } catch (\Error | \Exception $e) {

            file_put_contents(
                         '/var/www/html/log.txt',
                        "\n" . print_r($e->getMessage(), true) .
                        "\n" . print_r($e->getFile(), true) .
                        "\n" . print_r($e->getLine(), true) .
                        "\n" . print_r($e->getTrace(), true) .
                        "\n\nfile: " . __FILE__ .
                        "\n\nline: " . __LINE__ .
                        "\n\ntime: " . date('d-m-Y H:i:s'), 8
                    );
            $this->logger->error($e->getMessage());
        }
        
        return $this->_pageFactory->create();
    }
}