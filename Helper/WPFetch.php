<?php
namespace Hprasetyou\WCImporter\Helper;
use \Magento\Framework\App\Helper\AbstractHelper;

class WPFetch extends AbstractHelper{
  public function __construct(
        \Magento\Framework\App\Helper\Context $context)
  {
      parent::__construct($context);
      $this->consumerKey = 'ck_72fd48880aa159ce3e7d7418dfb1c8fa3262acac';
      $this->consumerSecret = 'cs_67a31154c926192e56a24f94d03f543617c5bc9c';

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
