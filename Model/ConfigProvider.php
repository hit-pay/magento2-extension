<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace SoftBuild\HitPay\Model;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Framework\Escaper;
use Magento\Payment\Helper\Data as PaymentHelper;
use SoftBuild\HitPay\Model\System\Source\Paymentlogos;
use Magento\Framework\View\Asset\Repository;
use Magento\Framework\Serialize\SerializerInterface;

class ConfigProvider implements ConfigProviderInterface
{
    /**
     * @var string[]
     */
    protected $methodCodes = [
        'hitpay'
    ];

    /**
     * @var \Magento\Payment\Model\Method\AbstractMethod[]
     */
    protected $methods = [];

    /**
     * @var Escaper
     */
    protected $escaper;
    
    protected $paymentlogos;
    
    protected $assetRepo;

    protected $serializer;

    /**
     * @param PaymentHelper $paymentHelper
     * @param Escaper $escaper
     */
    public function __construct(
        PaymentHelper $paymentHelper,
        Escaper $escaper,
        Paymentlogos $paymentlogos,
        Repository $assetRepo,
        SerializerInterface $serializer
    ) {
        $this->escaper = $escaper;
        $this->paymentlogos = $paymentlogos;
        $this->assetRepo = $assetRepo;
        $this->serializer = $serializer;
        
        foreach ($this->methodCodes as $code) {
            $this->methods[$code] = $paymentHelper->getMethodInstance($code);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getConfig()
    {
        $config = [];
        foreach ($this->methodCodes as $code) {
            if ($this->methods[$code]->isAvailable()) {
                $config['payment']['instructions'][$code] = $this->getInstructions($code);
                $config['payment'][$code]['redirectUrl'] = $this->methods[$code]->getCheckoutRedirectUrl();
                $config['payment'][$code]['cartUrl'] = $this->methods[$code]->getCheckoutCartUrl();
                $config['payment'][$code]['images'] = $this->getLogos($code);
                $config['payment'][$code]['status'] = $this->getLogosStatus($code);
                $config['payment'][$code]['dropIn'] = (int)$this->methods[$code]->getConfigValue('checkout_mode');
                $config['payment'][$code]['pos_enabled'] = $this->getPosStatus($code);

                $terminalIds = $this->filterTerminalIds($code);
                $config['payment'][$code]['terminal_ids'] = $terminalIds;

                if (!$terminalIds) {
                    $config['payment'][$code]['total_terminal_ids'] = 0;
                    $config['payment'][$code]['only_one_terminal_id'] = 0;
                } else {
                    $totalTerminalIds = count($terminalIds);
                    $config['payment'][$code]['total_terminal_ids'] = $totalTerminalIds;
                    if ($totalTerminalIds == 1) {
                        $config['payment'][$code]['only_one_terminal_id'] = 1;
                    } else {
                        $config['payment'][$code]['only_one_terminal_id'] = 0;
                    }
                }
            }
        }
        return $config;
    }

    /**
     * Get instructions text from config
     *
     * @param string $code
     * @return string
     */
    protected function getInstructions($code)
    {
        return nl2br($this->escaper->escapeHtml($this->methods[$code]->getInstructions()));
    }
    
    public function getLogos($code)
    {
        $pngs = [
            'pesonet',
            'eftpos',
            'doku',
            'philtrustbank',
            'allbank',
            'aub',
            'chinabank',
            'instapay',
            'landbank',
            'metrobank',
            'pnb',
            'queenbank',
            'ussc',
            'bayad',
            'cebuanalhuillier',
            'psbank',
            'robinsonsbank',
            'doku_wallet',
            'favepay',
            'shopback_paylater'
        ];
        $images = [];
        foreach ($this->methodCodes as $code) {
            $enabledLogos = $this->methods[$code]->getConfigValue('paymentlogos');
            if (!empty($enabledLogos)) {
                $enabledLogos = explode(',', $enabledLogos);
                foreach ($enabledLogos as $logoCode) {
                    $extn = 'svg';
                    if (in_array($logoCode, $pngs)) {
                        $extn = 'png';
                    }
                    $images[$logoCode] = $this->assetRepo->getUrl('SoftBuild_HitPay::images/'.$logoCode.'.'.$extn);
                }
            }
        }
        return $images;
    }
    
    public function getLogosStatus($code)
    {
        $status = [];
        foreach ($this->methodCodes as $code) {
            $enabledLogos = $this->methods[$code]->getConfigValue('paymentlogos');
            if (!empty($enabledLogos)) {
                $enabledLogos = explode(',', $enabledLogos);
            } else {
                $enabledLogos = [];
            }
            $logos = $this->paymentlogos->toOptionArray();
            foreach ($logos as $logo) {
                $logoCode = $logo['value'];
                $status[$logoCode] = (int)(in_array($logoCode, $enabledLogos));
            }
        }
        return $status;
    }

    public function getPosStatus($code)
    {
        $status = 0;

        $status = (int)$this->methods[$code]->getConfigValueByCode('hitpay_pos','active');

        if ($status) {
            $posTerminals = $this->getTerminals($code);
            if (!$posTerminals) {
                $status = 0;
            }
        }

        return $status;
    }

    public function getTerminals($code)
    {

        $data = $this->methods[$code]->getConfigValueByCode('hitpay_pos','pos_terminals');

        $posTerminals = $this->serializer->unserialize($data);

        if (is_array($posTerminals) && count($posTerminals) > 0) {
            return $posTerminals;
        }

        return false;
    }

    public function filterTerminalIds($code)
    {
        $filteredTerminalIds = array();

        $posTerminals = $this->getTerminals($code);

        if (!$posTerminals) {
            return false;
        }

        $quote = $this->methods[$code]->getQuote();

        if ($quote && $quote->getId() > 0) {
            $email = $quote->getCustomerEmail();

            if (!empty($email)) {
                $i = 0;
                foreach($posTerminals as $value) {
                    if($value['terminal_email'] == $email) {
                        $filteredTerminalIds[$i++] = $value['terminal_id'];
                    }
                }
            }
        }
    
        if (count($filteredTerminalIds) == 0) {
            $i = 0;
            foreach($posTerminals as $value) {
                $filteredTerminalIds[$i++] = $value['terminal_id'];
            }
        }

        return $filteredTerminalIds;
    }
}
