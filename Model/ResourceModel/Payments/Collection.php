<?php

namespace SoftBuild\HitPay\Model\ResourceModel\Payments;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

/**
 * Class Collection
 * @package SoftBuild\HitPay\Model\ResourceModel\Payments
 */
class Collection extends AbstractCollection
{
    protected $_idFieldName = 'id';

    protected $_eventPrefix = 'softbuild_hitpay_payments';

    protected $_eventObject = 'payments_collection';

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('SoftBuild\HitPay\Model\Payments', 'SoftBuild\HitPay\Model\ResourceModel\Payments');
    }

}