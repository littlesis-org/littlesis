<?php

use_helper('Javascript');

/**
 * Returns a link that will trigger a javascript function using the
 * onclick handler and return TRUE OR FALSE after the fact 
 * depending on the $html_options['return'] parameter. 
 * This attribute is removed before we use the default content_tag helper.
 *
 * Examples:
 *   <?php echo link_to_function('Greeting', "alert('Hello world!')", array('return'=>true)) ?>
 *   <?php echo link_to_function(image_tag('delete'), "if confirm('Really?'){ do_delete(); }") ?>
 */
function ls_link_to_function($name, $function, $html_options = array())
{
  $html_options = _parse_attributes($html_options);
 
  $html_options['href'] = isset($html_options['href']) ? $html_options['href'] : '#';
  $html_options['onclick'] = $function.'; return ';
  $html_options['onclick'] .= (isset($html_options['return']) && $html_options['return'] == true) ? 'true;' : 'false;';
  unset($html_options['return']);
 
  return content_tag('a', $name, $html_options);
}
 
/**
 * See docs on link_to_remote helper for more info.
 */
function ls_link_to_remote($name, $options = array(), $html_options = array())
{
  return ls_link_to_function($name, ls_remote_function($options), $html_options);
}


/**
 * Returns the javascript needed for a remote function.
 * Takes the same arguments as 'link_to_remote()'.
 *
 * Example:
 *   <select id="options" onchange="<?php echo remote_function(array('update' => 'options', 'url' => '@update_options')) ?>">
 *     <option value="0">Hello</option>
 *     <option value="1">World</option>
 *   </select>
 */
function ls_remote_function($options)
{
  sfContext::getInstance()->getResponse()->addJavascript(sfConfig::get('sf_prototype_web_dir').'/js/prototype');

  $javascript_options = _options_for_ajax($options);

  $update = '';
  if (isset($options['update']) && is_array($options['update']))
  {
    $update = array();
    if (isset($options['update']['success']))
    {
      $update[] = "success:'".$options['update']['success']."'";
    }
    if (isset($options['update']['failure']))
    {
      $update[] = "failure:'".$options['update']['failure']."'";
    }
    $update = '{'.join(',', $update).'}';
  }
  else if (isset($options['update']))
  {
    $update .= "'".$options['update']."'";
  }

  $function = !$update ?  "new Ajax.Request(" : "new Ajax.Updater($update, ";
  $function .= '\''.url_for($options['url']).'\'';


  //ADDED TO ALLOW FOR APPENDING HASHES TO URLS FOR AJAX
  if (isset($options['posturl']))
  {
    $function .= ' + ' . $options['posturl'];
  }


  $function .= ', '.$javascript_options.')';

  if (isset($options['before']))
  {
    $function = $options['before'].'; '.$function;
  }
  if (isset($options['after']))
  {
    $function = $function.'; '.$options['after'];
  }
  if (isset($options['condition']))
  {
    $function = 'if ('.$options['condition'].') { '.$function.'; }';
  }
  if (isset($options['confirm']))
  {
    $function = "if (confirm('".escape_javascript($options['confirm'])."')) { $function; }";
    if (isset($options['cancel']))
    {
      $function = $function.' else { '.$options['cancel'].' }';
    }
  }

  return $function.';';
}
 
?>