<?php

namespace SoftBuild\HitPay\Model;

use SoftBuild\HitPay\Api\ApiProviderInterface;

class Webhook implements ApiProviderInterface
{
    /**
     * @var \Magento\Framework\Webapi\Rest\Request
     */
    private $request;

    /**
     * @var \SoftBuild\HitPay\Services\HitPay
     */
    private $hitPayService;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    public function __construct(
        \Magento\Framework\Webapi\Rest\Request $request,
        \SoftBuild\HitPay\Services\HitPay $hitPayService,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->request = $request;
        $this->hitPayService = $hitPayService;
        $this->logger = $logger;
    }

    /**
     * @param int $cart_id
     * @return mixed|void
     */
    public function execute(int $cart_id)
    {
        try {
            if (($this->request->getParam('cart_id', false) == false)
                || ($this->request->getParam('hmac', false) == false)) {
                return false;
            }
            $this->hitPayService->checkData();
            exit;
        } catch (\Error | \Exception $e) {
            $this->logger->error($e->getMessage());
            throw new \Exception($e->getMessage());
        }
    }
}
