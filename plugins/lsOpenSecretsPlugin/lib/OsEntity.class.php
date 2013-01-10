<?php

class OsEntity
{
  static function addCategory($id, $categoryId, $source=null)
  {
    $db = LsDb::getDbConnection();
    $sql = "INSERT INTO os_entity_category (entity_id, category_id, source, created_at, updated_at) " .
           "VALUES (?, ?, ?, ?, ?) ".
           "ON DUPLICATE KEY UPDATE updated_at = ?";
    $now = LsDate::getCurrentDateTime();
    return $stmt = $db->execute($sql, array($id, $categoryId, $source, $now, $now, $now));    
  }
  
  static function getCategoryIds($id)
  {
    $db = LsDb::getDbConnection();
    $sql = "SELECT DISTINCT(category_id) FROM os_entity_category WHERE entity_id = ?";
    $stmt = $db->execute($sql, array($id));
    return $stmt->fetchAll(PDO::FETCH_COLUMN);
  }
  
  static function getCategories($id, $sort=false)
  {
    $db = LsDb::getDbConnection();
    $sql = "SELECT c.* FROM os_entity_category ec " .
           "LEFT JOIN os_category c ON (ec.category_id = c.category_id) " .
           "WHERE ec.entity_id = ?";
    if ($sort)
    {
      $sql .= " ORDER BY c.category_name ASC";
    }
    
    $stmt = $db->execute($sql, array($id));
    return $stmt->fetchAll(PDO::FETCH_ASSOC);  
  }
  
  static function getCategoriesForDisplay($id)
  {
    $filtered = array();
    $categories = self::getCategories($id, $sort=true);
    
    foreach ($categories as $category)
    {
      if (!in_array($category['category_id'], OsCategoryTable::$ignoreCategories))
      {
        $filtered[] = $category;
      }
    }
    
    return $filtered;
  }
}