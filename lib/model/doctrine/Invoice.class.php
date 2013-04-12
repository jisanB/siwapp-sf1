<?php

/**
 * This class has been auto-generated by the Doctrine ORM Framework
 */
class Invoice extends BaseInvoice
{
  const DRAFT   = 0;
  const CLOSED  = 1;
  const OPENED  = 2;
  const OVERDUE = 3;
  private $series_changed = false;

  public function setUp()
  {
    parent::setUp();
    
    $this->_table->removeColumn('days_to_due');
    $this->_table->removeColumn('enabled');
    $this->_table->removeColumn('max_occurrences');
    $this->_table->removeColumn('period');
    $this->_table->removeColumn('period_type');
    $this->_table->removeColumn('starting_date');
    $this->_table->removeColumn('finishing_date');
  }

  public function __toString()
  {
    return $this->getSeries()->getValue().($this->draft ? '[draft]' : $this->getNumber());
  }
  
  public function getDueAmount()
  {
    if ($this->getStatus() == Invoice::DRAFT)
      return null;
      
    return $this->getGrossAmount() - $this->getPaidAmount();
  }

  public function __get($name)
  {
    if($name == 'due_amount')
    {
      $m = sfInflector::camelize("get_{$name}");
      return $this->$m();
    }
    if(strpos($name,'tax_amount_') === 0)
    {
      return $this->calculate($name, true);
    }
    return parent::__get($name);
  }

  /**
   * When setting series id, we check if there has been a series change,
   * because the invoice number will have to change later
   *
   * @author JoeZ99 <jzarate@gmail.com>
   *
   **/
  public function setSeriesId($value)
  {
      // we check for is_numeric to prevent loading series by name in the tests
    if($this->getNumber() && $value != $this->series_id && 
       is_numeric($this->series_id) && is_numeric($value))
    {
      $this->series_changed = true;
    }
    parent::_set('series_id',$value);
  }

  public function __isset($name)
  {
    if($name == 'due_amount')
    {
      return true;
    }
    if(strpos($name, 'tax_amount_') === 0)
    {
      return true;
    }
    return parent::__isset($name);
  }
  
  public function preSave($event)
  {  
    // compute the number of invoice
    if ( (!$this->getNumber() && !$this->getDraft()) ||
         ($this->series_changed && !$this->getDraft())
         )
    {
      $this->series_changed = false;
      $this->setNumber($this->_table->getNextNumber($this->getSeriesId()));
    }
    
    parent::preSave($event);
  }
  
  /**
   * checks and sets the status
   *
   * @return Invoice  $this
   **/
  public function checkStatus()
  {
    if($this->getDraft())
    {
      $this->setStatus(Invoice::DRAFT);
    }
    else
    {
      if($this->getClosed() || $this->getDueAmount() == 0)
      {
        $this->setStatus(Invoice::CLOSED);
      }
      else
      {
        if($this->getDueDate() > sfDate::getInstance()->format('Y-m-d'))
        {
          $this->setStatus(Invoice::OPENED);
        }
        else
        {
          $this->setStatus(Invoice::OVERDUE);
        }
      }
    }
    
    return $this;
  }
  
  public function getStatusString()
  {
    switch($this->getStatus())
    {
      case Invoice::DRAFT:
        $status = 'draft';
        break;
      case Invoice::CLOSED:
        $status = 'closed';
        break;
      case Invoice::OPENED:
        $status = 'opened';
        break;
      case Invoice::OVERDUE:
        $status = 'overdue';
        break;
      default:
        $status = 'unknown';
        break;
    }
    
    return $status;
  }

  public function setAmounts()
  {
    parent::setAmounts();
    $this->setPaidAmount($this->calculate('paid_amount'));
    
    return $this;
  }

  
}