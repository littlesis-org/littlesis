<?php 

function pager_meta($pager, Array $sort=null)
{
  //pager has to be executed to proceed
  if (!$pager->getExecuted())
  {
    $pager->execute();
  }
  
  
  if ($pager->getNumResults() == 0)
  {
    return "Nothing found.";
  }


  //results info
  $ret = $pager->getFirstIndice() . "-" . $pager->getLastIndice() . " of " . $pager->getNumResults();   


  if ($pager->haveToPaginate())
  {
    //navigation links
    $ret .= " :: ";

    //range of pages should be five wide centered, if possible, on the current page
    $pager_range = $pager->getRange(
      'Sliding',
      array('chunk' => 5)
    );
  
  
     //create array of text and pages
    $nav_links = array();
    $nav_links['prev'] = $pager->getPreviousPage();

    foreach ($pager_range->rangeAroundPage() as $page)
    {
      $nav_links[$page] = $page;
    }
    
    $nav_links['next'] = $pager->getNextPage();


    //loop though pages, creating real links
    foreach ($nav_links as $text => $page)
    {
      //if page is null, we skip it
      if (!$page)
      {
        continue;
      }
      
      
      //set page number in request
      $request = sfContext::getInstance()->getRequest();
      $holder = $request->getParameterHolder();
      $holder->set('page', $page);


      //remove null params and clean double '??' (for some reason this appears on some installations)
      $params = array_filter($holder->getAll(), array('LsLogic', 'isNotNull'));
      unset($params['module'], $params['action']);
      $module = $request->getParameter('module');
      $action = $request->getParameter('action');
      $path = $module . '/' . $action;
      $queryString = http_build_query($params);


      $uri = $request->getUri();
      $nonPageParams = $params;
      unset($nonPageParams['id'], $nonPageParams['slug']);


      //for caching purposes, use path URL if entity module and no non-page params
      if ($module == 'entity' && count($nonPageParams) == 1)
      {
        $extra = ($page == 1) ? '' : '/page/' . $page;
  
        if (preg_match('#/page/\d+#', $uri))
        {
          $uri = preg_replace('#/page/\d+#', $extra, $uri);
        }
        else
        {
          if (strstr($uri, '?'))
          {
            $uri = str_replace('?', $extra . '?', $uri);
          }
          else
          {
            $uri .= $extra;
          }
          
          $uri = preg_replace('#(?<!http:)/{2,}#', '/', $uri);
        }
      }
      else
      {
        if (preg_match('/page=\d+/', $uri))
        {
          $uri = preg_replace('/page=\d+/', 'page=' . $page, $uri);
        }
        else
        {
          if (strstr($uri, '?'))
          {
            $uri .= '&page=' . $page;
          }
          else
          {
            $uri .= '?page=' . $page;        
          }        
        }
      }

      //sometimes pager links need to use AJAX
      if (method_exists($pager, 'isAjax') && $pager->isAjax())
      {
        $options = array(
          'update' => $pager->getAjaxUpdateId(),
          'url' => $path . '?' . $queryString,
          'method' => 'get'
        );
        
        //if a loading graphic id is given, use it
        if ($indicatorId = $pager->getAjaxIndicatorId())
        {
          $options['loading'] = "document.getElementById('" . $indicatorId . "').style.display = 'block';";
          $options['complete'] = "document.getElementById('" . $indicatorId . "').style.display = 'none';";
        }

        //hrefs are used to make the links RESTful
        if ($hash = $pager->getAjaxHash())
        {
          $href = '#' . $hash . '|page:' . $page;
        }
        else
        {
          $href = '#page:' . $page;
        }
                
        use_helper('LsJavascript');
        $link = ls_link_to_remote(
          $text, 
          $options,
          array('href' => $href, 'return' => true, 'query_string' => $queryString)
        );
      }
      else
      {
        $link = sprintf('<a href="%s">%s</a>', $uri, $text);
      }


      if ($pager->getPage() == $page)
      {
        if (is_int($text) || is_float($text))
        {
          //bold link for current page
          $ret .= '<span class="pager_meta_current">' . $link . '</span> ';
        }
        else
        {
          //no link for 'Previous' or 'Next' if on first or last page
          $ret .= $text . ' ';
        }
      }
      else
      {
        $ret .= $link . ' ';
      }
    }
  }
  
  $ret .= ' ';

  if ($sort && count($sort['values']))
  {  
    $ret .= ' :: sort by ';
    $sortLinks = array();

    $request = sfContext::getInstance()->getRequest();
    $holder = $request->getParameterHolder();
    $holder = clone $holder;
    $current = $request->getParameter('sort', $sort['default']);

    foreach ($sort['values'] as $name => $value)
    {
      if ($value != $current)
      {
        $holder->set('sort', $value);
  
        $params = array_filter($holder->getAll(), array('LsLogic', 'isNotNull'));
        $path = $request->getPathInfo();
        $queryString = http_build_query($params);

        $sortLinks[] = sprintf('<a href="%s">%s</a>', $uri, $text);
      }
    }
    
    $ret .= implode(' ', $sortLinks);
  }

  
  return $ret;
}


