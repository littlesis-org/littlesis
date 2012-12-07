<?php

class LsQuery
{
  static function getByModelAndFieldsQuery($model, Array $fields)
  {
    if (!Doctrine::getTable($model))
    {
      throw new Exception("Can't produce query for nonexistent model " . $model);
    }

    $lower = strtolower($model);
    $q = LsDoctrineQuery::create()
      ->from($model . ' ' . $lower);
    
    foreach ($fields as $name => $value)
    {
      $q->addWhere($lower . '.' . $name . ' = ?', $value);
    }
    
    return $q;
  }


  static function addExtensionRequirement(Doctrine_Query $q, $extensions, $entityAlias='e', $aliasSuffix='')
  {
    return $q->innerJoin($entityAlias . '.ExtensionRecord eerr' . $aliasSuffix)
      ->innerJoin('eerr' . $aliasSuffix . '.Definition dd' . $aliasSuffix)
      ->andWhereIn('dd' . $aliasSuffix . '.name', (array) $extensions);
  }

  static function addRelationshipDetails(Doctrine_Query $q, $detailIds, $alias='r')
  {
    return $q->andWhereIn($alias . '.detail_id', (array) $detailIds);
  }
  
  static function addDates(Doctrine_Query $q, $startDate=null, $endDate=null, $alias='r')
  {
    $ret = clone $q;
    $ret->addWhere($alias . '.start_date IS NOT NULL');

    if ($startDate && $endDate)
    {
      $ret->addWhere($alias . '.start_date <= ' . $endDate . ' AND ' .
                     $alias . '.end_date >= ' . $startDate);
    }
    elseif ($startDate)
    {
      $ret->addWhere($alias . '.end_date >= ' . $startDate);
    }
    elseif ($endDate)
    {
      $ret->addWhere($alias . '.start_date >= ' . $endDate);    
    }
    else
    {
      $ret = $q; 
    }
    
    return $ret;
  }

  static function addConcurrence(Doctrine_Query $q, $alias1='r1', $alias2='r2')
  {
    $whereStr = $alias1 . '.start_date IS NOT NULL AND ' . $alias2 . '.start_date IS NOT NULL AND ' .
      '((' . $alias1 . '.end_date IS NULL AND ' . $alias2 . '.end_date IS NULL) OR ' .
      '(' . $alias1 . '.end_date IS NOT NULL AND ' . $alias1 . '.start_date < ' . $alias2 . '.end_date AND ' . $alias1 . '.end_date > ' . $alias2 . '.start_date) OR ' .
      '(' . $alias2 . '.end_date IS NOT NULL AND ' . $alias2 . '.start_date < ' . $alias1 . '.end_date AND ' . $alias2 . '.end_date > ' . $alias1 . '.start_date))';

    return $q->addWhere($whereStr);
  }
  
  static function splitSearchPhrase ($str)
  {
    $terms = preg_split('/[\s\.\-\,]+/isu',$str,-1,PREG_SPLIT_NO_EMPTY);
        
    foreach ($terms as &$term)
    {
      if ($term == '&' || $term == 'and')
      {
        $term = array('&','and');
      }    
      else if (preg_match('/^\p{Lu}{2,5}$/',$term))
      {
        $new = implode('.',str_split($term)) . '.';
        $term = array($term, $new);
      }
      else if (preg_match('/^(\p{Lu}\.)+$/su',$term))
      {
        $new = str_replace('.','',$term);
        $term = array($term, $new);
      }      
    }
    
    return $terms;
  }
  
}