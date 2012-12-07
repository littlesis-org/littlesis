<?php

class LsLanguage
{
	static $prefixes = array();
	
	static $generationalSuffixes = array('Jr', 'Sr', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII', 'IX', 'X', 'XI', 'XII');
	
	static $malePrefixes = array('Sir','Mr');
	
	static $femalePrefixes = array('Mrs','Ms','Miss','Lady');
	
	static $commonPrefixes = array('Mr', 'Mrs', 'Ms', 'Miss');
	
	static $punctuations = array('"', '_', '-','.', '\'', '?','!','*','=','Ó','%','@','&',',','/');

	static $months = array(
		'January', 
		'February',
		'March',
		'April',
		'May',
		'June',
		'July',
		'August',
		'September',
		'October',
		'November',
		'December'
	);
	
	static $weekdays = array(
		'Monday',
		'Tuesday',
		'Wednesday',
		'Thursday',
		'Friday',
		'Saturday',
		'Sunday'
	);

	static $regions = array(
		'Asia',
		'Africa',
		'North America',
		'South America',
		'Europe',
		'Middle East',
		'East Asia',
		'Western Europe',
		'Eastern Europe',
		'Near East',
		'Asia Pacific',
		'South Pacific',
		'America'
	);
	
	static $geography = array(
		'Southern',
		'Northern',
		'Western',
		'Eastern',
	);
	
	static $grammar = array(
		'Since',
		'From',
		'And',
		'Until',
		'When',
		'Upon',
		'The',
		'There',
		'Here',
		'Over',
		'When',
		'It',
		'In',
		'Prior',
		'That'
	);
	
	static $possessives = array(
		'He',
		'She',
		'His',
		'Her',
		'Their',
		'Our'
	);

	static $schools = array(
		'School',
		'University',
		'College',
		'Department',
		'Faculty',
		'Master',
		'Bachelor',
		'Doctorate',
		'State'
	);

	static $businessAbbreviations = array(
		'Inc',
		'Incorporated',
		'Co',		
		'Cos',
		'Companies',
		'Company',
		'Corp',
		'Corporation',
		'Bancorp',
		'Bancorporation',
		'&',
		'and',
		'Ins',
		'Insurance',
		'Ltd',
		'Limited',
		'LLP',
		'LLC',
		'LP',
		'PA'
	);
	
	static $business = array(
		'American',
		'Board',
		'Directors',
		'Corp',
		'Company',
		'Inc',
		'CEO',
		'Chief',
		'President',
		'Executive',
		'Director',
		'Vice',
		'Chair',
		'Chairman',
		'COO',
		'CFO',
		'EVP',
		'SVP',
		'Treasurer',
		'Secretary',
		'Controller',
		'Committee',
		'LLC',
		'LLP',
		'Corporation',
		'Co',
		'Fund',
		'Bank',
		'Industries',
		'Financial',
		'Bancorp',
		'Holding',
		'Holdings',
		'Insurance',
		'International',
		'Trust',
		'Equity',
		'Stores',
		'Companies',
		'Restaurants',
		'Communications',
		'Enterprises',
		'Energy',
		'Air',
		'Systems',
		'Consulting',
		'Partners',
		'Limited',
		'Ltd',
		'Development',
		'Management',
		'Realty',
		'Health',
		'Medical',
		'Center',
		'Engineering',
		'Corporate',
		'Business',
		'Senior',
		'Group',
		'Solutions',
		'Service',
		'Worldwide',

		
	);

  static $commonPositions = array(
    'Director',
    'Executive Vice President',
    'President',
    'CEO',
    'Senior Vice President',
    'CFO',
    'Chairman',
    'Vice President',
    'General Counsel',
    'Governor',
    'Secretary',
    'SVP',
    'COO',
    'Ranking Member',
    'Chief Financial Officer',
    'Ex Officio',
    'Vice Chairman',
    'Chair',
    'Chairman of the Board',
    'Treasurer',
    'Controller',
    'Chief Executive Officer',
    'Chief Operating Officer',
    'Executive',
    'CAO',
    'Senior Executive Vice President',
    'Chief Accounting Officer',
    'Chief Administrative Officer',
    'Member',
    'Group President',
    'CIO',
    'Executive Chairman',
    'Chief Legal Officer',
    'Trustee',
    'Group Vice President',
    'Chief Information Officer');
	
	static $commonFirstNames = array(
		'John',
		'Robert',
		'William',
		'James',
		'Michael',
		'David',
		'Richard',
		'Thomas',
		'Charles',
		'Peter',
		'J',
		'Paul',
		'George',
		'Steven',
		'Stephen',
		'Edward',
		'Donald',
		'Joseph',
		'Ronald',
		'Mark',
		'Frank',
		'W',
		'Gary',
		'Daniel',
		'Jeffrey',
		'Dennis',
		'Kenneth',
		'Douglas',
		'Alan',
		'Lawrence',
		'Patrick',
		'Philip',
		'Mary',
		'Henry',
		'Bruce',
		'H',
		'R',
		'Ralph',
		'Timothy',
		'C',
		'Gerald',
		'Roger',
		'Anthony',
		'Larry',
		'Susan',
		'Arthur',
		'A',
		'Brian',
		'Jack',
		'Andrew',
		'Martin',
		'Christopher',
		'Linda',
		'Patricia',
		'Frederick',
		'Walter',
		'Howard',
		'E',
		'Jerry',
		'Gregory',
		'Scott',
		'M',
		'Barbara',
		'Ann',
		'Carl',
		'Kevin',
		'Raymond',
		'Eric',
		'Craig',
		'G',
		'Herbert',
		'Keith',
		'D',
		'Louis',
		'Elizabeth',
		'Matthew',
		'Albert',
		'Harold',
		'Wayne',
		'Carol',
		'Judith',
		'Joe',
		'L',
		'Nicholas',
		'Gordon',
		'Eugene',
		'Barry',
		'Nancy',
		'Jon',
		'Terry',
		'Victor',
		'Norman',
		'Margaret',
		'Jim',
		'Harvey',
		'Marc',
		'Anne',
		'Theodore',
		'Deborah',
		'Jane'
	);

	static $commonLastNames = array(	
		'Smith',
		'Johnson',
		'Miller',
		'Davis',
		'Brown',
		'Jones',
		'Williams',
		'Thompson',
		'Anderson',
		'Moore',
		'Martin',
		'White',
		'Wilson',
		'Campbell',
		'Rogers',
		'Lewis',
		'Murphy',
		'Turner',
		'Sullivan',
		'Nelson',
		'Kennedy',
		'Thomas',
		'Ryan',
		'Carter',
		'Kelly',
		'Clark',
		'Jackson',
		'Young',
		'Watson',
		'Cook',
		'Green',
		'Shaw',
		'Roberts',
		'Bell',
		'Harris',
		'Cohen',
		'King',
		'Taylor',
		'Scott',
		'Ross',
		'Shapiro',
		'Fisher',
		'Hunt',
		'Powell',
		'Adams',
		'Wright',
		'Ford',
		'Collins',
		'Jordan',
		'Evans'
	);

	static $commonCities = array(
		'New York',
		'Los Angeles',
		'Chicago',
		'Houston',
		'Phoenix',
		'Philadelphia',
		'San Antonio',
		'San Diego',
		'Dallas',
		'San Jose',
		'Detroit',
		'Jacksonville',
		'Indianapolis',
		'San Francisco',
		'Columbus',
		'Austin',
		'Memphis',
		'Fort Worth',
		'Baltimore',
		'Charlotte',
		'El Paso',
		'Boston',
		'Seattle',
		'Washington',
		'Milwaukee',
		'Denver',
		'Louisville/Jefferson County (balance)',
		'Las Vegas',
		'Nashville-Davidson (balance)',
		'Oklahoma City',
		'Portland',
		'Tucson',
		'Albuquerque',
		'Atlanta',
		'Long Beach',
		'Fresno',
		'Sacramento',
		'Mesa',
		'Kansas City',
		'Cleveland',
		'Virginia Beach',
		'Omaha',
		'Miami',
		'Oakland',
		'Tulsa',
		'Honolulu',
		'Minneapolis',
		'Colorado Springs',
		'Arlington',
		'Wichita',
		'San Juan'
	);

	static $states = array(
		'Alaska',
		'Alabama',
		'Arizona',
		'Arkansas',
		'California',
		'Colorado',
		'Connecticut',
		'Delaware',
		'Florida',
		'Georgia',
		'Guam',
		'Hawaii',
		'Idaho',
		'Illinois',
		'Indiana',
		'Iowa',
		'Kansas',
		'Kentucky',
		'Louisiana',
		'Maine',
		'Maryland',
		'Massachusetts',
		'Michigan',
		'Minnesota',
		'Mississippi',
		'Missouri',
		'Montana',
		'Nebraska',
		'Nevada',
		'Hampshire',
		'New Hampsire',
		'Jersey',
		'New Jersey',
		'Mexico',
		'New Mexico',
		'York',
		'New York',
		'Carolina',
		'North Carolina',
		'Dakota',
		'North Dakota',
		'Ohio',
		'Oklahoma',
		'Oregon',
		'Pennsylvania',
		'Rhode',
		'Island',
		'Rhode Island',
		'South Carolina',
		'South Dakota',
		'Tennessee',
		'Texas',
		'Utah',
		'Vermont',
		'Virginia',
		'West Virginia',
		'Washington',
		'Wisconsin',
		'Wyoming',
		'New',
		'North',
		'South',
		'West',
		'United States',
	);
	
	static $countries = array(
	  'Afghanistan',
    'Albania',
    'Algeria',
    'Andorra',
    'Angola',
    'Antigua and Barbuda',
    'Argentina',
    'Armenia',
    'Australia',
    'Austria',
    'Azerbaijan',
    'Bahamas',
    'Bahrain',
    'Bangladesh',
    'Barbados',
    'Belarus',
    'Belgium',
    'Belize',
    'Benin',
    'Bhutan',
    'Bolivia',
    'Bosnia and Herzegovina',
    'Botswana',
    'Brazil',
    'Brunei',
    'Bulgaria',
    'Burkina Faso',
    'Burundi',
    'Cambodia',
    'Cameroon',
    'Canada',
    'Cape Verde',
    'Central African Republic',
    'Chad',
    'Chile',
    'China',
    'Colombia',
    'Comoros',
    'Congo (Brazzaville)',
    'Congo, Democratic Republic of the',
    'Costa Rica',
    'Côte d\'Ivoire',
    'Croatia',
    'Cuba',
    'Cyprus',
    'Czech Republic',
    'Denmark',
    'Djibouti',
    'Dominica',
    'Dominican Republic',
    'East Timor',
    'Ecuador',
    'Egypt',
    'El Salvador',
    'Equatorial Guinea',
    'Eritrea',
    'Estonia',
    'Ethiopia',
    'Fiji',
    'Finland',
    'France',
    'Gabon',
    'Gambia, The',
    'Georgia',
    'Germany',
    'Ghana',
    'Greece',
    'Grenada',
    'Guatemala',
    'Guinea',
    'Guinea-Bissau',
    'Guyana',
    'Haiti',
    'Honduras',
    'Hungary',
    'Iceland',
    'India',
    'Indonesia',
    'Iran',
    'Iraq',
    'Ireland',
    'Israel',
    'Italy',
    'Jamaica',
    'Japan',
    'Jordan',
    'Kazakhstan',
    'Kenya',
    'Kiribati',
    'Korea, North',
    'Korea, South',
    'Kuwait',
    'Kyrgyzstan',
    'Laos',
    'Latvia',
    'Lebanon',
    'Lesotho',
    'Liberia',
    'Libya',
    'Liechtenstein',
    'Lithuania',
    'Luxembourg',
    'Macedonia, Former Yugoslav Republic of',
    'Madagascar',
    'Malawi',
    'Malaysia',
    'Maldives',
    'Mali',
    'Malta',
    'Marshall Islands',
    'Mauritania',
    'Mauritius',
    'Mexico',
    'Micronesia, Federated States of',
    'Moldova',
    'Monaco',
    'Mongolia',
    'Montenegro',
    'Morocco',
    'Mozambique',
    'Myanmar (Burma)',
    'Namibia',
    'Nauru',
    'Nepal',
    'Netherlands',
    'New Zealand',
    'Nicaragua',
    'Niger',
    'Nigeria',
    'Norway',
    'Oman',
    'Pakistan',
    'Palau',
    'Panama',
    'Papua New Guinea',
    'Paraguay',
    'Peru',
    'Philippines',
    'Poland',
    'Portugal',
    'Qatar',
    'Romania',
    'Russia',
    'Rwanda',
    'Saint Kitts and Nevis',
    'Saint Lucia',
    'Saint Vincent and The Grenadines',
    'Samoa',
    'San Marino',
    'Sao Tome and Principe',
    'Saudi Arabia',
    'Senegal',
    'Serbia',
    'Seychelles',
    'Sierra Leone',
    'Singapore',
    'Slovakia',
    'Slovenia',
    'Solomon Islands',
    'Somalia',
    'South Africa',
    'Spain',
    'Sri Lanka',
    'Sudan',
    'Suriname',
    'Swaziland',
    'Sweden',
    'Switzerland',
    'Syria',
    'Taiwan',
    'Tajikistan',
    'Tanzania',
    'Thailand',
    'Togo',
    'Tonga',
    'Trinidad and Tobago',
    'Tunisia',
    'Turkey',
    'Turkmenistan',
    'Tuvalu',
    'Uganda',
    'Ukraine',
    'United Arab Emirates',
    'United Kingdom',
    'United States',
    'Uruguay',
    'Uzbekistan',
    'Vanuatu',
    'Vatican City',
    'Venezuela',
    'Vietnam',
    'Western Sahara',
    'Yemen',
    'Zambia',
    'Zimbabwe');

	static $commonAcronyms = array(
		'3M',
		'3PL',
		'A&W',
		'AA',
		'AAA',
		'ADP',
		'AIG',
		'AMD',
		'AMF',
		'AOL',
		'AT&T',
		'AX',
		'B&N',
		'B2B',
		'B2C',
		'BBB',
		'BHAG',
		'BMC',
		'BOI',
		'CEO',
		'CFO',
		'CK',
		'COO',
		'CPK',
		'CRM',
		'CSP',
		'CTO',
		'DBA',
		'DHL',
		'DNC',
		'DSO',
		'DSW',
		'EA',
		'EBX',
		'EMS',
		'EMC',
		'FLP',
		'FSB',
		'FT',
		'FTE',
		'GE',
		'GM',
		'GMC',
		'GNC',
		'HP',
		'IBD',
		'IBM',
		'ILM',
		'IMR',
		'ING',
		'ITT',
		'IREA',
		'JC',
		'JOA',
		'JVC',
		'KB',
		'KFC',
		'KPI',
		'KPMG',
		'LLC',
		'LLP',
		'LP',
		'MCI',
		'MOH',
		'MSI',
		'MSN',
		'MSRP',
		'MVI',
		'NASDAQ',
		'NBB',
		'NCOA',
		'NDA',
		'NIB',
		'NSA',
		'OPM',
		'OPR',
		'OPT',
		'PAC',
		'P&G',
		'PG&E',
		'PBGC',
		'PC', 
		'PE',
		'PL',
		'PO',
		'POP',
		'PT',
		'PPL',
		'QA',
		'QAI',
		'QC',
		'QSC',
		'REI',
		'RNC',
		'RFP',
		'SBC',
		'SBI',
		'SFI',
		'SKU',
		'SM',
		'SOHO',
		'SOS',
		'TCBY',
		'TM',
		'TJX',
		'TSR',
		'UMB',
		'UPS',
		'USA',
		'USAA',
		'U.S',
		'US',
	  'PA',
		'LP'
	);	
	
	static $lowerCaseTitleWords = array(
		'of',
		'a',
		'the',
		'and',
		'an',
		'or',
		'nor',
		'but',
		'is',
		'if',
		'then', 
		'else',
		'when',
		'at',
		'from',
		'by',
		'on',
		'off',
		'for',
		'in',
		'out',
		'over',
		'to',
		'into',
		'with'
	); 
	
	
	static function ordinal($cardinal)
	{
		$cardinal = (int) $cardinal;
		$digit = substr($cardinal, -1, 1);
		
		if ($cardinal <100) $tens = round($cardinal/10);
		else $tens = substr($cardinal, -2, 1);
		
		if ($tens == 1)  
		{
			return $cardinal.'th';
		}
		
		switch ($digit) 
		{
			case 1:
				return $cardinal.'st';
			case 2:
				return $cardinal.'nd';
			case 3:
				return $cardinal.'rd';
			default:
				return $cardinal.'th';
		}
	}

	/*
		returns the number of words that are in both str1 and str2, but excludes everything in exclude
	*/
	static function getCommonPronouns($str1, $str2, array $exclude=array())
	{
		//get first set of pronouns, converted to lowercase
		preg_match_all('/(?<!\')[\p{Lu}]+[A-Za-z\-\']{2,}/', $str1, $matches1);
		$matches1 = array_unique($matches1[0]);
		foreach ($matches1 as &$match)
		{
			$match = strtolower($match);
		}

		//get second set of pronouns, converted to lowercase
		preg_match_all('/(?<!\')[\p{Lu}]+[A-Za-z\-\']{2,}/', $str2, $matches2);
		$matches2 = array_unique($matches2[0]);
		foreach ($matches2 as &$match)
		{
			$match = strtolower($match);
		}

		$common = array_intersect($matches1, $matches2);

		foreach ($exclude as &$word)
		{
			$word = strtolower($word);
		}
		
		$common = array_diff($common, $exclude);

		return $common;	
	}

	static function getNames($str, $minWords=1)
	{
		$names = array();
		preg_match_all('/([\p{Lu}]([A-Za-z\-]+|\.)?( |\. |\,|\?|\'|<)){' . $minWords . ',}/', $str, $matches, PREG_PATTERN_ORDER);

		foreach ($matches[0] as $match)
		{		
			$match = substr($match, 0, -1);
			$match = str_replace('.', '', $match);
			$excludes = array_merge(self::$months, self::$weekdays, self::$regions, self::$geography, self::$grammar, self::$possessives, self::$states, self::$business);
			
			if (in_array($match, $excludes))
			{
				continue;
			}
		
			$names[] = $match;
			
			$names = array_unique($names);
		}
		
		return $names;
	}

	static function parsePositions($str)
	{
		$positions = array();

		if (strstr($str, ';'))
		{
			$parts = explode(';', $str);
		}
		else
		{
			$parts = explode(',', $str);

			if (count($parts) == 2)
			{
				$firstPart = trim($parts[0]);
				$secondPart = trim($parts[1]);
				
				if (count(explode(' ', $secondPart)) == 1 && !in_array($secondPart, self::$business))
				{
					$parts = array($firstPart . ', ' . $secondPart);
				}
			}				
		}
		
		foreach ($parts as $part)
		{
			$subparts = explode(',', $part);

			if (count($subparts) == 2 && stristr($subparts[1], 'Director'))
			{
				$positions[] = trim($subparts[0]);
				$positions[] = trim($subparts[1]);
				continue;
			}

			if (stristr($part, ' of ') || strstr($part, ','))
			{
				$positions[] = trim($part);
			}
			else
			{
				$subparts = preg_split('/and|&/', $part);
				
				foreach ($subparts as $subpart)
				{
					$positions[] = trim($subpart);
				}
			}
		}
		
		$positions = array_diff($positions, self::$regions);
		$positions = array_unique($positions);
	
		return $positions;	
	}
	
	
	/*
	    builds regular expression out of name
	    allows for initials, abbreviations (Sam for Samuel), previously unknown middle names, and unknown full last names
  */
  
  static function buildLooseNameRegex ($name)
  {
    $name = str_replace(')','',$name);
    $name = str_replace('(','',$name);    
    
    $parts = preg_split('/[\s|\.\\\\\/]+/', $name, -1, PREG_SPLIT_NO_EMPTY);
    $first = array();
    $last = array();
    
    foreach ($parts as $p)
    {
      $p = LsString::escapeStringForRegex($p);
      $re_str = $p[0];
      if (strlen($p) > 1)
      {
        $str = substr($p,1);
        for($i = 0; $i < strlen($str); $i++)
        {
          if ($str[$i] != '\\')
          {
            $re_str .= $str[$i] . '?';
          }
          else $re_str .= $str[$i];
        }
        $last[] = $p[0] . "'?" . substr($p,1);
      }
      $first[] = $re_str[0] . "'?" . substr($re_str,1);
    }
    $first = implode('|', $first);
    $last = implode('|', $last);
    $separator = '\b([\'"\(\)\.]{0,3}\s+|\.\s*|\s?-\s?)?';
    
    $re = '/[^,<>\(\)]*?((\b(' . $first . ')\p{L}*' . $separator . '((\p{L}|[\'\-])+' . $separator . ')?)+((' . $last . ')\b\s*-?\s*)+)([^<>\d]{0,20})/isu';
    return $re;
  
  }  
  
 
  
  /*
    given a last name and string will pull the name from the string and returns a name array 
    eg array('nameFull' => 'Sarah Louise Palin Jr', 'nameStart' => 'Sarah Louise', 'nameLast' => 'Palin', 'namePost' => 'Jr')
    ideally will return a person object but that will take some futzing (to deal with nicknames, punctuation, etc)
  */
  
  static function getNameWithLast($str, $last)
  {
    $re_last = LsString::escapeStringForRegex($last);
    //hyphens and spaces interchangeable in last names
    $re_last = preg_replace('/\\\\s+|\\\\\-/is','(\s+|\-)',$re_last);
    $matches = array();
    $matched = preg_match_all('/\b' . $re_last . '\b/isu',$str, $matches, PREG_SET_ORDER | PREG_OFFSET_CAPTURE);
    $name = null;
    foreach ($matches as $match)
    {
      $pos_last = $match[0][1];
      if ($pos_last == 0) 
      {
        return null;
      }
      $last = $match[0][0];
      //work backwards from last name to find comma
      $pos = -1 * (strlen($str) - $pos_last);
      $comma = strripos($str,',',$pos);
      $str = substr($str,$comma);
      
      $splat = preg_split('/\b' . $re_last . '\b/is',$str);
      $pre = $splat[0];
      $post = $splat[1];
      
      $arr = array_reverse(preg_split('/[\s]+/', $pre, -1, PREG_SPLIT_NO_EMPTY));
      $new = array();
      
      foreach($arr as $a)
      {
        if ($case = LsString::checkCase($a))
        {
          if ($case == 'initial') 
          {
            $new[] = $a;
          }
          else if ($case == 'lower')
          {
            break;
          }
          else if (preg_match('/\.(\P{L})*$/u',$a) == 1)
          {
            $a = LsString::stripNonAlpha($a);
            if($s = LsArray::inArrayNoCase($a,PersonTable::$nameParsePrefixes))
            {
              $new[] = $s;
            }
            break;
          }
          else 
          {
            $new[] = $a;
          }
        }
      }
      
      $pre = implode(' ', array_reverse($new));
      
      if (strlen(trim($pre)) == 0) 
      {
        continue;
      }
      
      $arr = preg_split('/[\s]+/', $post, -1, PREG_SPLIT_NO_EMPTY);
      $new = array();
      
      foreach($arr as $a)
      {
        if ($case = LsString::checkCase($a))
        {
          if ($case == 'lower') break;
          $a = LsString::stripNonAlpha($a);
          if($s = LsArray::inArrayNoCase($a,PersonTable::$nameParseSuffixes))
          {
            $new[] = $s;
          }
          else
          {
            break;
          }
        }
      }
      $post = trim(implode(' ', $new));
      $full = $pre . ' ' . $last;
      if (strlen($post) > 0)
      {
        $full .= ', ' . $post;
      }

      $name = array('nameFull' => $full, 'nameStart' => $pre, 'nameLast' => $last, 'namePost' =>$post);

    }
    return $name;
  }
  
  
  /*
    capitalizes names properly
    not yet corrected so that McDonald doesn't become Mcdonald
  */
	static function  titleize($str, $capitalize_acronyms = true, $delimiters = array("'","-"," ","/","(","&","."), $prefixes = "Mc"){  


		//empty string. return it.
		if(strlen($str) == 0){
			return $str;
		}
		
		$prefixes = 'Mc';
    
		//lets look from common acronyms and capitalize them properly 
		if($capitalize_acronyms)
		{			
			$patterns = array();
			$replace = self::$commonAcronyms;
			
			foreach(self::$commonAcronyms as $key=> $acronym){
				$patterns[] = '/\b('.  addslashes( $acronym ) .')(\b|$)/ie';
			}
			$str = preg_replace($patterns, 'strtoupper("$1")', $str);
		}
		
		//is it an acronym. test to see if all the characters are capital if it's just one word
		if( strpos($str,' ') === false && strtoupper($str) == $str  && strlen($str) < 10){
			return $str;
		}
		

		//it seems to be properly capitalized. don't lose data. just confirm acronyms.
		$str_arr = explode(' ', $str);
		$pattern=null;
		foreach($str_arr as $word){
			if(LsString::checkCase($word) == 'capitalized'){

				$pattern= '/\b(';
				foreach(self::$lowerCaseTitleWords as $key=> $lcword){
					$pattern .= addslashes( $lcword ). "|";
				}
				$pattern .= 'of)(\b|$)/ie';

				//echo $pattern;
				$str = preg_replace($pattern, 'strtolower("$1")', $str);
				$str = preg_replace("/\\b($prefixes)(\w)/e", '"$1".strtoupper("$2")', $str);		
				$str = preg_replace("/\\b(\w)/e", 'strtoupper("$1")', $str);		
				$str = preg_replace("/(\'S)\b|$/e", 'stripslashes(strtolower("$1"))', $str);		
        $str = self::hgCaser($str,false,true);
				//echo "						End1: $str   \n";				
				$str = preg_replace('/\s(The|Of|And)\b/e','strtolower(" $1")',$str);
				return $str;
			}		
		}

		//bring the string to our level
		$string = strtolower($str);
		
		//break the words by delims
    foreach ($delimiters as $delimeter)
    {		
			if(preg_match('/\'s(\b|$)/i', $string) && $delimeter == "'" ){
				continue;
			}
			
      $pos = strpos($string, $delimeter);

      if ($pos)
      {				
        $mend = '';
        $words = explode($delimeter,$string);
        foreach ($words as $word){
					//capitalize each portion of the string which was separated at a special character          
					$mend .= in_array($word, self::$lowerCaseTitleWords) ?  $word.$delimeter: ucfirst($word).$delimeter;
        }
        $string = substr($mend,0,-1);				
      }
    }	

		//lets look from common acronyms and capitalize them properly 
		if($capitalize_acronyms){
			
			$patterns = array();
			$replace = self::$commonAcronyms;
			
			foreach(self::$commonAcronyms as $key=> $acronym){
				$patterns[] = '/\b('.  addslashes( $acronym ) .')(\b|$)/ie';
			}
			$string = preg_replace($patterns, 'strtoupper("$1")', $string);
		}
		
		//add prefixes
		$string = preg_replace("/\\b($prefixes)(\\w)/e", '"$1".strtoupper("$2")', $string);		
		$string = preg_replace('/\b(mc)(\w)/e', '"$1".strtoupper("$2")',$string);
    //echo "											End2 ".ucfirst($string)."\n";
	  $string = self::hgCaser($string, false, true);
		$string = preg_replace('/\s(The|Of|And)\b/e','strtolower(" $1")',$string);
    return ucfirst($string);	
	}
	
	
  //http://www.php.net/ucwords
  static function nameize($str, $capitalize_acronyms = false, $delimiters = array("'","-"," ","/","(","&",".") ){   
		//echo "Start: $str   ";
		$prefixes = 'MC|Mc';

		//empty string. return it.
		if(strlen($str) == 0){
			return $str;
		}
    
		//it seems to be properly capitalizaed. don't lose data. just confirm acronyms.
		$str_arr = explode(' ', $str);
		$pattern=null;
		foreach($str_arr as $word){
			if(LsString::checkCase($word) == 'capitalized'){
				$str = preg_replace("/\\b($prefixes)(\w)/e", '"$1".strtoupper("$2")', $str);		//confirm Mc
				$str = preg_replace("/\\b(\w)/e", 'strtoupper("$1")', $str);										//capitalize first letter
				$str = preg_replace("/(\'S)\b|$/e", 'stripslashes(strtolower("$1"))', $str);		//make sure the 's are lowercase
				//echo "						End1: $str   \n";			
				return $str;
			}		
		}
		
		//bring the string to our level
		$string = strtolower($str);
		
		//break the words by delims
    foreach ($delimiters as $delimeter)
    {		
      $pos = strpos($string, $delimeter);

			if(preg_match('/\'s(\b|$)/i', $string) && $delimeter == "'" ){
				continue;
			}
			
      if ($pos)
      {				
        $mend = '';
        $words = explode($delimeter,$string);
        foreach ($words as $word){
					//capitalize each portion of the string which was separated at a special character          
					$mend .= ucfirst($word).$delimeter;
        }
        $string = substr($mend,0,-1);				
      }
    }	
 				
		//add prefixes
		//$string = preg_replace('/\b(' . $prefixes . ')(\w)/e', '"$1".strtoupper("$2")', $string);		
		$string = preg_replace('/\b(mc)(\w)/e', '"$1".strtoupper("$2")',$string);
    return ucfirst($string);
  }
  

  static function hgCaser ($str, $exclude_last = true, $org = false)
  {
    $last = '';
    if ($exclude_last)
    {
      $arr = LsString::split($str);
      if (count($arr) > 1)
      {
        $last = array_pop($arr);
        $str = implode(' ', $arr);
      }
    }
    $exclude = array('jo','bo','ed','al','de','of');
    if ($org == true)
    {
      $exclude = array_merge($exclude, array('of','at','on','in','co', 'to', 're'));
    }
    if ($org == true)
    {
      $spacer = '';
    }
    else
    {
      $spacer = ' ';
    }
    $str = preg_replace('/\b(\p{L})(\p{L})(?<!(' . implode('|',$exclude) . '))\b/eisu','strtoupper("$1" . $spacer ."$2")',$str);
    $str .= ' ' . $last;
    $str = trim($str);
    
    return $str;
  } 


	static function parseFlatName($str){

		$namePrefix = $nameFirst = $nameMiddle = $nameLast = $nameSuffix = $nameNick = null;

		//trim and remove periods and commas
		$str = strip_tags($str);
		
    $name_in_reverse_order = false;
    if(strpos($str, ',') ){
      $name_in_reverse_order = true;
    }
      
    $name = LsLanguage::nameize( (str_ireplace( LsLanguage::$punctuations, '',$str)));
		
		$nameArray  = explode(" ", $name);

		foreach($nameArray as $key => $part){
			
      if($name_in_reverse_order){
        if($key == 0){
          $nameLast = $part;
        }
        
        if($key == 1){
          $nameFirst = $part;
        }
      }
      else{
        if($key == 0){
          $nameFirst = $part;
        }      
        
        if($key == 1){
          $nameLast = $part;
        }
        
      
      }
			
			if( in_array($part, LsLanguage::$generationalSuffixes) ){
				$nameSuffix = $part;
			}
			
			
			//find nickname in quotes
			if (preg_match('/\'([\S]+)\'|"([\S]+)"/', $part, $nickFound))
			{
				$nameNick = $nickFound[1] ? $nickFound[1] : $nickFound[2];
				$str = trim(preg_replace('/\'([\S]+)\'|"([\S]+)"/', '', $str));
			}
			
			if( $key == 2 AND !in_array($part, LsLanguage::$commonPrefixes) AND !in_array($part, LsLanguage::$generationalSuffixes)) {
				$nameMiddle = $part;
			}			
		
		}
		
		//return person with name fields
    return array( 'name_prefix' => $namePrefix, 'name_first' => $nameFirst, 'name_middle' => $nameMiddle, 
                  'name_last' => $nameLast, 'name_suffix' => $nameSuffix, 'name_nick' => $nameNick);			
  }


  static function pluralize($str)
  {
    switch (substr($str, -1))
    {
      case 'y':
        return substr($str, 0, -1) . 'ies';
        break;
      case 's':
        return $str . 'es';
        break;
      default:
        return $str . 's';
        break;    
    }
  }  

  

	static function parseCityStatePostal($address){
		$address = trim($address);
		$zip = '([a-z0-9][0-9][a-z0-9][- ]*[0-9][a-z0-9][0-9]*)([- ]+([0-9]{4}))*';
		$state = '([.a-z]{2,4})';
		
		if(!preg_match("/\s+$state\s+$zip".'$'."/i",$address,$a)){
			return false;
		}
		
		$state = strtoupper(str_replace('.','',$a[1]));
		$zip=$a[2];
		
		
		if(isset($a[4])){
			$plus4=$a[4];
			$zipplus4=$zip.'-'.$plus4;
		}
		
		$city = substr($address,0,strlen($address)-strlen($a[0]));
		$city = rtrim($city,',');
		$order = array( 'city', 'state', 'zip', 'plus4', 'zipplus4', 'country');

		foreach($order as $v){
			isset($$v)?$b[$v]=$$v:'';
		}
		return $b;
	}  
	
	static function getAllNames($str)
	{
	  $w = '(\p{Lu}\'?\p{L}+(\s+|\-|\'s?\s+)|\p{Lu}\.\s*)';
    $w2 = '(\p{Lu}\'?\p{L}+)';	  
	  $re = '/((\b' . $w . '(of\s+|\&\s+|for\s+|the\s+|at\s+|and\s+|' . $w . ')*' . $w2 . '|\p{Lu}\'?\p{L}{3,}))\b/su';
	  preg_match_all($re, $str, $matches);
	  return $matches[1];
	
	}
	
	static function getHtmlPersonNames($text)
  {
    $name_matches = array();
    
    $re = '/>\s*\p{Lu}\'?(\p{L}+|\.)?\s+\p{Lu}\.?\s+\p{Lu}\p{L}+(\,?\s+\p{Lu}\p{L}{1,4}\.?)?/su';
    $re2 = '/>\s*(\p{Lu}\'?(\p{L}+|\.)?\s+(\p{Lu}\'?(\s+|\p{L}+\s+|\.\s*)?){0,2}\p{Lu}\'?\p{L}+(\-\p{Lu}\'?\p{L}+)?(\,?\s+\p{Lu}\p{L}{1,4}\.?)?)\**\s*</su';
    
    $re3 = '/>\s*(\p{Lu}\'?\p{L}+(\-\p{Lu}\'?\p{L}+)?\,\s+(\p{Lu}\'?(\p{L}+|\.)?(\s+(\p{Lu}\'?(\s+|\p{L}+\s+|\.\s*)?){0,2})?)(\,?\s+\p{Lu}\p{L}{1,4}\.?)?)\**\s*</su';
    
    $text = LsHtml::replaceEntities($text);
    $name_matches = array();
    if (preg_match_all($re2,$text,$matches,PREG_OFFSET_CAPTURE))
    {
      //LOOP THROUGH MATCHES TO CONFIRM NAMES
      for($i = 0; $i < count($matches[1]); $i++)
      {
        $m = $matches[1][$i];          
        //echo $m[0] . "\n";
        $is_name = false;
        if (preg_match('/\s+\p{Lu}\.?\s/',$m[0]))
        {
          //echo '  * initial' . "\n";
          $is_name = true;
        }
        $parts = LsString::split(trim($m[0]));
        //ADD NAME TO MATCH LIST IF IT FITS CONDITIONS
        if (in_array($parts[0],LsLanguage::$commonFirstNames))
        {
          //echo '  * first name' . "\n";
          $is_name = true;
        }
        $q = LsDoctrineQuery::create()
          ->from('Person p')
          ->where('p.name_first = ?', $parts[0]);
        if ($q->count() > 0)
        {
          //echo '  LS name' . "\n";
          $is_name = true;
        } 
        if ($is_name)
        {
          $name_matches[] = $m[0];
        }
        /*
        if ($i != 0)
        {
          $beg = $matches[1][$i-1][1];
          $tweenstr = substr($text,$beg, $m[1] - $beg);
          //echo '  tag count: ' . LsHtml::tagCount($tweenstr) . "\n";
        }
        preg_match('/^[^\s]+\s/su',trim($m[0]),$match);
        
        $tags = LsHtml::getSurroundingTags($text,$m[1],3);*/
      }
    }
    if (preg_match_all($re3,$text,$matches,PREG_OFFSET_CAPTURE))
    {
      for($i = 0; $i < count($matches[1]); $i++)
      {
        $m = $matches[1][$i];          
        //echo $m[0] . "\n";
        $person = PersonTable::parseCommaName($m[0]);
        $name_matches[] = $person->getFullName(false);
      }

    }
    return $name_matches;
  }
  

}
