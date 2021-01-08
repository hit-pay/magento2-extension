<?php

namespace SoftBuild\HitPay\Api;

/**
 * Interface ApiProviderInterface
 * @package SoftBuild\HitPay\Api
 */
interface ApiProviderInterface
{
    /**
     * @param int $cart_id
     * @return mixed
     */
    public function execute(int $cart_id);
}
