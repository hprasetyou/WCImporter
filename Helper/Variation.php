<?php
namespace Hprasetyou\WCImporter\Helper;

use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Eav\Model\Config;
use Magento\Eav\Model\Entity\AttributeFactory;
class Variation extends AbstractHelper{

   private $eavSetupFactory;

   public function __construct(
     EavSetupFactory $eavSetupFactory,
     ModuleDataSetupInterface $setup,
     AttributeFactory $attributeFactory,
     Config $eavConfig)
   {
      $this->eavSetupFactory = $eavSetupFactory;
      $this->eavConfig = $eavConfig;
      $this->setup = $setup;
      $this->attributeFactory = $attributeFactory;
   }

   function getAttr($attributeCode){
     $attribute = $this->eavConfig->getAttribute('catalog_product', $attributeCode);

     if (!$attribute || !$attribute->getAttributeId()) {
         return false;
     }
     return $attribute;
   }
   public function getOrNew($label, $option)
   {
     $attrCode = str_replace(' ', '_', strtolower($label));
     // $attr = $this->getAttr($attrCode);
     // if(!$attr){
       $eavSetup = $this->eavSetupFactory->create(['setup' => $this->setup]);
       $eavSetup->addAttribute(
         \Magento\Catalog\Model\Product::ENTITY,
         $attrCode,
         [
           'type' => 'int',
           'backend' => '',
           'frontend' => '',
           'label' => $label,
           'input' => 'select',
           'class' => '',
           'source' => '',
           'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
           'visible' => true,
           'required' => false,
           'user_defined' => false,
           'default' => '',
           'searchable' => false,
           'filterable' => false,
           'comparable' => false,
           'visible_on_front' => false,
           'used_in_product_listing' => true,
           'unique' => false,
           'apply_to' => ''
         ]
       );
       $attr = $this->getAttr($attrCode);
       $eavFactory = $this->attributeFactory->create()->load($attrCode,"attribute_code");
       $newOpt = [];
       $newOpt['attribute_id'] = $eavFactory->getAttributeId();
       $newOpt['value'][$option][] =  $option;
       $eavSetup = $this->eavSetupFactory->create();
       $eavSetup->addAttributeOption($newOpt);
     // }
     return $attr;
   }

}
