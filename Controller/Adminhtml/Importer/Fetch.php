<?php
namespace Hprasetyou\WCImporter\Controller\Adminhtml\Importer;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Hprasetyou\WCImporter\Helper\WPFetch;

class Fetch extends Action
{

    private $resultJsonFactory;

    public function __construct(JsonFactory $resultJsonFactory, WPFetch $wPFetch, Context $context)
    {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->wPFetch = $wPFetch;
    }

    public function execute()
    {
        $resultJson = $this->resultJsonFactory->create();
        $data = $this->getProducts();
        return $resultJson->setData(['data' => $data]);
    }

    function getProducts(){
      $data = [];
      $i = 0;
      $a = 1;
      while ($a > 0) {
        $i++;
        $params = array('per_page' => 100,'page'=>$i);
        $items = json_decode($this->wPFetch->fetchData('products', $params));
        $data = array_merge($data, $items);
        $a = count($items);
      }
      return $data;
    }


}
