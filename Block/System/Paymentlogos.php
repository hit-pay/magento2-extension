<?php

namespace SoftBuild\HitPay\Block\System;

use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;

class Paymentlogos extends Field
{
    /**
     * @param AbstractElement $element
     * @return string
     */
    protected function _getElementHtml(AbstractElement $element)
    {
        // @codingStandardsIgnoreLine
        $script = " <script>
                require([
                    'jquery',
                    'chosen'
                ], function ($, chosen) {
                    $('#" . $element->getId() . "').chosen({
                        max_selected_options:10,
                        width: '100%',
                        placeholder_text: '" . __('Select Logos') . "'
                    });

                })
            </script>";
        return parent::_getElementHtml($element) . $script;
    }
}
