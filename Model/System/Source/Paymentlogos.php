<?php

namespace SoftBuild\HitPay\Model\System\Source;

use Magento\Framework\Option\ArrayInterface;
use Magento\Payment\Model\Method\AbstractMethod;

/**
 * Class Mode
 */
class Paymentlogos implements ArrayInterface
{
    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => 'visa',
                'label' => __('Visa')
           ],
            [
                'value' => 'master',
                'label' => __('Mastercard')
           ],
            [
                'value' => 'american_express',
                'label' => __('American Express')
           ],
            [
                'value' => 'apple_pay',
                'label' => __('Apple Pay')
           ],
            [
                'value' => 'google_pay',
                'label' => __('Google Pay')
           ],
            [
                'value' => 'paynow',
                'label' => __('PayNow QR')
           ],
            [
                'value' => 'grabpay',
                'label' => __('GrabPay')
           ],
            [
                'value' => 'wechatpay',
                'label' => __('WeChatPay')
           ],
            [
                'value' => 'alipay',
                'label' => __('AliPay')
           ],
            [
                'value' => 'shopeepay',
                'label' => __('Shopee Pay')
           ],
            [
                'value' => 'fpx',
                'label' => __('FPX')
           ],
            [
                'value' => 'zip',
                'label' => __('Zip')
           ],
            [
                'value' => 'atomeplus',
                'label' => __('ATome+')
            ],
            [
                'value' => 'unionbank',
                'label' => __('Unionbank Online')
            ],
            [
                'value' => 'qrph',
                'label' => __('Instapay QR PH')
            ],
            [
                'value' => 'pesonet',
                'label' => __('PESONet')
            ],
            [
                'value' => 'gcash',
                'label' => __('GCash')
            ],
            [
                'value' => 'billease',
                'label' => __('Billease BNPL')
            ],
            [
                'value' => 'eftpos',
                'label' => __('eftpos')
            ],
            [
                'value' => 'maestro',
                'label' => __('maestro')
            ],
            [
                'value' => 'alfamart',
                'label' => __('Alfamart')
            ],
            [
                'value' => 'indomaret',
                'label' => __('Indomaret')
            ],
            [
                'value' => 'dana',
                'label' => __('DANA')
            ],
            [
                'value' => 'gopay',
                'label' => __('gopay')
            ],
            [
                'value' => 'linkaja',
                'label' => __('Link Aja!')
            ],
            [
                'value' => 'ovo',
                'label' => __('OVO')
            ],
            [
                'value' => 'qris',
                'label' => __('QRIS')
            ],
            [
                'value' => 'danamononline',
                'label' => __('Bank Danamon')
            ],
            [
                'value' => 'permata',
                'label' => __('PermataBank')
            ],
            [
                'value' => 'bsi',
                'label' => __('Bank Syariah Indonesia')
            ],
            [
                'value' => 'bca',
                'label' => __('BCA')
            ],
            [
                'value' => 'bni',
                'label' => __('BNI')
            ],
            [
                'value' => 'bri',
                'label' => __('BRI')
            ],
            [
                'value' => 'cimb',
                'label' => __('CIMB Niaga')
            ],
            [
                'value' => 'doku',
                'label' => __('DOKU')
            ],
            [
                'value' => 'mandiri',
                'label' => __('Mandiri')
            ],
            [
                'value' => 'akulaku',
                'label' => __('AkuLaku BNPL')
            ],
            [
                'value' => 'kredivo',
                'label' => __('Kredivo BNPL')
            ],
            [
                'value' => 'philtrustbank',
                'label' => __('PHILTRUST BANK')
            ],
            [
                'value' => 'allbank',
                'label' => __('AllBank')
            ],
            [
                'value' => 'aub',
                'label' => __('ASIA UNITED BANK')
            ],
            [
                'value' => 'chinabank',
                'label' => __('CHINABANK')
            ],
            [
                'value' => 'instapay',
                'label' => __('instaPay')
            ],
            [
                'value' => 'landbank',
                'label' => __('LANDBANK')
            ],
            [
                'value' => 'metrobank',
                'label' => __('Metrobank')
            ],
            [
                'value' => 'pnb',
                'label' => __('PNB')
            ],
            [
                'value' => 'queenbank',
                'label' => __('QUEENBANK')
            ],
            [
                'value' => 'rcbc',
                'label' => __('RCBC')
            ],
            [
                'value' => 'tayocash',
                'label' => __('TayoCash')
            ],
            [
                'value' => 'ussc',
                'label' => __('USSC')
            ],
            [
                'value' => 'bayad',
                'label' => __('bayad')
            ],
            [
                'value' => 'cebuanalhuillier',
                'label' => __('CEBUANA LHUILLIER')
            ],
            [
                'value' => 'ecpay',
                'label' => __('ecPay')
            ],
            [
                'value' => 'palawan',
                'label' => __('PALAWAN PAWNSHOP')
            ],
            [
                'value' => 'bpi',
                'label' => __('BPI')
            ],
            [
                'value' => 'psbank',
                'label' => __('PSBank')
            ],
            [
                'value' => 'robinsonsbank',
                'label' => __('Robinsons Bank')
            ],
            [
                'value' => 'diners_club',
                'label' => __('Diners Club')
            ],
            [
                'value' => 'discover',
                'label' => __('Discover')
            ],
            [
                'value' => 'doku_wallet',
                'label' => __('DOKU Wallet')
            ],
            [
                'value' => 'grab_paylater',
                'label' => __('PayLater by Grab')
            ],
            [
                'value' => 'favepay',
                'label' => __('FavePay')
            ],
            [
                'value' => 'shopback_paylater',
                'label' => __('ShopBack PayLater')
            ],
            [
                'value' => 'duitnow',
                'label' => __('DuitNow')
            ],
            [
                'value' => 'touchngo',
                'label' => __('Touch \'n Go')
            ],
            [
                'value' => 'boost',
                'label' => __('Boost')
            ],
        ];
    }
}
