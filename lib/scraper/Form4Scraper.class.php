<?php

class Form4Scraper extends Scraper
{
  private $url = null;
  private $xml = null;
  private $results = null;

  public function setUrl($url)
  {
    $this->url = $url;
  }

  public function execute()
  {
    if(!$this->url)
    {
      return null;
    }
    try
  	{
  	  if (!$this->browser->get($this->url)->responseIsError())
  		{
  		  $this->xml = $this->browser->getResponseXml();
  		  $this->results = $this->parseData();
  		      
  		}
  		else
  		{
  			//Error response (eg. 404, 500, etc)
    	  $log = fopen($this->logFile, 'a');
  			fwrite($log, "Couldn't get " . $url . "\n");
  			fclose($log);
  		}
  	}
  	catch (Exception $e)
  	{
  		//Adapter error (eg. Host not found)
   		echo $e->getMessage(); die; 
  	}

  }
  
  public function getResults()
  {
    return $this->results;
  }

  private function parseData()
  {
    $results = array();
    
		//person & org
		$results['corpCik'] = trim($this->xml->issuer->issuerCik);
		$results['personCik'] = trim($this->xml->reportingOwner->reportingOwnerId->rptOwnerCik);
		$results['personName'] = trim($this->xml->reportingOwner->reportingOwnerId->rptOwnerName);
		
		//sometimes /CT/ (or /NY, /VA, etc) appears at the end of the person's name
		$results['personName'] = preg_replace('/\/\p{L}+\/?$/','',$results['personName']);
		//$results['corpName'] = 
		//$results['corpCik'] = 
		//$results['corpSymbol'] = 
		
		//date
		$results['date'] = trim($this->xml->ownerSignature->signatureDate);
		
	  //address
		$address = array();
		$address_raw = (array) $this->xml->reportingOwner->reportingOwnerAddress;
		$address['street1'] = trim($address_raw['rptOwnerStreet1']);
		$address['street2'] = trim($address_raw['rptOwnerStreet2']);
		$address['city'] = trim($address_raw['rptOwnerCity']);
		$address['state'] = trim($address_raw['rptOwnerState']);
		$address['postal'] = trim($address_raw['rptOwnerZipCode']);
		$results['address'] = $address;

		//position
		$results['isDirector'] = trim($this->xml->reportingOwner->reportingOwnerRelationship->isDirector);
		if (strtolower($results['isDirector']) == 'true')
		{
		  $results['isDirector'] = '1';
		}
		else if (strtolower($results['isDirector']) == 'false')
		{
		  $results['isDirector'] = '0';
		}
		$results['officerTitle'] = trim($this->xml->reportingOwner->reportingOwnerRelationship->officerTitle);
		$results['otherText'] = trim($this->xml->reportingOwner->reportingOwnerRelationship->otherText);
    
    //financial info - just a placeholder
    //$results['shares_post'] = trim($this->xml->nonDerivativeTable->nonDerivativeTransaction->postTransactionAmounts->sharesOwnedFollowingTransaction->value);
    
    return $results;
  }

}