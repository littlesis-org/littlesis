<?php

class OsOrg
{
  static function addCategory($id, $categoryId, $source=null)
  {
    return OsEntity::addCategory($id, $categoryId, $source);
  }

  static function getCategoryIds($id)
  {
    return OsEntity::getCategoryIds($id);  
  }

  static function getCategoriesFromPersons($id, $name, $exactNameOverride=false)
  {
    $categories = array();        
    $topCategories = array();
    $filters = self::getNameParts($id, $name);

    //get categories from persons with position in this org
    $numPersonsWithCategories = 0;
    $persons = OrgTable::getPersonsWithPositions($id);

    foreach ($persons as $person)
    {
      $donations = OsPerson::getMatchedDonations($person['id']);      

      if ($exactNameOverride)
      {
        $exactCategories = self::getCategoriesFromDonationsWithExactName($donations, $name);
        $topCategories = array_merge($topCategories, $exactCategories);
      }

      $donations = self::filterDonations($donations, $filters);      
      $categoryIds = OsCategoryTable::getCategoryIdsFromDonations($donations);

      $hasCategory = false;

      foreach ($categoryIds as $categoryId)
      {
        if (in_array($categoryId, $topCategories)) 
        { 
          $numPersonsWithCategories++;
          continue; 
        }

        if (in_array($categoryId, OsCategoryTable::$ignoreCategories)) { continue; }
      
        if (isset($categories[$categoryId]))
        {
          $categories[$categoryId]++;
        }
        else
        {
          $categories[$categoryId] = 1;
        }   
        
        $hasCategory = true;                     
      }   
      
      if ($hasCategory) { $numPersonsWithCategories++; }       
    }

    $topCategories = array_unique($topCategories);
    arsort($categories);

    foreach ($categories as $categoryId => $num)
    {
      if ($num >= max(2, $numPersonsWithCategories/5))
      {
        $topCategories[] = $categoryId;
      }
    }

    return array_unique($topCategories);
  }

  static function filterDonations($donations, $filters)
  {
    $filtered = array();

    $func = function($value) { return preg_quote($value); };
    $filters = array_map($func, $filters);
    $pattern = "/" . join("|", $filters) . "/i";

    foreach ($donations as $donation)
    {
      if (!$donation['employer_name']) { continue; }

      if (preg_match($pattern, $donation['employer_name']) == 1)
      {
        $filtered[] = $donation;
      }
    }
    
    return $filtered;
  }

  static function updateCategories($id, $name, $exactNameOverride=false)
  {
    $categoryIds = self::getCategoriesFromPersons($id, $name, $exactNameOverride);
    
    $existingCategoryIds = self::getCategoryIds($id);
    $newCategories = array();

    foreach ($categoryIds as $categoryId)
    {
      if (in_array($categoryId, $existingCategoryIds)) { continue; }
      self::addCategory($id, $categoryId, $source="OpenSecrets");
      $newCategories[] = $categoryId;
    }

    OsMatchIndustryCodesTask::logUpdate($id);
    
    return $newCategories;
  }

  static function updateAliasesFromPersonDonations($id, $name)
  {
    $aliases = self::getAliasesFromPersonDonations($id, $name);

    foreach ($aliases as $alias)
    {
      self::addAlias($id, $alias);
    }    
  }

  static function getAliasesFromPersonDonations($id, $name)
  {
    $allAliases = array();
    $filters = self::getNameParts($id, $name);

    //get org aliases from donation records of persons with position in this org
    $persons = OrgTable::getPersonsWithPositions($id);

    foreach ($persons as $person)
    {
      $aliases = OsPerson::getOrgAliasesFromMatchedDonations($person['id'], $filters);      
      $allAliases = array_unique(array_merge($allAliases, $aliases));      
    }

    return $allAliases;
  }

  static function addAlias($id, $alias)
  {
    $db = LsDb::getDbConnection();
    $sql = "INSERT IGNORE INTO alias (entity_id, name, context, updated_at, created_at) VALUES (?, ?, ?, ?, ?)";
    $now = LsDate::getCurrentDateTime();
    return $stmt = $db->execute($sql, array($id, $alias, "opensecrets", $now, $now));    
  }
  
  static function getNameParts($id, $name=null)
  {
    if (!$name)
    {
      $name = EntityTable::getName($id);
    }

    //get base parts of org name to filter donations
    $parts = preg_split('#[ \\\/]#', OrgTable::stripNamePunctuation($name));    
    $parts = self::filterNameParts($parts);

    if (count($parts) == 0 || (count($parts) == 1 && $parts[0] == strtoupper($parts[0])))
    {
      $moreParts = array();
      $aliases = AliasTable::getByEntityId($id, false);

      foreach ($aliases as $alias)
      {
        $aliasParts = preg_split('#[ \-\\\/]#', OrgTable::stripNamePunctuation($alias));
        $aliasParts = self::filterNameParts($aliasParts);
        
        foreach ($aliasParts as $aliasPart)
        {
          $moreParts[] = $aliasPart;
          if (count($moreParts) > 1) { break 2; }
        }
      }
      
      // trick for finding unique values of merged $parts + $moreParts without changing the order:
      $parts = array_keys(array_count_values(array_merge($parts, $moreParts)));
    }

    $parts = count($parts) ? array_slice($parts, 0, 2) : array($name);  

    return $parts;
  }
  
  static function filterNameParts($parts)
  {
    $filtered = array();
  
    foreach ($parts as $part)
    {
      if (strlen($part) > 2 && $part != 'The')
      {
        $filtered[] = $part;
      }
    }    

    return $filtered;
  }

  static function getCategoriesFromDonationsWithExactName($donations, $name)
  {
    $categories = array();
    $exactNames = array();

    foreach ($donations as $donation)
    {
      if ($category = $donation['industry_id'])
      {
        if (in_array($category, OsCategoryTable::$ignoreCategories)) { continue; }

        $cleanOrgName = self::cleanNameForCategoryMatching($name);
        $cleanEmployerName = self::cleanNameForCategoryMatching($donation['employer_name']);

        if ($cleanOrgName && ($cleanOrgName == $cleanEmployerName))
        {
          $categories[strtoupper($category)] = true;
        }
      }      
    }

    return array_keys($categories);  
  }
  
  static function cleanNameForCategoryMatching($name)
  {
    $name = strtolower(OrgTable::removeSuffixes($name));
    $name = str_replace("'", "", $name);

    return $name;
  }
}