<?php
namespace SoftBuild\HitPay\Block\Adminhtml\Form\Field;

use Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray;

class POSFields extends AbstractFieldArray
{
    protected function _prepareToRender()
    {
        $this->addColumn('terminal_id', ['label' => __('Terminal ID'), 'class' => 'required-entry']);
        $this->addColumn('terminal_email', ['label' => __('Email (Optional)'), 'class' => '']);        
        $this->_addAfter = false;
        $this->_addButtonLabel = __('Add New');
    }
}
