<?php
// app/code/local/Envato/Customapimodule/Model/Product/Api.php
class Customs_Api_Model_Product_Api extends Mage_Api_Model_Resource_Abstract
{
  public function items()
  {
    $arr_products=array();
    $products=Mage::getModel("catalog/product")
      ->getCollection()
      ->addAttributeToSelect('*')
      ->setOrder('entity_id', 'DESC')
      ->setPageSize(5);
 
    foreach ($products as $product) {
      $arr_products[] = $product->toArray(array('entity_id', 'name'));
    }
 
    return $arr_products;
  }
}