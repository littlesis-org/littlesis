<?php

class ForbesPrivateCompaniesScraper extends ForbesScraper
{
  protected $list_urls = array(
    '1996'=> array('source_url' => 'http://www.forbes.com/lists/results.jhtml?passListId=21&passYear=1996&passListType=Company&searchParameter1=unset&searchParameter2=unset&resultsStart=1&resultsHowMany=25&resultsSortProperties=%2Bnumberfield1%2C%2Bstringfield1&resultsSortCategoryName=rank&passKeyword=&category1=category&category2=category', 
                   'reg_ex_match' => '/td class="mainlisttxt"><a href="\/finance\/lists\/21\/([^"]+)" /i',
                   'list_base_url' => 'http://www.forbes.com/lists/results.jhtml?passListId=21&passYear=1996&passListType=Company&resultsHowMany=25&resultsSortProperties=%2Bnumberfield1%2C%2Bstringfield1&resultsSortCategoryName=rank&resultsStart=',
                   'profile_url' =>  'http://www.forbes.com/finance/lists/21/',
                   'img_src' => '',
                   'append' => '',
                   'count_by' => 25,                                             
                   'enabled' => true,                                             
                   'counting_exception' => false,                                             
                  ),
    '1997'=> array('source_url' => 'http://www.forbes.com/lists/results.jhtml?passListId=21&passYear=1997&passListType=Company&searchParameter1=unset&searchParameter2=unset&resultsStart=1&resultsHowMany=25&resultsSortProperties=%2Bnumberfield1%2C%2Bstringfield1&resultsSortCategoryName=rank&passKeyword=&category1=category&category2=category', 
                   'reg_ex_match' => '/td class="mainlisttxt"><a href="\/finance\/lists\/21\/([^"]+)" /i',
                   'list_base_url' => 'http://www.forbes.com/lists/results.jhtml?passListId=21&passYear=1997&passListType=Company&resultsHowMany=25&resultsSortProperties=%2Bnumberfield1%2C%2Bstringfield1&resultsSortCategoryName=rank&resultsStart=',
                   'profile_url' =>  'http://www.forbes.com/finance/lists/21/',
                   'img_src' => '',
                   'append' => '',
                   'count_by' => 25,                                             
                   'enabled' => true,                                             
                   'counting_exception' => false,                                         
                  ),
    '1998'=> array('source_url' => 'http://www.forbes.com/lists/results.jhtml?passListId=21&passYear=1998&passListType=Company&searchParameter1=unset&searchParameter2=unset&resultsStart=1&resultsHowMany=25&resultsSortProperties=%2Bnumberfield1%2C%2Bstringfield1&resultsSortCategoryName=rank&passKeyword=&category1=category&category2=category', 
                   'reg_ex_match' => '/td class="mainlisttxt"><a href="\/finance\/lists\/21\/([^"]+)" /i',
                   'list_base_url' => 'http://www.forbes.com/lists/results.jhtml?passListId=21&passYear=1998&passListType=Company&resultsHowMany=25&resultsSortProperties=%2Bnumberfield1%2C%2Bstringfield1&resultsSortCategoryName=rank&resultsStart=',
                   'profile_url' =>  'http://www.forbes.com/finance/lists/21/',
                   'img_src' => '',
                   'append' => '',
                   'count_by' => 25,                                             
                   'enabled' => true,                                             
                   'counting_exception' => false,                                            
                  ),
    '1999'=> array('source_url' => 'http://www.forbes.com/lists/results.jhtml?passListId=21&passYear=1999&passListType=Company&searchParameter1=unset&searchParameter2=unset&resultsStart=1&resultsHowMany=25&resultsSortProperties=%2Bnumberfield1%2C%2Bstringfield1&resultsSortCategoryName=rank&passKeyword=&category1=category&category2=category', 
                   'reg_ex_match' => '/td class="mainlisttxt"><a href="\/finance\/lists\/21\/([^"]+)" /i',
                   'list_base_url' => 'http://www.forbes.com/lists/results.jhtml?passListId=21&passYear=1999&passListType=Company&resultsHowMany=25&resultsSortProperties=%2Bnumberfield1%2C%2Bstringfield1&resultsSortCategoryName=rank&resultsStart=',
                   'profile_url' =>  'http://www.forbes.com/finance/lists/21/',
                   'img_src' => '',
                   'append' => '',
                   'count_by' => 25,                                             
                   'enabled' => true,                                             
                   'counting_exception' => false,                                            
                  ),                                      
    '2000'=> array('source_url' => 'http://www.forbes.com/lists/results.jhtml?passListId=21&passYear=2000&passListType=Company&searchParameter1=unset&searchParameter2=unset&resultsStart=1&resultsHowMany=25&resultsSortProperties=%2Bnumberfield1%2C%2Bstringfield1&resultsSortCategoryName=rank&passKeyword=&category1=category&category2=category', 
                   'reg_ex_match' => '/td class="mainlisttxt"><a href="\/finance\/lists\/21\/([^"]+)" /i',
                   'list_base_url' => 'http://www.forbes.com/lists/results.jhtml?passListId=21&passYear=2000&passListType=Company&resultsHowMany=25&resultsSortProperties=%2Bnumberfield1%2C%2Bstringfield1&resultsSortCategoryName=rank&resultsStart=',
                   'profile_url' =>  'http://www.forbes.com/finance/lists/21/',
                   'img_src' => '',
                   'append' => '',
                   'count_by' => 25,                                             
                   'enabled' => true,                                             
                   'counting_exception' => false,                                           
                  ),                                           
    '2001'=> array('source_url' => 'http://www.forbes.com/lists/results.jhtml?passListId=21&passYear=2001&passListType=Company&searchParameter1=unset&searchParameter2=unset&resultsStart=1&resultsHowMany=25&resultsSortProperties=%2Bnumberfield1%2C%2Bstringfield1&resultsSortCategoryName=rank&passKeyword=&category1=category&category2=category', 
                   'reg_ex_match' => '/td class="mainlisttxt"><a href="\/finance\/lists\/21\/([^"]+)" /i',
                   'list_base_url' => 'http://www.forbes.com/lists/results.jhtml?passListId=21&passYear=2001&passListType=Company&resultsHowMany=25&resultsSortProperties=%2Bnumberfield1%2C%2Bstringfield1&resultsSortCategoryName=rank&resultsStart=',
                   'profile_url' =>  'http://www.forbes.com/finance/lists/21/',
                   'img_src' => '',
                   'append' => '',
                   'count_by' => 25,                                             
                   'enabled' => true,                                             
                   'counting_exception' => false,                                           
                  ),                                           
    '2002'=> array('source_url' => 'http://www.forbes.com/lists/results.jhtml?passListId=21&passYear=2002&passListType=Company&searchParameter1=unset&searchParameter2=unset&resultsStart=1&resultsHowMany=25&resultsSortProperties=%2Bnumberfield1%2C%2Bstringfield1&resultsSortCategoryName=rank&passKeyword=&category1=category&category2=category', 
                   'reg_ex_match' => '/td class="mainlisttxt"><a href="\/finance\/lists\/21\/([^"]+)" /i',
                   'list_base_url' => 'http://www.forbes.com/lists/results.jhtml?passListId=21&passYear=2002&passListType=Company&resultsHowMany=25&resultsSortProperties=%2Bnumberfield1%2C%2Bstringfield1&resultsSortCategoryName=rank&resultsStart=',
                   'profile_url' =>  'http://www.forbes.com/finance/lists/21/',
                   'img_src' => '',
                   'append' => '',
                   'count_by' => 25,                                             
                   'enabled' => true,
                   'counting_exception' => false,                                            
                  ),                                           
    '2003'=> array('source_url' => 'http://www.forbes.com/lists/results.jhtml?passListId=21&passYear=2003&passListType=Company&searchParameter1=unset&searchParameter2=unset&resultsStart=1&resultsHowMany=25&resultsSortProperties=%2Bnumberfield1%2C%2Bstringfield8&resultsSortCategoryName=rank&passKeyword=&category1=category&category2=category', 
                   'reg_ex_match' => '/td class="mainlisttxt"><a href="\/finance\/lists\/21\/([^"]+)" /i',
                   'list_base_url' => 'http://www.forbes.com/lists/results.jhtml?passListId=21&passYear=2003&passListType=Company&resultsHowMany=25&resultsSortProperties=%2Bnumberfield1%2C%2Bstringfield1&resultsSortCategoryName=rank&resultsStart=',
                   'profile_url' =>  'http://www.forbes.com/finance/lists/21/',
                   'img_src' => '',
                   'append' => '',
                   'count_by' => 25,                                             
                   'enabled' => true,                                             
                   'counting_exception' => false,                                             
                  ),                                           
    '2004'=> array('source_url' => 'http://www.forbes.com/lists/results.jhtml?passListId=21&passYear=2004&passListType=Company&searchParameter1=unset&searchParameter2=unset&resultsStart=1&resultsHowMany=25&resultsSortProperties=%2Bnumberfield1%2C%2Bstringfield8&resultsSortCategoryName=rank&passKeyword=&category1=category&category2=category', 
                   'reg_ex_match' => '/td class="mainlisttxt"><a href="\/finance\/lists\/21\/([^"]+)"/i',
                   'list_base_url' => 'http://www.forbes.com/lists/results.jhtml?passListId=21&passYear=2004&passListType=Company&resultsHowMany=25&resultsSortProperties=%2Bnumberfield1%2C%2Bstringfield1&resultsSortCategoryName=rank&resultsStart=',
                   'profile_url' =>  'http://www.forbes.com/finance/lists/21/',
                   'img_src' => '',
                   'append' => '',
                   'count_by' => 25,                                             
                   'enabled' => true,                                             
                   'counting_exception' => false,                                             
                  ),
    '2005'=> array('source_url' => 'http://www.forbes.com/2005/11/09/largest-private-companies_05private_land.html', 
                   'reg_ex_match' => '/nowrap="nowrap" class="rowcolor"> <a href="([^"]+)">/i',
                   'list_base_url' =>  'http://www.forbes.com/2005/11/09/largest-private-companies_05private_land.html',
                   'profile_url' =>  'http://www.forbes.com/lists/2007/21/',
                   'img_src' => '',
                   'append' => '.html',
                   'enabled' => false,                                             
                   'counting_exception' => 'underscore',                                             
                  ),
    '2006'=> array('source_url' => 'http://www.forbes.com/lists/2006/21/biz_06privates_The-Largest-Private-Companies_land.html', 
                   'reg_ex_match' => '/nowrap="nowrap" class="rowcolor"> <a href="([^"]+)">/i',
                   'list_base_url' =>  'http://www.forbes.com/lists/2006/21/biz_06privates_The-Largest-Private-Companies_Rank',
                   'profile_url' =>  'http://www.forbes.com/lists/2006/21/',
                   'img_src' => '',
                   'append' => '.html',
                   'count_by' => 1,                                             
                   'enabled' => true,                                             
                   'counting_exception' => 'underscore',                                             
                  ),                                           
    '2007'=> array('source_url' => 'http://www.forbes.com/2007/11/08/largest-private-companies-biz-privates07-cx_sr_1108private_land.html', 
                   'reg_ex_match' => '/nowrap="nowrap"> <a href="([^"]+)">/i',
                    'list_base_url' =>  'http://www.forbes.com/lists/2007/21/biz_privates07_Americas-Largest-Private-Companies_Rank',
                   'profile_url' =>  'http://www.forbes.com/lists/2007/21/',
                   'img_src' => '',
                   'append' => '.html',
                   'count_by' => 1,                                             
                   'enabled' => true,                                             
                   'counting_exception' => 'underscore',                                             
                  )
    );
  

  
  

  
  protected function setListOptions(){
    $this->list_name = "Forbes Largest Private Companies";
    $this->list_description = "Fortune Magazine's list of large US private companies";
    $this->list_fields="name, description, is_ranked";  
  }

  
	protected function import($url)
	{

    $company = null;

		if (!$this->browser->get($url)->responseIsError())
		{
			$text = $this->browser->getResponseText();
			
			$rank = null;
			$name = null;
			$industryName = null;
			$street1 = null;
			$street2 = null;
			$city = null;
			$state = null;
			$postal = null;
			$phone = null;
			$fax = null;
			$website = null;
			$blurb = null;
			$summary = null;
			$revenue = null;
			$employees = null;
			$ceoName = null;
			$ceoBirthYear = null;

      //get rank
      if ($this->year > 1999 && $this->year < 2005 && preg_match('/ForbesListRank" content="(\d+)"/i', $text, $match))
      {
        $rank = $match[1];
      }
      elseif ($this->year < 2000 && preg_match('/td class="highlightcolor1">(\d+)/i', $text, $match))
      {
        $rank = $match[1];
      }
      elseif ($this->year > 2004 &&  preg_match('/<b>#(\d+) ([^<]+)<\/b>/i', $text, $match))
      {
        $rank = html_entity_decode($match[1]);
      }
    
      //get name      
      if ($this->year > 1995 && $this->year < 2005 && preg_match('/span class="mainlisttitle">([^<]+)<\/span>/i', $text, $match))
      {
        $name = html_entity_decode ($match[1]);
      }      
      elseif ($this->year > 2004 && preg_match('/<b>#(\d+) ([^<]+)<\/b>/i', $text, $match))
      {
        $name = html_entity_decode($match[2]);
      }
      else
      {
        $this->printDebug("Company name not found");
        return;				
      }

      //get industry
      if ($this->year > 1995 && $this->year < 2001 && preg_match('/<b>See more private companies in <a [^>]+>([^<]+)<\/a><\/b>/ism', $text, $match))
      {
        $industryName = trim(html_entity_decode( $match[1] ));
      }
      elseif ($this->year > 2000 && $this->year < 2005 && preg_match('/private companies\<\/a> in ([^\.]+)/ism', $text, $match))
      {
        $industryName = trim(html_entity_decode( $match[1] ));
      }
      elseif ($this->year > 2004 && preg_match('/<b>Industry:<\/b> <a href="[^"]+">([^<]+)<\/a>/ism', $text, $match))
      {
        $industryName = trim(html_entity_decode($match[1]));
      }    
      
      //get address
      if ($this->year > 1995 && $this->year < 2000 && preg_match('/<td class="mainlisttxt"\>(.+)phone/smU', $text, $match))
      {
        $contactLines = explode('<br>', trim($match[1]));
        array_pop($contactLines);

        $street1 =  $contactLines[0];
        $street2 =  (count($contactLines) == 3) ? $contactLines[2] : null;
        $city_state_zip = (count($contactLines) == 3) ? LsLanguage::parseCityStatePostal($contactLines[2]) : LsLanguage::parseCityStatePostal($contactLines[1]);

        $city = $city_state_zip['city'];
        $state = $city_state_zip['state'];
        $postal = $city_state_zip['zip'];

      }
      elseif ($this->year > 1999 && $this->year < 2005 && preg_match('/(view private companies under this industry|in the same industry).+<br><br>(.+)phone/is', $text, $match))
      {
        var_dump($match);
        $contactLines = explode('<br>', trim($match[1]));
        array_pop($contactLines);

        $street1 =  $contactLines[0];
        $street2 =  (count($contactLines) == 3) ? $contactLines[2] : null;
        $city_state_zip = (count($contactLines) == 3) ? LsLanguage::parseCityStatePostal($contactLines[2]) : LsLanguage::parseCityStatePostal($contactLines[1]);

        $city = $city_state_zip['city'];
        $state = $city_state_zip['state'];
        $postal = $city_state_zip['zip'];

      }
      elseif ($this->year > 2004 && preg_match('/<div class="spaced">(.+)<\/div>/ismU', $text, $match))
      {
        $contactLines = explode('<br>', $match[1]);
        if (!preg_match('/Phone\:|Fax\:/i', $contactLines[0]) && !preg_match('/Phone\:|Fax\:/i', $contactLines[1]))
        {
          $street1 = trim($contactLines[0]);

          if (count($contactLines) == 4)
          {
            if (preg_match('/^(.+?) ([A-Z]{2}) (\d{5})($|-)/sU', trim($contactLines[1]), $match))
            {
              $city = $match[1];
              $state = $match[2];
              $postal = $match[3];
            }
          }
          elseif (count($contactLines) == 5)
          {
            $street2 = $contactLines[1];

            if (preg_match('/^(.+?) ([A-Z]{2}) (\d{5})($|-)/sU', trim($contactLines[2]), $match))
            {
              $city = $match[1];
              $state = $match[2];
              $postal = $match[3];
            }
          }
        }
      }

      //get phone
      if ($this->year > 1995 && $this->year < 2005 && preg_match('/phone ([\d\-]{12})/is', $text, $match))
      {
        $phone = trim(str_replace('-', '', $match[1]));
      }
      elseif ($this->year > 2004 && preg_match('/Phone: ([\d\-]{12})/is', $text, $match))
      {
        $phone = trim(str_replace('-', '', $match[1]));
      }

      //get fax
      if ($this->year > 1995 && $this->year < 2005 && preg_match('/fax ([\d\-]{12})/is', $text, $match))
      {
        $fax = trim(str_replace('-', '', $match[1]));
      }
      else if ($this->year > 2004 && preg_match('/Fax: ([\d\-]{12})/is', $text, $match))
      {
        $fax = trim(str_replace('-', '', $match[1]));
      }

      
      
      //get website
      if ($this->year > 1995 && $this->year < 2005 && preg_match('/this company\'s web site[^>]+\>(http[^\<]+)/is', $text, $match))
      {
        $website = $match[1];
      }      
      elseif ($this->year > 2004 && preg_match('/<div class="spaced">.*<\/div>\s+<br>\s+<a href="(http:\/\/[^"]+)">/ismU', $text, $match))
      {
        $website = $match[1];
      }
      
      
      //get ceo
      if ($this->year > 1995 && $this->year < 2005 && preg_match('/b>CEO: ([^<]+)<\/b>/ism', $text, $match))
      {
        $ceoName = $match[1];
      }
      elseif ($this->year > 2004 && preg_match('/CEO: ([^<]+)<\/b> , (\d+) <br>/ism', $text, $match))
      {
        $ceoName = html_entity_decode($match[1]);
        $ceoBirthYear = date("Y"); - $match[2];
      }

      //get summary
      if ($this->year > 1995 && $this->year < 2000 && preg_match_all('/p class="mainlisttxt">(.*)<\/p>/ismU', $text, $match))
      {
        $summary = str_replace(array('  ',"\n"), array(' ',' '), html_entity_decode(trim(strip_tags($match[1][1]))));        
      }
      elseif ($this->year > 1999 && $this->year < 2005 && preg_match('/p class="mainlisttxt">(.*)<\/p>/ismU', $text, $match))
      {
        $summary = str_replace(array('  ',"\n"), array(' ',' '), html_entity_decode(trim(strip_tags($match[1]))));
      }
      elseif ($this->year > 2004 && preg_match('/<blockquote class="spaced">(.*)<\/blockquote>/ismU', $text, $match))
      {
        $summary = str_replace(array('  ',"\n"), array(' ',' '), html_entity_decode(trim(strip_tags($match[1]))));
      }
      
      //get revenue
      if ($this->year > 1995 && $this->year < 2000 && preg_match('/<td class="mainlisttxt">\$([\S]+) mil<sup>e?<\/sup><\/td>/ismU', $text, $match))
      {
        $this->printDebug($match[1]);
        $revenue = str_replace(",", "", $match[1] . ",000,000");
      }
      elseif ($this->year > 1999 && $this->year < 2005 && preg_match('/<td class="mainlisttxt" nowrap>([^<]+)<sup>e?<\/sup><\/td>/ismU', $text, $match))
      {
        $this->printDebug($match[1]);
        $revenue = str_replace(",", "", $match[1] . ",000,000");
      }
      elseif ($this->year > 2004 && preg_match('/<td class="highlight" nowrap="nowrap">\$([\S]+) bil.*<\/td> <td class="highlight" nowrap="nowrap">[^<]+<\/td> <td class="highlight" nowrap="nowrap">([^<]+)<\/td>/ismU', $text, $match))
      {
        $revenue = 1000000000 * $match[1];
      }
        
      //get employees
      if ($this->year > 1995 && $this->year < 2005 && preg_match('/mil<\/td>.+<td class="mainlisttxt"( nowrap)?>(\d[^<]+)<\/td>.+<td class="mainlisttxt">[a-zA-Z]+<\/td>/ismU', $text, $match))
      {
        $employees = str_replace(',', '', $match[2]);
      }
      elseif ($this->year > 1999 && $this->year < 2005 && preg_match('/<sup>e?<\/sup><\/td> <td class="mainlisttxt"( nowrap)?>(\d[^<]+)<sup>e?<\/sup><\/td> <td class="mainlisttxt">[a-zA-Z]+<\/td>/ismU', $text, $match))
      {
        $employees = str_replace(',', '', $match[2]);
      }
      elseif ($this->year > 2004 && preg_match('/<td class="highlight" nowrap="nowrap">([\d,]+)<\/td> <td class="highlight" nowrap="nowrap">[A-Z][a-z]{2,}<\/td>/', $text, $match))
      {
        $employees = str_replace(',', '', $match[1]);
      }

      /*$this->printDebug( "URL: ". $url);
      $this->printDebug( "Rank: " . $rank );
      $this->printDebug( "Name: " . $name );
      $this->printDebug( "Industry: " . $industryName );
      $this->printDebug( "Street: " . $street1 );
      $this->printDebug( "Street2: " . $street2 );
      $this->printDebug( "City: " . $city );
      $this->printDebug( "State: " . $state );
      $this->printDebug( "Postal: " . $postal );
      $this->printDebug( "Phone: " . $phone );
      $this->printDebug( "Fax: " . $fax );
      $this->printDebug( "Website: " . $website );
      $this->printDebug( "CEO: " . $ceoName . "  " . $ceoBirthYear);
      $this->printDebug( "Summary: " . $summary );
      $this->printDebug( "Revenue: " . $revenue );
      $this->printDebug( "Employees: " . $employees );*/

      $search_company_name = trim(implode(' ', array_diff(explode(' ', ucwords(strtolower($name))), array_merge( LsLanguage::$business, LsLanguage::$businessAbbreviations))));

      //continue;				
      $this->printDebug("$search_company_name == $name");				
      if($company = EntityTable::getByExtensionQuery(array('Org','PrivateCompany'))->addWhere("LOWER(REPLACE( org.name, '-' , '')) = ?", strtolower($name))->fetchOne())
      {
        $this->printDebug("Company exists");				
        $company->revenue = $revenue;
        $company->save();
      }
      else
      {
        $this->printDebug("Creating new company $name");				      
        Doctrine::getTable('ExtensionDefinition')->clear();
        $company = new Entity;
        $company->addExtension('Org');		
        $company->addExtension('Business');				
        $company->addExtension('PrivateCompany');        
        $company->name = LsLanguage::titleize($name);				
        
        
  
        $company->employees = strlen($employees) ? $employees : null;
        $company->revenue = strlen($revenue) ? $revenue : null;
        $company->website = strlen($website) ? $website : null;
        $company->summary = strlen($summary) ? trim($summary) : null;
        
        //add address
        if ($phone)
        {
          $company->addPhone($phone);
        }
    
        if ($fax)
        {
          //$company->addPhone($fax);
        }
  
        
        if ($city && $state)
        {
          $address = new Address;
          $address->street1 = strlen($street1) ? $street1 : null;
          $address->street2 = strlen($street2) ? $street2 : null;
          $address->city = strlen($city) ? $city : null;
        
          if ($state = AddressStateTable::retrieveByText($state))
          {
            $address->State = $state;
          }
        
          $address->postal = $postal;          
          $company->addAddress($address);
          $address->save();
          $address->addReference( $source = $url, 
                                  $excerpt=null, 
                                  $fields=array('city', 'country_id', 'postal', 'state_id', 'street1'), 
                                  $name='Forbes.com', 
                                  $detail=null, 
                                  $date=null);
        }
      }
  
      /*$this->printDebug( "URL: ". $url);
      $this->printDebug( "Rank: " . $rank );
      $this->printDebug( "Name: " . $name );
      $this->printDebug( "Industry: " . $industryName );
      $this->printDebug( "Street: " . $street1 );
      $this->printDebug( "Street2: " . $street2 );
      $this->printDebug( "City: " . $city );
      $this->printDebug( "State: " . $state );
      $this->printDebug( "Postal: " . $postal );
      $this->printDebug( "Phone: " . $phone );
      $this->printDebug( "Fax: " . $fax );
      $this->printDebug( "Website: " . $website );
      $this->printDebug( "CEO: " . $ceoName . "  " . $ceoBirthYear);
      $this->printDebug( "Summary: " . $summary );
      $this->printDebug( "Revenue: " . $revenue );
      $this->printDebug( "Employees: " . $employees );*/

      
      $company->save();
      $company->addReference( $source = $url, 
                              $excerpt=null, 
                              $fields=array('website','name', 'website', 'summary', 'revenue', 'employees'), 
                              $name='Forbes.com', 
                              $detail=null, 
                              $date=null);


      $this->saveToList($company, $rank);
		}
		else
		{
			$this->printDebug("Couldn't get company: " . $url );
		}
	}
  
 
}
