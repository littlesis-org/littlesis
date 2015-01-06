<?php

class LsApi
{
  static $responseFields = array(
    'Entity' => array(
      'id' => 'id',
      'name' => 'name',
      'blurb' => 'description',
      'summary' => 'summary',
      'start_date' => 'start_date',
      'end_date' => 'end_date',
      'website' => 'website',
      'is_current' => 'is_current',
      'primary_ext' => 'primary_type',
      'parent_id' => 'parent_id',
      'updated_at' => 'updated_at',
      "CONCAT('http://littlesis.org/', LOWER(*.primary_ext), '/', *.id, '/', REPLACE(REPLACE(*.name, ' ', '_'), '/', '~'))" => 'uri',
      "CONCAT('http://api.littlesis.org/entity/', *.id, '.xml')" => 'api_uri'
    ),
    'Relationship' => array(
      'id' => 'id',
      'entity1_id' => 'entity1_id',
      'entity2_id' => 'entity2_id',
      'category_id' => 'category_id',
      'description1' => 'description1',
      'description2' => 'description2',
      'amount' => 'amount',
      'goods' => 'goods',
      'start_date' => 'start_date',
      'end_date' => 'end_date',
      'is_current' => 'is_current',
      'updated_at' => 'updated_at',
      'notes' => 'notes',
      "CONCAT('http://littlesis.org/relationship/view/id/', *.id)" => 'uri',
      "CONCAT('http://api.littlesis.org/relationship/', *.id, '.xml')" => 'api_uri'      
    ),
    'Position' => array(
      'is_board' => 'is_board',
      'is_executive' => 'is_executive',
      'is_employee' => 'is_employee'
    ),
    'LsList' => array(
      'id' => 'id',
      'name' => 'name',
      'description' => 'description',
      'is_ranked' => 'is_ranked',
      'updated_at' => 'updated_at',
      "CONCAT('http://littlesis.org/list/', *.id, '/', REPLACE(REPLACE(*.name, ' ', '_'), '/', '~'))" => 'uri',
      "CONCAT('http://api.littlesis.org/list/', *.id, '.xml')" => 'api_uri'
    ),
    'Reference' => array(
      'id' => 'id',
      'name' => 'name',
      'source' => 'source',
      'source_detail' => 'source_detail',
      'publication_date ' => 'publication_date',
      'updated_at' => 'updated_at'
    ),
    'Image' => array(
      'id' => 'id',
      'title' => 'title',
      'caption' => 'caption',
      'is_featured' => 'is_featured',
      'url' => 'url',
      'updated_at' => 'updated_at'
    )
  );


  static function checkApp()
  {
    if (sfContext::getInstance()->getConfiguration()->getApplication() == 'frontend')
    {
      self::$responseFields['Entity']['blurb'] = 'blurb';
      self::$responseFields['Entity']['primary_ext'] = 'primary_ext';
      self::$responseFields['Entity']['is_deleted'] = 'is_deleted';
      self::$responseFields['Entity']['updated_at'] = 'updated_at';
      self::$responseFields['Entity']['last_user_id'] = 'last_user_id';
      self::$responseFields['Relationship']['filings'] = 'filings';
      self::$responseFields['Relationship']['is_deleted'] = 'is_deleted';
      self::$responseFields['Relationship']['last_user_id'] = 'last_user_id';
    }
  }
  
  
  static function generateSelectQuery($ary, $usePrefix=false, $returnArray=false)
  {
    self::checkApp();

    $selectFields = array();

    foreach ($ary as $alias => $class)
    {
      $fields = self::$responseFields[$class];
      
      foreach ($fields as $old => $new)
      {
        if (strpos($old, '*') === false)
        {
          $pattern = '%s.%s AS %s';
          $new = $usePrefix ? ($alias . '__' . $new) : $new;
  
          $selectFields[] = sprintf($pattern, $alias, $old, $new);
        }
        else
        {
          $pattern = '%s AS %s';
          $new = $usePrefix ? ($alias . '__' . $new) : $new;
          $old = str_replace('*', $alias, $old);

          $selectFields[] = sprintf($pattern, $old, $new);
        }
      }
    }
    
    return $returnArray ? $selectFields : implode(', ', $selectFields);
  }
  
  
  static function filterResponseFields(Array $data, $class)
  {
    self::checkApp();

    $fields = self::$responseFields[$class];
    $filtered = array();

    foreach ($fields as $old => $new)
    {
      if (array_key_exists($old, $data))
      {
        $filtered[$new] = $data[$old];
      }
    }
    
    return $filtered;
  }


