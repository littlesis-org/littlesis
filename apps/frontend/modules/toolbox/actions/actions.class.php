<?php

/**
 * toolbox actions.
 *
 * @package    ls
 * @subpackage toolbox
 * @author     Your name here
 * @version    SVN: $Id: actions.class.php 9301 2008-05-27 01:08:46Z dwhittle $
 */
class toolboxActions extends sfActions
{
 /**
  * Executes index action
  *
  * @param sfRequest $request A request object
  */
  public function executeIndex($request)
  {
    
  }
  
  //parse NYS data table
  public function executeParseNyDonations($request)
  {
    $this->reference_form = new ReferenceForm;
    if ($request->isMethod('post'))
    {
      $refParams = $request->getParameter('reference');
      $url = $refParams['source'];
      $this->reference_form->bind($refParams);
      
      $commit = $request->getParameter('commit');
      
      if (preg_match('/^http...www.elections.state.ny.us.8080.plsql_browser.*$/is',$url,$match))
      {
        $browser = new sfWebBrowser();
        if (!$browser->get($url)->responseIsError())
        {
          $text = $browser->getResponseText();
          $text = LsHtml::parseNyDonations($text);
          $this->parsed_text = $text;
        }
      }
    }
    
  }
  
  public function executeOnePercentSearch($request)
  {
    $this->search_form = new OnePercentSearchForm;
    if ($request->isMethod('post'))
    {
      $searchParams = $request->getParameter('onepercent');
      
      $url = $searchParams['url'];
      $text = $searchParams['text'];
      $this->search_form->bind($searchParams);
      if (!$this->search_form->isValid()) return;
      if ($url)
      {
        $browser = new sfWebBrowser();
        if (!$browser->get($url)->responseIsError())
        {
          $text = $browser->getResponseText();
        }
      }      
      if ($text)
      {
        $html = stripos($text,"<html");
        $entity_types = 'people';
        $names = array();
        if ($html !== false)
        {
          $names = LsTextAnalysis::getHtmlEntityNames($text,$entity_types);
        }
        else
        {
          $names = LsTextAnalysis::getTextEntityNames($text,$entity_types);
        }
        $this->matches = array();
        foreach($names as $name)
        {
          $name_terms = $name;
          $name_parts = preg_split('/\s+/',$name);
          if (count($name_parts) > 1)
          {
            $name_terms = PersonTable::nameSearch($name);
          }
          $terms = '@(name,aliases) ' . $name_terms . ' @primary_ext Person';  
          $pager = EntityTable::getSphinxPager($terms, 1, 20); 
          $match['name'] = $name;
          $match['search_results'] = $pager->execute();
          $this->matches[] = $match;
        }     
      }
    }
    
  }
  
}
