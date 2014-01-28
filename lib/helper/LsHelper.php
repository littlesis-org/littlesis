<?php

function entity_link($entity, $class='text_big', $strong = true, $link_text = null, $htmlOptions=array())
{
  if (!$link_text)
  {
    $link_text = $entity['name'];
  }

  if (!isset($htmlOptions['title']))
  {
    $htmlOptions['title'] = $entity['blurb'] ? $entity['blurb'] : 'view profile';
  }
  
  $link = link_to($link_text, EntityTable::generateRoute($entity), $htmlOptions);

  if ($strong)
  {
    $strongTag = $class ? '<strong class="' . $class . '">' : '<strong>';
    $link = $strongTag . $link . '</strong>';
  }
  else
  {
    $link = '<span class= "' . $class . '">' . $link . '</span>';
  }
  return $link;
}


function address_link(Address $address)
{
  return link_to($address->getOneLiner(), 'entity/address?id=' . $address->id);
}


function user_link($user, $app=null, $strong=true)
{
  //profile object needed to get internal url
  if (!$user['Profile'])
  {
    $user['Profile'] = LsDoctrineQuery::create()
      ->from('sfGuardUserProfile p')
      ->where('p.user_id = ?', $user['id'])
      ->setHydrationMode(Doctrine::HYDRATE_ARRAY)
      ->fetchOne();
  }

  $url = sfGuardUserTable::getInternalUrl($user);

  if ($app == 'frontend')
  {    
    $url = frontend_base() . '/' . $url;
  }
  
  if ($app == 'backend')
  {
    $url = backend_base() . '/' . $url;
  }

  $link =  link_to($user['Profile']['public_name'], $url);
  if ($strong)
  {
    $link = '<strong>' . $link . '</strong>';
  }
  return $link;
}


function user_link_by_public_name($name, $app=null, $strong=true)
{
  $url = '@userView?name=' . $name;

  if ($app == 'frontend')
  {    
    $url = frontend_base() . '/' . $url;
  }
  
  if ($app == 'backend')
  {
    $url = backend_base() . '/' . $url;
  }

  $link =  link_to($name, $url);

  if ($strong)
  {
    $link = '<strong>' . $link . '</strong>';
  }

  return $link;
}

function user_link_by_id($id, $app=null, $strong=true)
{
  $name = LsDoctrineQuery::create()
    ->select('public_name')
    ->from('sfGuardUserProfile')
    ->where('user_id = ?', $id)
    ->fetch(PDO::FETCH_COLUMN);

  return user_link_by_public_name($name);
}


function user_pic($user, $size='small', $htmlOptions=array())
{
  //profile object needed to get internal url
  if (!$user['Profile'])
  {
    $user['Profile'] = LsDoctrineQuery::create()
      ->from('sfGuardUserProfile p')
      ->where('p.user_id = ?', $user['id'])
      ->setHydrationMode(Doctrine::HYDRATE_ARRAY)
      ->fetchOne();
  }

  if ($fn = $user['Profile']['filename'])
  {
    $str = $size . DIRECTORY_SEPARATOR . $fn;
  }
  else
  {
    $str = 'system'.DIRECTORY_SEPARATOR.'user.png';
  }  

  $htmlOptions = array_merge(array('alt' => $user['Profile']['public_name'], 'style' => 'border: 0;'), $htmlOptions);
  $link = link_to(image_tag($str, $htmlOptions), sfGuardUserTable::getInternalUrl($user));

  return $link;
}


function list_link($list)
{
  $link = '<strong>';
  $admin = '';

  if ($list['is_admin'])
  {
    $admin = '*';
  } 

  $link .= link_to($admin . $list['name'], LsListTable::generateRoute($list)) . '</strong>';

  return $link;
}


function network_link($network)
{
  return link_to($network['name'], '@localView?name=' . $network['display_name']);
}


function reference_link($reference, $excerptLength=null)
{
  use_helper('LsText');

  $text = $excerptLength ? excerpt(ReferenceTable::getDisplayName($reference), $excerptLength) : ReferenceTable::getDisplayName($reference);

  if (stripos($reference['source'], 'http') === 0)
  {
    return link_to($text, $reference['source'], 'target=_blank');
  }
  else
  {
    return $text;
  }
}


function object_link(Doctrine_Record $object, $app=null, $text=null)
{  
  if (method_exists($object, 'getInternalUrl'))
  {
    $url = $object->getInternalUrl();

    if ($app == 'frontend')
    {    
      $url = frontend_base() . '/' . $url;
    }
    
    if ($app == 'backend')
    {
      $url = backend_base() . '/' . $url;
    }
    if (!$text)
    {
      $text = $object->getName();
    }
    return '<strong>' . link_to($text, $url) . '</strong>';
  }
  
  return null;
}


function frontend_base()
{
  return 'http://' . sfContext::getInstance()->getRequest()->getHost() . str_replace('frontend.php', '', str_replace('backend', 'frontend', sfContext::getInstance()->getRequest()->getScriptName()));
}


function backend_base()
{
  return 'http://' . sfContext::getInstance()->getRequest()->getHost() . str_replace('index.php', 'backend.php', str_replace('frontend', 'backend', sfContext::getInstance()->getRequest()->getScriptName()));
}


function list_entity_link($le)
{
  $link = '<strong>';
  $admin = '';
  if ($le->LsList->is_admin)
  {
    $admin = '*';
  }
  
  $link .= link_to($admin . $le->LsList->name, $le->LsList->getInternalUrl()) . '</strong>';

  if ($le->rank)
  {
    $link .= ' [#' . $le->rank . ']';
  }
  return $link;
}


function group_link($group)
{
  return link_to($group['display_name'], sfGuardGroupTable::getInternalUrl($group));
}

function rails_group_link($group)
{
  return link_to($group['name'], '/groups/' . $group['slug']);
}


function ls_image_tag($source, $options = array())
{
  if (sfConfig::get('app_amazon_enable_s3'))
  {
    $source = ImageTable::generateS3Url($source);
  }
  
  return image_tag($source, $options);
}


function category_link($category, $type=null)
{
  switch ($type)
  {
    case 'Person':
      $action = '@industryPeople';
      break;
    case 'Org':
      $action = '@industryOrgs';
      break;
    default:
      $action = '@categoryView';
  }

  return link_to($category['category_name'], $action . '?category=' . $category['category_id']);
}