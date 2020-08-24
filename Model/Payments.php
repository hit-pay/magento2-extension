<?php

namespace SoftBuild\HitPay\Model;

use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Model\AbstractModel;

/**
 * Class Payments
 * @package SoftBuild\HItPay\Model
 */
class Payments extends AbstractModel implements IdentityInterface
{
    const CACHE_TAG = 'softbuild_hitpay_payments';

    protected $_cacheTag = 'softbuild_hitpay_payments';

    protected $_eventPrefix = 'softbuild_hitpay_payments';

    /**
     *
     */
    protected function _construct()
    {
        $this->_init('SoftBuild\HitPay\Model\ResourceModel\Payments');
    }

    /**
     * Return unique ID(s) for each object in system
     *
     * @return string[]
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

    /**
     * @return array
     */
    public function getDefaultValues()
    {
        return [];
    }
}