<?php
class Akatus_Akatus_Block_Form_Pay extends Mage_Payment_Block_Form
{
	protected function _construct()
	{
		parent::_construct();
		$this->setTemplate('akatus/form/pay.phtml');
	}
    
    public function getParcelamentoUrl()
    {
        return Akatus_Akatus_Helper_Data::getParcelamentoUrl();
    }
    
    public function getMeiosPagamentoUrl()
    {
        return Akatus_Akatus_Helper_Data::getMeiosPagamentoUrl();
    }

    public function getMeiosPagamento()
    {
        $cache = Mage::app()->getCache();
        $use_cache = (true === Mage::app()->useCache('akatus_akatus'));
        if($use_cache && $from_cache = $cache->load('akatus_meiosdepagamento')){
            return unserialize($from_cache);
        }

        $json = array("meios_de_pagamento" => array("correntista"=>array("api_key"=>$this->getMethod()->getConfigData('api_key'),
            "email"=>$this->getMethod()->getConfigData('email_gateway'))));
        $toJson = json_encode($json);

        $url = $this->getMeiosPagamentoUrl();

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL,$url);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_USERAGENT, "Mozilla/4.0");
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $toJson);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        $retJson = curl_exec($curl);
        curl_close($curl);
        $meios = array();
        if($retJson){
            $retornoArray = json_decode($retJson);

            $meios = $retornoArray->resposta->meios_de_pagamento;

            $cache->save(serialize($meios), 'akatus_meiosdepagamento', array('akatus'), 60*60);
        }
        return $meios;

    }

    public function getParcelamentos(){
        $cache = Mage::app()->getCache();
        $use_cache = (true === Mage::app()->useCache('akatus_akatus'));

        if($use_cache && $from_cache = $cache->load('akatus_parcelamentos')){
            return unserialize($from_cache);
        }

        $tokens = array(
            '{EMAIL}',
            '{API_KEY}',
            '{AMOUNT}'
        );

        $valores = array(
            $this->getMethod()->getConfigData('email_gateway'),
            $this->getMethod()->getConfigData('api_key'),
            Mage::getSingleton('checkout/cart')->getQuote()->getGrandTotal()
        );

        $preUrl = $this->getParcelamentoUrl();
        $url = str_replace($tokens, $valores, $preUrl);

        $curl2 = curl_init($url);
        curl_setopt($curl2, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl2, CURLOPT_POST, false);
        curl_setopt($curl2, CURLOPT_RETURNTRANSFER, true);
        $ret = curl_exec($curl2);
        $err = curl_error($curl2);
        curl_close($curl2);

        $parcelamentos = json_decode($ret, true);
        $cache->save(serialize($parcelamentos), 'akatus_parcelamentos', array('akatus'), 60*60);

        return $parcelamentos;

    }
}