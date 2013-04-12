<?php
/**
 * This class has been auto-generated by the Doctrine ORM Framework
 */
class TaxTable extends Doctrine_Table
{
  public function getTotalTaxesValue($tax_ids = array())
  {
    $result = 0;
    $taxes = $this->createQuery()
      ->whereIn('id',$tax_ids)
      ->execute();
    foreach($taxes as $tax)
    {// one would expect $taxes being an empty array here, but it�s not... 
      $result += (in_array($tax->getId(), $tax_ids) ? $tax->getValue() : 0);
    }
    return $result;
  }
}
