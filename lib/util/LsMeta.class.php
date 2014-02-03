<?php

class LsMeta
{
  static function generateHeader()
  {
    $context = sfContext::getInstance();
    $response = $context->getResponse();
    $request = $context->getRequest();
    $slots = $response->getSlots();
    $module = $request->getParameter('module');
    $action = $request->getParameter('action');
    $page = null;
    $header = isset($slots['header_text']) ? $slots['header_text'] : null;

    $entityPages = array(
      'giving' => 'campaign contributions',
      'family' => 'family',
      'friends' => 'friends & colleagues',
      'government' => 'government positions',
      'business' => 'business positions',
      'officeOf' => 'bosses',
      'office' => 'office/staff',
      'otherPositions' => 'other positions',
      'education' => 'education',
      'transactions' => 'transactions',
      'holdings' => 'holdings',
      'fundraising' => 'fundraising committees',
      'donors' => 'donors',
      'recipients' => 'donation recipients',
      'lobbyingTargets' => 'lobbying targets',
      'leadership' => 'leadership',
      'members' => 'members',
      'memberships' => 'memberships',
      'owners' => 'owners',
      'students' => 'students',
      'childOrgs' => 'child orgs'
    );

    $userPages = array(
      'notes' => '- notes',
      'groups' => '- research groups',
      'modifications' => '- edits'
    );
    
    if ($module == 'entity')
    {
      if ($page = @$entityPages[$action])
      {
        $detail = $page;
      }
      else
      {
        $detail = "power network";
      }

      $header = rtrim($header, " .") . "'s " . $detail;
      $response->setSlot('share_text', $header);
    }

    if ($module == 'user' || $module == 'home')
    {
      if ($page = @$userPages[$action])
      {
        $header = $header . ' ' . $page;
      }
    }

    if ($module == 'user' && $action == 'note')
    {
      $header = 'Note by ' . $header;
    }
    
    return $header;
  }

  static function generateTitle()
  {
    $context = sfContext::getInstance();
    $response = $context->getResponse();
    $request = $context->getRequest();
    $title = $response->getTitle();  
    $module = $request->getParameter('module');
    $action = $request->getParameter('action');
    
    if ($module == 'local')
    {
      return $title . ' - LittleSis';
    }
    
    $header = self::generateHeader();
    
    if ($header)
    {
      return $header . ' - ' . $title;
    }
    else
    {
      return $title . ' - Profiling the powers that be';
    }
  }
}