<?php
/**
 * Copyright © 2020 PAY. All rights reserved.
 */

namespace Paynl\Payment\Model;

use Magento\Store\Model\Store;

/**
 * Description of Config
 *
 * @author Andy Pieters <andy@pay.nl>
 */
class Config
{
    const FINISH_PAYLINK = 'paynl/checkout/paylink';
    const FINISH_STANDARD = 'checkout/onepage/success';
    const ORDERSTATUS_PAID = 100;
    const ORDERSTATUS_DENIED = -63;

    /** @var  Store */
    private $store;

    /** @var  Resources */
    private $resources;

    /** @array  Brands */
    public $brands = array(
        "paynl_payment_afterpay" => "14",
        "paynl_payment_alipay" => "82",
        "paynl_payment_amazonpay" => "22",
        "paynl_payment_amex" => "9",
        "paynl_payment_applepay" => "114",
        "paynl_payment_billink" => "16",
        "paynl_payment_capayable" => "18",
        "paynl_payment_capayable_gespreid" => "19",
        "paynl_payment_cartasi" => "76",
        "paynl_payment_cartebleue" => "11",
        "paynl_payment_cashly" => "43",
        "paynl_payment_clickandbuy" => "1",
        "paynl_payment_creditclick" => "99",
        "paynl_payment_dankort" => "58",
        "paynl_payment_decadeaukaart" => "189",
        "paynl_payment_eps" => "79",
        "paynl_payment_fashioncheque" => "27",
        "paynl_payment_fashiongiftcard" => "28",
        "paynl_payment_focum" => "17",
        "paynl_payment_gezondheidsbon" => "30",
        "paynl_payment_giropay" => "3",
        "paynl_payment_givacard" => "61",
        "paynl_payment_good4fun" => "207",
        "paynl_payment_googlepay" => "176",
        "paynl_payment_huisentuincadeau" => "117",
        "paynl_payment_ideal" => "1",
        "paynl_payment_instore" => "164",
        "paynl_payment_klarna" => "15",
        "paynl_payment_klarnakp" => "15",
        "paynl_payment_maestro" => "33",
        "paynl_payment_mistercash" => "2",
        "paynl_payment_multibanco" => "141",
        "paynl_payment_mybank" => "5",
        "paynl_payment_overboeking" => "12",
        "paynl_payment_payconiq" => "138",
        "paynl_payment_paypal" => "21",
        "paynl_payment_paysafecard" => "24",
        "paynl_payment_podiumcadeaukaart" => "29",
        "paynl_payment_postepay" => "10",
        "paynl_payment_przelewy24" => "93",
        "paynl_payment_sofortbanking" => "4",
        "paynl_payment_sofortbanking_hr" => "4",
        "paynl_payment_sofortbanking_ds" => "4",
        "paynl_payment_spraypay" => "20",
        "paynl_payment_telefonischbetalen" => "173",
        "paynl_payment_tikkie" => "84",
        "paynl_payment_trustly" => "213",
        "paynl_payment_visamastercard" => "7",
        "paynl_payment_vvvgiftcard" => "25",
        "paynl_payment_webshopgiftcard" => "26",
        "paynl_payment_wechatpay" => "23",
        "paynl_payment_wijncadeau" => "135",
        "paynl_payment_yehhpay" => "1",
        "paynl_payment_yourgift" => "31"
    );

    public function __construct(
        Store $store,
        \Magento\Framework\View\Element\Template $resources
    ) {
        $this->store = $store;
        $this->resources = $resources;
    }

    /**
     * @param string $defaultValue
     * @return mixed|string
     */
    public function getVersion($defaultValue = '')
    {
        $composerFilePath = sprintf('%s/%s', rtrim(__DIR__, '/'), '../composer.json');
        if (file_exists($composerFilePath)) {
            $composer = json_decode(file_get_contents($composerFilePath), true);

            if (isset($composer['version'])) {
                return $composer['version'];
            }
        }

        return $defaultValue;
    }

    public function getMagentoVersion()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $productMetadata = $objectManager->get('Magento\Framework\App\ProductMetadataInterface');
        $version = $productMetadata->getVersion();

