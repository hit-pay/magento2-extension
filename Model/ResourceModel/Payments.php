<?php

namespace SoftBuild\HitPay\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Context;

/**
 * Class Payments
 * @package SoftBuild\HitPay\Model\ResourceModel
 */
class Payments extends AbstractDb
{
    /**
     * Payments constructor.
     * @param Context $context
     */
    public function __construct(Context $context)
    {
        parent::__construct($context);
    }

    /**
     *
     */
    protected function _construct()
    {
        $this->_init('softbuild_hitpay_payments', 'id');
    }

    /**
     * @param $paymentId
     * @return false|mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getIdByPaymentId($paymentId)
    {
        $select = $this->getConnection()
            ->select()
            ->from($this->getMainTable())
            ->where('payment_id = ?', $paymentId);

        $result = $this->getConnection()->fetchRow($select);

        if (!empty($result)) {
            return $result['id'];
        }

        return false;
    }
}