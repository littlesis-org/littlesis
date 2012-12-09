<?php 

class LsSphinxClient extends SphinxClient
{
  var $_page = null;
  var $_total = null;
  

  static function cleanQuery($query)
  {
    //remove single not operator
    if (strpos($query, '-') === 0 && str_word_count($query) < 2)
    {
      $query = '';
    }

    $query = preg_replace('#(?<! )-&#', '\-', $query);
    $query = preg_replace('#(?<!\\\)/#', '\/', $query);

    return $query;
  }

  static function cleanForQuery($str)
  {
    $str = preg_replace('#[\(\)\[\]@|*"~/<=\$\^:,]#', '', $str);
    return $str;
  }

  public function buildEntityQuery($terms, $aliases=true, $primary_ext=null)
  {
    $terms = self::cleanQuery($terms);    
    $terms = $this->EscapeString($terms);
    $ext = $primary_ext ? " @primary_ext " . $primary_ext : "";
    $fields = "@(name" . ($aliases ? ",aliases" : "") . ") ";

    return $fields . $terms . $ext;
  }
  
  
  public function __construct($page=1, $num=20)
  {
    parent::SphinxClient();

    $this->_page = $page;
    
    $this->SetServer('localhost', 3312);
    $this->SetMatchMode(SPH_MATCH_EXTENDED);
    $this->SetLimits(($page - 1) * $num, $num);        
  }
  
  
  function Query($query, $index="*", $comment="")
  {
    $result = parent::Query($query, $index, $comment);
    
    $this->_total = $result['total_found'];
    
    return $result;
  }
}