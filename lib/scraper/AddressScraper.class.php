<?php

class AddressScraper extends Scraper
{
  protected $_addresses;
  protected $_limit = 50;
  
  public function setAddresses($addresses = null)
  {
    if ($addresses)
    {
      $this->_addresses = $addresses;
    }
    else
    {
      $q = LsQuery::getByModelAndFieldsQuery('Address',array('latitude' => null))->limit($this->_limit);
      if ($this->hasMeta('standardization','last_processed'))
      {
        $start_id = $this->getMeta('standardization','last_processed'); 
        $q->addWhere('id > ?', $start_id);
      }
      $this->printDebug($q->getSqlQuery());
      $this->_addresses = $q->execute();      
    }
      
  }
  
  public function setLimit($limit)
  {
    $this->_limit = $limit;
  }

  public function execute()
  {
   
    if (!$this->safeToRun('address'))
    {
      $this->printDebug('script already running');
      die;
    }
    if (!$this->_addresses)
    {
      $this->setAddresses();
    }
    if (!count($this->_addresses))
    {
      echo 'no more addresses';
      die;  
    }
    foreach ($this->_addresses as $address)
    {
      try
      {
        $this->db->beginTransaction();
        $this->printDebug('standardizing ' . $address->getOneLiner() . ' entity: ' . $address->entity_id);
        $address = AddressTable::standardize($address);
        if ($address)
        {
          $address->save();
          $this->printDebug('address saved');
          $this->printDebug('saved as ' . $address->getOneLiner());
        }
        $this->saveMeta('standardization','last_processed',$address->id);
        $this->db->commit();
      }
      catch (Exception $e)
      {
        $this->db->rollback();
        throw $e;
      }
    }  
  }


}