        return $version;
    }

    public function getPHPVersion()
    {
        return substr(phpversion(), 0, 3);
    }

    /**
     * @param Store $store
     */
    public function setStore($store)
    {
        $this->store = $store;
    }

    public function isSkipFraudDetection()
    {
        return $this->store->getConfig('payment/paynl/skip_fraud_detection') == 1;
    }

    public function isTestMode()
    {
        $remoteIP =  isset($_SERVER['HTTP_X_FORWARDED_FOR']) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : $_SERVER['REMOTE_ADDR'];
        $ip = isset($_SERVER['HTTP_CLIENT_IP']) ? $_SERVER['HTTP_CLIENT_IP'] : $remoteIP;

        $ipconfig = $this->store->getConfig('payment/paynl/testipaddress');
        $allowed_ips = explode(',', $ipconfig);
        if(in_array($ip, $allowed_ips) && filter_var($ip, FILTER_VALIDATE_IP) && strlen($ip) > 0 && count($allowed_ips) > 0){
            return true;
        }
        return $this->store->getConfig('payment/paynl/testmode') == 1;
    }

    public function isSendDiscountTax()
    {
        return $this->store->getConfig('payment/paynl/discount_tax') == 1;
    }

    public function isNeverCancel()
    {
        return $this->store->getConfig('payment/paynl/never_cancel') == 1;
    }

    public function isAlwaysBaseCurrency()
    {
        return $this->store->getConfig('payment/paynl/always_base_currency') == 1;
    }

    public function useMagOrderAmountForAuth()
    {
        return $this->store->getConfig('payment/paynl/use_magorder_for_auth') == 1;
    }

    public function getLanguage()
    {
        $language = $this->store->getConfig('payment/paynl/language');

        return $language ? $language : 'nl'; //default nl
    }

    public function getPaymentOptionId($methodCode)
    {
        return $this->store->getConfig('payment/' . $methodCode . '/payment_option_id');
    }

    public function getPendingStatus($methodCode)
    {
        return $this->store->getConfig('payment/' . $methodCode . '/order_status');
    }

    public function getAuthorizedStatus($methodCode)
    {
        return $this->store->getConfig('payment/' . $methodCode . '/order_status_authorized');
    }

    public function getPaidStatus($methodCode)
    {
        return $this->store->getConfig('payment/' . $methodCode . '/order_status_processing');
    }

    public function ignoreB2BInvoice($methodCode)
    {
        return $this->store->getConfig('payment/' . $methodCode . '/turn_off_invoices_b2b') == 1;
    }

    public function autoCaptureEnabled()
    {
       return $this->store->getConfig('payment/paynl/auto_capture') == 1;
    }

    /**
     * @param $methodCode string
     * @return string
     */
    public function getSuccessPage($methodCode)
    {
        return $this->store->getConfig('payment/' . $methodCode . '/custom_success_page');
    }

    /**
     * Configures the sdk with the API token and serviceId
     *
     * @return bool TRUE when config loaded, FALSE when the apitoken or serviceId are empty
     */
    public function configureSDK()
    {
        $apiToken  = $this->getApiToken();
        $serviceId = $this->getServiceId();
        $tokencode = $this->getTokencode();
        $gateway = $this->getFailoverGateway();

        if (!empty($tokencode)) {
            \Paynl\Config::setTokenCode($tokencode);
        }

        if (!empty($apiToken) && !empty($serviceId)) {
            if (!empty(trim($gateway)) && substr(trim($gateway), 0, 4) === "http") {
                \Paynl\Config::setApiBase(trim($gateway));
            }
            \Paynl\Config::setApiToken($apiToken);
            \Paynl\Config::setServiceId($serviceId);

            return true;
        }

        return false;
    }

    public function getApiToken()
    {
        return trim($this->store->getConfig('payment/paynl/apitoken'));
    }

    public function getTokencode()
    {
        return trim($this->store->getConfig('payment/paynl/tokencode'));
    }

    public function getServiceId()
    {
        return trim($this->store->getConfig('payment/paynl/serviceid'));
    }

    public function getFailoverGateway()
    {
        return $this->store->getConfig('payment/paynl/failover_gateway');
    }
    
    public function getIconUrl($methodCode, $paymentOptionId)
    {
        if ($this->store->getConfig('payment/paynl/image_style') == 'newest') {
            $brandId = $this->store->getConfig('payment/' . $methodCode . '/brand_id');
            if (empty($brandId)) {
                $brandId = $this->brands[$methodCode];
            }
            $iconUrl = $this->resources->getViewFileUrl("Paynl_Payment::logos/" . $brandId . ".png");
        } else {
            $iconsize = '50x32';
            if($this->store->getConfig('payment/paynl/pay_style_checkout') == 1){
                switch($this->store->getConfig('payment/paynl/icon_size')){
                    case 'xlarge': $iconsize = '100x100'; break;
                    case 'large':  $iconsize = '75x75';   break;
                    case 'medium': $iconsize = '50x50';   break;                     
                }          
            }
            $iconUrl = 'https://static.pay.nl/payment_profiles/' . $iconsize . '/' . $paymentOptionId . '.png';
            $customUrl = trim($this->store->getConfig('payment/paynl/iconurl'));
            if (!empty($customUrl)) {
                $iconUrl = str_replace('#paymentOptionId#', $paymentOptionId, $customUrl);
            }
        }

        return $iconUrl;
    }

    public function getIconSize()
    {        
        if($this->store->getConfig('payment/paynl/pay_style_checkout') == 1 && $this->store->getConfig('payment/paynl/image_style') == 'newest'){
            return $this->store->getConfig('payment/paynl/icon_size');
        }
        return false;
    }

    public function getUseAdditionalValidation()
    {
        return trim($this->store->getConfig('payment/paynl/use_additional_validation'));
    }

    public function getCancelURL()
    {
        return $this->store->getConfig('payment/paynl/cancelurl');
    }

    public function getDefaultPaymentOption()
    {
        return $this->store->getConfig('payment/paynl/default_payment_option');
    }

    public function registerPartialPayments()
    {
        return $this->store->getConfig('payment/paynl/register_partial_payments');
    }

    public function getPaymentmethodCode($paymentProfileId){

        //Get all PAY. methods
        $objectManager =  \Magento\Framework\App\ObjectManager::getInstance();
        $paymentHelper = $objectManager->get('Magento\Payment\Helper\Data');
        $paymentMethodList = $paymentHelper->getPaymentMethods();
        $pay_methods = array();
        foreach ($paymentMethodList as $key => $value) {
            if (strpos($key, 'paynl_') !== false && $key != 'paynl_payment_paylink') {
                $code = $this->store->getConfig('payment/' . $key . '/payment_option_id');
                if($code == $paymentProfileId){
                    return $key;
                }
            }
        }
    }
}
