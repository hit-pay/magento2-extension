<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace SoftBuild\HitPay\Model\Ui;

use Magento\Checkout\Model\ConfigProviderInterface;
use SoftBuild\HitPay\Gateway\Http\Client\ClientMock;

/**
 * Class ConfigProvider
 */
final class ConfigProvider implements ConfigProviderInterface
{
    const CODE = 'hitpay_gateway';

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var \Magento\Framework\View\Asset\Repository
     */
    private $assetRepo;

    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\View\Asset\Repository $assetRepo
    )
    {
        $this->scopeConfig = $scopeConfig;
        $this->assetRepo = $assetRepo;
    }

    /**
     * Retrieve assoc array of checkout configuration
     *
     * @return array
     */
    public function getConfig()
    {
        return [
            'payment' => [
                self::CODE => [
                    'transactionResults' => [
                        ClientMock::SUCCESS => __('Success'),
                        ClientMock::FAILURE => __('Fraud')
                    ],
                    'images' => [
                        'paynow' => $this->assetRepo->getUrl('SoftBuild_HitPay::images/paynow.png'),
                        'wechat' => $this->assetRepo->getUrl('SoftBuild_HitPay::images/wechat.png'),
                        'cards' => $this->assetRepo->getUrl('SoftBuild_HitPay::images/cards.png'),
                    ],
                    'paynow' => (int)$this->scopeConfig->getValue('payment/hitpay_gateway/paynow'),
                    'wechat' => (int)$this->scopeConfig->getValue('payment/hitpay_gateway/wechat'),
                    'cards' => (int)$this->scopeConfig->getValue('payment/hitpay_gateway/cards'),
                ]
            ]
        ];
    }
}
