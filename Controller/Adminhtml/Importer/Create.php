<?php
namespace Hprasetyou\WCImporter\Controller\Adminhtml\Importer;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Catalog\Api\Data\ProductInterfaceFactory;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem\Io\File;
use Hprasetyou\WCImporter\Helper\WPFetch;
use Hprasetyou\WCImporter\Helper\Variation;

class Create extends Action
{

    private $resultJsonFactory;

    public function __construct(
      JsonFactory $resultJsonFactory,
      ProductInterfaceFactory $productFactory,
      ProductRepositoryInterface $productRepository,
      StockRegistryInterface $stockRegistry,
      DirectoryList $directoryList,
      File $file,
      WPFetch $wPFetch,
      Variation $variationHelper,
      Context $context)
    {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->productFactory = $productFactory;
        $this->productRepository = $productRepository;
        $this->stockRegistry = $stockRegistry;
        $this->directoryList = $directoryList;
        $this->file = $file;
        $this->wPFetch = $wPFetch;
        $this->variationHelper = $variationHelper;

    }

    function createVariations($product){
      $items = json_decode($this->wPFetch->fetchData('products/' . $product['id'] . '/variations'), true);
      $ids = [];
      foreach ($items as $key => $item) {
        $item['name'] = $product['name'];
        foreach ($item['attributes'] as $attr) {
          $item['name'] .= ' ' . $attr['option'];
          $this->variationHelper->getOrNew($attr['name'], $attr['option']);
          print_r($attr);
        }
        if(!isset($item['image'][0])){
          $item['images'] = [$item['image']];
        }
        $product = $this->createProduct($item);
        $ids[] = $product->getId();
      }
      return $ids;
    }

    protected function getMediaDirTmpDir()
    {
        return $this->directoryList->getPath(DirectoryList::MEDIA) . DIRECTORY_SEPARATOR . 'tmp';
    }

    public function createProduct($data){
        $product = $this->productFactory->create();
        $product->setSku($data['sku']);
        $product->setName($data['name']);

        $product->setVisibility(4);
        $product->setPrice($data['price']);
        $product->setAttributeSetId(4); // Default attribute set for products
        $product->setStatus(\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED);
        $product->setPriceView(1);

        $tmpDir = $this->getMediaDirTmpDir();
        $this->file->checkAndCreateFolder($tmpDir);
        foreach ($data['images'] as $key => $image) {
          $imageUrl = $image['src'];
          $newFileName = $tmpDir . baseName($imageUrl);
          $result = $this->file->read($imageUrl, $newFileName);
          $attr = [];
          if($key == 0){
            $attr =  ['thumbnail'];
          }
          if ($result) {
              $product->addImageToMediaGallery($newFileName, $attr, true, true);
          }
        }

        if(isset($data['variations'])){
          $product->setTypeId('configurable');
          $variations = $this->createVariations($data);
          // $extensionConfigurableAttributes = $product->getExtensionAttributes();
          // $extensionConfigurableAttributes->setConfigurableProductLinks($variations);
          // $product->setExtensionAttributes($extensionConfigurableAttributes);

        }else{
          $product->setTypeId('simple');
        }

        $product = $this->productRepository->save($product);
        return $product;
    }

    public function execute()
    {

        $data = $this->getRequest()->getParams();
        $product = $this->createProduct($data);
        $resultJson = $this->resultJsonFactory->create();
        return $resultJson->setData(['data' => $product->getId()]);
    }
}
