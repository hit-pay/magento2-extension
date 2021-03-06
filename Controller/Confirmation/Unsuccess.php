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
 * Class Unsuccess
 * @package SoftBuild\HitPay\Controller\Confirmation
 */
class Unsuccess extends Action
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
     * Index constructor.
     * @param Context $context
     * @param PageFactory $pageFactory
     * @param HitPay $hitPayService
     */
    public function __construct(
        Context $context,
        PageFactory $pageFactory,
        HitPay $hitPayService
    ) {
        $this->_pageFactory = $pageFactory;
        $this->hitPayService = $hitPayService;

        return parent::__construct($context);
    }

    /**
     * @return ResponseInterface|ResultInterface|Page
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute()
    {
        try {
            $this->hitPayService->checkPayment();
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
        }

        return $this->_pageFactory->create();
    }
}