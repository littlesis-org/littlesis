<?php

class LsApiRequestFilter extends sfFilter
{
  //authorization codes
  const AUTH_FAIL_KEY = 0;
  const AUTH_SUCCESS = 1;
  const AUTH_FAIL_LIMIT = 2;


  protected
    $db           = null,
    $request      = null,
    $response     = null;


  protected $protectedModules = array(
    'entity',
    'relationship',
    'list',
    'search',
    'batch'
  );


  public function initialize($context, $parameters = array())
  {
    parent::initialize($context, $parameters);

    $this->db           = Doctrine_Manager::connection();
    $this->request      = $context->getRequest();
    $this->response     = $context->getResponse();
  }


	public function execute($filterChain)
	{			
    self::escapeParameters();

    if (in_array($this->request->getParameter('module'), $this->protectedModules))
    {
      //if host is localhost, don't need to authenticate
      if ($this->request->getHost() != 'localhost')
      {
        $this->authenticateUserRequest();
        $this->logUserRequest();
      }
    }

    $filterChain->execute();
	}


  public function authenticateKey($key)
  {
    $sql = 'SELECT u.request_limit, COUNT(r.id) AS requests FROM api_user u LEFT JOIN api_request r ON r.api_key = u.api_key AND r.created_at > DATE_SUB(NOW(), INTERVAL 1 DAY) WHERE u.api_key = ? AND u.is_active = ? GROUP BY u.id';
    $stmt = $this->db->execute($sql, array($key, true));
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


  public function authenticateUserRequest()
  {
    $authResult = $this->authenticateKey($this->request->getParameter('_key'));

    switch ($authResult)
    {
      case self::AUTH_FAIL_KEY:
        $this->response->setStatusCode(401);
        sfLoader::loadHelpers('Partial');
        $partial = get_partial('global/401');
        $this->response->setContent($partial);
        $this->response->setHttpHeader('WWW-Authenticate', 'Your request must include a query parameter named "_key" with a valid API key value. To obtain an API key, visit http://api.littlesis.org/register');
        $this->response->sendHttpHeaders();
        $this->response->sendContent();
        throw new sfStopException();
        break;
      case self::AUTH_FAIL_LIMIT:
        $this->response = sfContext::getInstance()->getResponse();
        $this->response->setStatusCode(403);
        $user = Doctrine::getTable('ApiUser')->findOneByApiKey($this->request->getParameter('_key'));
        sfLoader::loadHelpers('Partial');
        $partial = get_partial('global/403', array('request_limit' => $user->request_limit));
        $this->response->setContent($partial);
        $this->response->sendHttpHeaders();
        $this->response->sendContent();
        throw new sfStopException();
        break;
      case self::AUTH_SUCCESS:
        break;
      default:
        throw new Exception("Invalid return value from LsApi::autheticate()");
    }    
  }
  
  
  public function logUserRequest()
  {
    $sql = 'INSERT INTO api_request (api_key, resource, ip_address, created_at, updated_at) VALUES (?, ?, ?, ?, ?)';

    $params = array();
    
    foreach (explode('&', $_SERVER['QUERY_STRING']) as $pair)
    {
      list($key, $value) = explode('=', $pair);
      
      if ($key != '_key')
      {
        $params[$key] = $value;
      }
    }
    
    $path = $this->request->getPathInfo();
    $paramStr = ((count($params) && strpos($path, '?') === false) ? '?' : '') . http_build_query($params);
        
    $stmt = $this->db->execute($sql, array(
      $this->request->getParameter('_key'),
      $path . http_build_query($params),
      $_SERVER['REMOTE_ADDR'],
      date('Y-m-d H:i:s', $_SERVER['REQUEST_TIME']),
      date('Y-m-d H:i:s', $_SERVER['REQUEST_TIME'])
    ));
    
    if ($stmt === false)
    {
      throw new Exception("Couldn't log user request");
    }
  }
  
  
  static function escapeParameters()
  {
    $request = sfContext::getInstance()->getRequest();    
    $params = $request->getParameterHolder()->getAll();

    foreach ($params as $key => $value)
    {
      $request->setParameter($key, self::escapeParameter($key, $value));
    }
  }
  
  
  static function escapeParameter($key, $value)
  {
    $vulnerableKeys = array(
      'cat_ids', 'cat1_ids', 'cat2_ids', 'ids', 'order', 'order1', 'order2', 'num', 'page'
    );

    if (in_array($key, $vulnerableKeys))
    {
      $ary = (array) $value;

      foreach ($ary as $val)
      {    
        if (!preg_match('#^[\d,]+$#', $val))
        {
          return null;
        }
      }
    }

    return $value;
  }
}

?>