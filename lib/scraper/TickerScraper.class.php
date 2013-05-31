<?php

class TickerScraper extends Scraper
{
  

  protected $dir = '/tmp/';
  protected $filename = null;
  protected $offset = 0;
  protected $limit = 20000;
  protected $min_market_cap = 2000000000;
  protected $exchange = 'nasdaq';
  protected $too_small_ct = 0;
  protected $added_ct = 0;

  
  public function setExchange($exchange)
  {
    $this->exchange = $exchange;
  }
  
  public function setMinMarketCap($min_market_cap)
  {
    $this->min_market_cap = $min_market_cap;
  }
  
  public function execute()
  {
    $filename = $this->exchange . ".csv";
    $handle = fopen($this->dir . $filename, "r");
    if ($handle) 
    {
      while ($row = fgets($handle)) 
      {
        $this->processRow($row);
      }
      $this->printDebug($this->added_ct . " corps added");
      $this->printDebug($this->existing_ct . " corps existing");
      $this->printDebug($this->too_small_ct . " corps too small");
    }
    else
    {
      $this->printDebug("No such file! Download from http://www.nasdaq.com/screening/company-list.aspx and save to /tmp as nasdaq.csv or amex.csv or nyse.csv");
    }
  
  }
  
  public function processRow($row)
  {
    $arr = str_getcsv($row);
    $ticker = $arr[0];
    $name = $arr[1];
    $name = str_replace('&#39;',"'",$name);
    if ($name == 'Name') return false;
    $cap = $arr[3];
    if ($cap < $this->min_market_cap)
    {
      $this->too_small_ct ++;
      return false;
    }
    else
    {
      $corp = Doctrine::getTable('PublicCompany')->findOneByTicker($ticker);
      if ($corp)
      {
        $this->existing_ct ++;
        return false;
      }
      else
      {
        $corp = new Entity;
        $corp->addExtension('Org');
        $corp->addExtension('Business');
        $corp->addExtension('PublicCompany');
        $corp->ticker = $ticker;
        $corp->name = $name;
        $corp->save();
        $this->printDebug("New company added: " . $name);
        $this->added_ct ++;

      }
    }
  }
  
  
}