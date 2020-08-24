<?php

namespace SoftBuild\HitPay\Model\Config\Backend;

use \Magento\Config\Model\Config\Backend\Image as BackendImage;

/**
 * Class Image
 * @package Soft\Build\Model\Config\Backend
 */
class Image extends BackendImage
{
    /**
     * The tail part of directory path for uploading
     *
     */
    const UPLOAD_DIR = 'hitpay'; // Folder save image

    /**
     * Return path to directory for upload file
     *
     * @return string
     * @throw \Magento\Framework\Exception\LocalizedException
     */
    protected function _getUploadDir()
    {
        return $this->_mediaDirectory->getAbsolutePath($this->_appendScopeInfo(self::UPLOAD_DIR));
    }

    /**
     * Makes a decision about whether to add info about the scope.
     *
     * @return boolean
     */
    protected function _addWhetherScopeInfo()
    {
        return true;
    }

    /**
     * Getter for allowed extensions of uploaded files.
     *
     * @return string[]
     */
    protected function _getAllowedExtensions()
    {
        return ['jpg', 'jpeg', 'gif', 'png', 'svg'];
    }
}