function pager_meta_sample($pager, $path, $sort=null)
{
  if (!$pager->getExecuted())
  {
    $pager->execute();
  }


  if ($pager->getNumResults() == 0)
  {
    return "Nothing found.";
  }


  $ret = $pager->getFirstIndice() . "-" . $pager->getLastIndice() . " of " . $pager->getNumResults();   

  if ($pager->getLastIndice() < $pager->getNumResults())
  {
    $ret .= " :: ";

    //sometimes pager links need to use AJAX
    if ($pager->isAjax())
    {
      $options = array(
        'update' => $pager->getAjaxUpdateId(),
        'url' => $path,
        'method' => 'get'
      );


      //if a loading graphic id is given, use it
      if ($indicatorId = $pager->getAjaxIndicatorId())
      {
        $options['loading'] = "document.getElementById('" . $indicatorId . "').style.display = 'block';";
        $options['complete'] = "document.getElementById('" . $indicatorId . "').style.display = 'none';";
      }

      //hrefs are used to make the links RESTful
      if ($hash = $pager->getAjaxHash())
      {
        $htmlOptions = array(
          'return' => true, 
          'href' => $href = '#' . $hash
        );
      }
      else
      {
        $htmlOptions = array('return' => true);
      }

      
      use_helper('LsJavascript');
      $link = ls_link_to_remote(
        'see all', 
        $options,
        $htmlOptions
      );
    }
    else
    {
      $link = link_to('see all', $path);
    }

    $ret .= $link;
  }

  if ($sort && count($sort['values']))
  {  
    $ret .= ' :: sort by ';
    $sortLinks = array();

    $request = sfContext::getInstance()->getRequest();
    $holder = $request->getParameterHolder();
    $holder = clone $holder;
    $current = $request->getParameter('sort', $sort['default']);

    foreach ($sort['values'] as $name => $value)
    {
      if ($value != $current)
      {
        $holder->set('sort', $value);

        $params = array_filter($holder->getAll(), array('LsLogic', 'isNotNull'));
        $path = $request->getPathInfo();
        $queryString = http_build_query($params);

        $sortLinks[] = link_to($name, $path, array('query_string' => $queryString));
      }
    }
    
    $ret .= implode(' ', $sortLinks);
  }

  return $ret;
}


function pager_noresults(Doctrine_Pager $pager, $strong=true)
{
	$ret = '';

	if ($pager->getNumResults() == 0)
	{
    $nothing =  '<span class="pager_noresults">Nothing found.</span>';

		if ($strong)
		{
			$ret = '<strong>' . $nothing . '</strong>';
		}
		else
		{
		  $ret = $nothing;
		}
	}
	
	return $ret;
}