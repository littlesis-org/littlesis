<?php

class LsExternalApiKeys
{
  
  
  static function findKeys($entity, $domain)
  {
    $search_terms = $entity->name;
    $apiClass = 'Ls' . $domain;
    $api = new $apiClass();
    $matches = $api->findKeys($entity);
    return $matches;
  }


}