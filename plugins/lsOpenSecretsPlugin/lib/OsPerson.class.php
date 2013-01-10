<?php

class OsPerson
{
  static function getMatchedDonations($id)
  {
    $db = LsDb::getDbConnection();
    $sql = "SELECT d.* FROM littlesis_raw.os_donation d " .
           "LEFT JOIN littlesis.os_entity_transaction et ON (d.cycle = et.cycle AND d.row_id = et.transaction_id) " .
           "WHERE et.entity_id = ? AND et.is_verified = 1";
    $params = array($id);
    $stmt = $db->execute($sql, $params);
    $donations = $stmt->fetchAll(PDO::FETCH_ASSOC);

    return $donations;
  }

  static function getCategoriesFromMatchedDonations($id)
  {
    $donations = self::getMatchedDonations($id);

    return OsCategoryTable::getCategoryIdsFromDonations($donations);
  }

  static function getOrgAliasesFromMatcheDonations($id, $filters)
  {
    $aliases = array();

    foreach (self::getMatchedDonations($id, $filters) as $donation)
    {
      if ($alias = $donation['employer_name'])
      {
        $aliases[$alias] = true;
      }
    }

    return array_keys($aliases);
  }
  
  static function addCategory($id, $categoryId, $source=null)
  {
    return OsEntity::addCategory($id, $categoryId, $source);
  }
  
  static function getCategoryIds($id)
  {
    return OsEntity::getCategoryIds($id);  
  }
  
  static function updateCategories($id)
  {
    $categoryIds = self::getCategoriesFromMatchedDonations($id);
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
}