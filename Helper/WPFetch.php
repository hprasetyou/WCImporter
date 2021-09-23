<?php
namespace Hprasetyou\WCImporter\Helper;
use \Magento\Framework\App\Helper\AbstractHelper;

class WPFetch extends AbstractHelper{
  public function __construct(
        \Magento\Framework\App\Helper\Context $context)
  {
      parent::__construct($context);
      $this->consumerKey = $this->scopeConfig->getValue('hpu_wci/general/consumer_key', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
      $this->consumerSecret =  $this->scopeConfig->getValue('hpu_wci/general/consumer_secret', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
      $this->oauth = new \OAuth($this->consumerKey, $this->consumerSecret);
      $this->curl = curl_init();
  }

  function fetchData($reqUrl, $params = []){
      $baseUrl = "https://triconville.com.my";
      $url = $baseUrl . "/wp-json/wc/v3/" . $reqUrl;
      $url .= '?' . http_build_query($params);
      curl_setopt($this->curl, CURLOPT_URL, $url);
      curl_setopt($this->curl, CURLOPT_HTTPGET, true);
      curl_setopt($this->curl, CURLOPT_HTTPHEADER, array(
        'Authorization: ' . $this->oauth->getRequestHeader('GET', $url)
      ));
      curl_setopt($this->curl, CURLOPT_VERBOSE, 0);
      curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, 1);
      return curl_exec($this->curl);
  }
}
