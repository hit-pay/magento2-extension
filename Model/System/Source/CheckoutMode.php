<?php

namespace SoftBuild\HitPay\Model\System\Source;

use Magento\Framework\Option\ArrayInterface;
use Magento\Payment\Model\Method\AbstractMethod;

/**
 * Class CheckoutMode
 */

class CheckoutMode implements ArrayInterface
{
    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => 1,
                'label' => __('Drop-In (Popup)')
            ],
            [
                'value' => 0,
                'label' => __('Redirect')
            ]
        ];
    }
}