  static function getResponseTime()
  {
    return microtime(true) - $GLOBALS['startTime'];
  }


  static function sliceArrayFromOptions(Array $ary, Array $options, $defaultNum=100, $maxNum=500)
  {
    $num = @$options['num'] ? $options['num'] : $defaultNum;
    
    if ($num && $maxNum)
    {
      $num = ($num > $maxNum) ? $maxNum : $num;    
    }
    
    if ($num)
    {
      if (!$offset = @$options['offset'])
      {
        if ($page = @$options['page'])
        {
          $offset = $num * ($page - 1);
        }
        else
        {
          $offset = 0;
        }
      }
            
      $ret = array_slice($ary, $offset, $num);
    }
    else
    {
      $ret = $ary;
    }
    
    return $ret;
  }
  
  
  static function getPagingFromOptions(Array $options, $defaultNum=null, $maxNum=null)
  {
    $paging = '';
    $num = @$options['num'] ? $options['num'] : $defaultNum;

    if ($num && $maxNum)
    {
      $num = ($num > $maxNum) ? $maxNum : $num;
    }

    if ($num)
    {
      $paging = 'LIMIT ' . $num;

      if ($page = @$options['page'])
      {
        $paging .= ' OFFSET ' . $num * ($page - 1);
      }
    }
    
    return $paging;  
  }


  static function setPagingFromOptions(Doctrine_Query $q, Array $options, $defaultNum=null, $maxNum=null)
  {
    $num = @$options['num'] ? $options['num'] : $defaultNum;

    if ($num && $maxNum)
    {
      $num = ($num > $maxNum) ? $maxNum : $num;
    }

    if ($num)
    {
      $q->limit($num);

      if ($page = @$options['page'])
      {
        $q->offset($num * ($page - 1));
      }
    }

    return $q;  
  }
  
  
  static function authenticateKey($key)
  {
    $db = Doctrine_Manager::connection();
    $sql = 'SELECT u.request_limit, COUNT(r.id) AS requests FROM api_user u LEFT JOIN api_request r ON r.api_key = u.api_key AND r.created_at > DATE_SUB(NOW(), INTERVAL 1 DAY) WHERE u.api_key = ? AND u.is_active = ? GROUP BY u.id';
    $stmt = $db->execute($sql, array($key, true));
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($result === false)
    {
      return self::AUTH_FAIL_KEY;
    }
    
    if ($result['requests'] > $result['request_limit'])
    {
      return self::AUTH_FAIL_LIMIT;
    }
    
    return self::AUTH_SUCCESS;
  }  


  static function authenticateUserRequest($request)
  {
    $authResult = self::authenticateKey($request->getParameter('_key'));

    switch ($authResult)
    {
      case LsApi::AUTH_FAIL_KEY:
        $response = sfContext::getInstance()->getResponse();
        $response->setStatusCode(401);
        sfLoader::loadHelpers('Partial');
        $partial = get_partial('global/401');
        $response->setContent($partial);
        $response->setHttpHeader('WWW-Authenticate', 'Your request must include a query parameter named "_key" with a valid API key value. To obtain an API key, visit http://api.littlesis.org/register');
        $response->sendHttpHeaders();
        $response->sendContent();
        throw new sfStopException();
        break;
      case LsApi::AUTH_FAIL_LIMIT:
        $response = sfContext::getInstance()->getResponse();
        $response->setStatusCode(403);
        $user = Doctrine::getTable('ApiUser')->findOneByApiKey($request->getParameter('_key'));
        sfLoader::loadHelpers('Partial');
        $partial = get_partial('global/403', array('request_limit' => $user->request_limit));
        $response->setContent($partial);
        $response->sendHttpHeaders();
        $response->sendContent();
        throw new sfStopException();
        break;
      case LsApi::AUTH_SUCCESS:
        break;
      default:
        throw new Exception("Invalid return value from LsApi::autheticate()");
    }    
  }
  
  
  static function logUserRequest($request)
  {
    $db = Doctrine_Manager::connection();
    $sql = 'INSERT INTO api_request (api_key, resource, ip_address, created_at, updated_at) VALUES (?, ?, ?, ?, ?)';
    $stmt = $db->execute($sql, array(
      $request->getParameter('_key'),
      $_SERVER['PATH_INFO'],
      $_SERVER['REMOTE_ADDR'],
      date('Y-m-d H:i:s', $_SERVER['REQUEST_TIME']),
      date('Y-m-d H:i:s', $_SERVER['REQUEST_TIME'])
    ));
    
    if ($stmt === false)
    {
      throw new Exception("Couldn't log user request");
    }
  }
}