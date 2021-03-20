<?php

namespace SoftBuild\HitPay\Api;

/**
 * Interface ApiProviderInterface
 * @package SoftBuild\HitPay\Api
 */
interface ApiProviderInterface
{
    /**
     * @param int $order_id
     * @return mixed
     */
    public function execute(int $order_id);
}
