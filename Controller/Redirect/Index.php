<?php

namespace SoftBuild\HitPay\Controller\Redirect;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\View\Result\Page;

/**
 * Class Index
 * @package SoftBuild\HitPay\Controller\Redirect
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
     * Index constructor.
     * @param Context $context
     * @param PageFactory $pageFactory
     * @param HitPay $hitPayService
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $pageFactory,
        \SoftBuild\HitPay\Services\HitPay $hitPayService
    ) {
        $this->_pageFactory = $pageFactory;
        $this->hitPayService = $hitPayService;

        return parent::__construct($context);
    }

    /**
     * @return ResponseInterface|ResultInterface|Page
     */
    public function execute()
    {
        try {
            if ($link = $this->hitPayService->createRequest()) {
                $this->_redirect($link);
            }
        } catch (\Exception $e) {
            return $this->_pageFactory->create();
        }
    }
}