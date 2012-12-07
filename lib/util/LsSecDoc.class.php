<?php

class LsSecDoc
{
  public $text = null;
  public $pages = null;
  
  function __construct($text)
  {
    $text = LsHtml::replaceEntities($text);
    $text = LsString::utf8TransUnaccent($text);
    $this->text = $text;
  }
  
  //finds positions of all page numbers.  $this->pages is actually array of page numbers and positions
  public function paginateWithNumbers()
	{
	  $res = array();
	  $res[] = array('/<[^>]*>\s*(((\p{L}){0,2}\-)?(\d{1,2}|[ivx]{1,3}))\s*(<[^>]*>[^<]*){0,8}<[^>]*(?<=page-break-before)/isu',1);
	  $res[] = array('/(((\p{L}){0,2}\-)?(\d{1,2}|[ivx]{1,3}))\s*(<[^>]*>[^<]*){0,8}<PAGE>/isu',1);

    $res[] = array('/page\-break\-before\:[^>]*>\s*(<[^>]*>\s*){0,10}(table\s+of\s+contents\s*)?(<[^>]*>\s*){0,10}(((\p{L}){0,2}\-)?(\d{1,2}|[ivx]{1,3}))/isu',5);
    $res[] = array('/page\-break\-after\:[^>]*>\s*(<[^>]*>\s*){0,10}(table\s+of\s+contents\s*)?(<[^>]*>\s*){0,10}(\-\s*)?(((\p{L}){0,2}\-)?(\d{1,2}|[ivx]{1,3}))/isu',6);
	  foreach ($res as $re)
	  {
	    $matched = preg_match_all($re[0],$this->text,$matches,PREG_SET_ORDER | PREG_OFFSET_CAPTURE);
	    $pages = array();
	    if ($matched > 5) 
	    {
	      foreach ($matches as $match)
	      {
	        $pages[] = array($match[$re[1]][0],$match[$re[1]][1]);
	      }
	      array_unshift($pages,array('0',0));
	      $this->pages = $pages;
        break;
	    }
    }
    var_dump($pages); 
	}
	
	//takes position in string of character, returns page index and number
	private function getPageNumber($x)
	{
	  $ret = null;
	  for($i = 1; $i < count($this->pages); $i++)
	  {
	    if ($x < $this->pages[$i] && $x > $this->pages[$i-1])
	    {
	      $ret = array($i, $this->pages[$i]);
        break;
	    }	  
	  }
	  return $ret;
	}
	
	public function paginate()
	{
	  $re = '/page.break/is';
	  $matched = preg_match_all($re,$this->text,$matches,PREG_SET_ORDER | PREG_OFFSET_CAPTURE);
	  $pages = array();
	  if ($matched)
	  {
      foreach ($matches as $match)
      {
        $pages[] = $match[0][1];
      }
      $this->pages = $pages;
    }  
	}
	
	public function findPageMatches($re)
	{
	  $matched = preg_match_all($re,$this->text,$matches,PREG_SET_ORDER | PREG_OFFSET_CAPTURE);
	  if ($matched)
	  {
	    foreach($matches as $match)
	    {
	      $pages[] = $this->getPageNumber($match[0][1]);
	    } 
	  }
	  var_dump($pages);
	  return $pages;
	}
	

}
