<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfWidgetFormSelectRadio represents radio HTML tags.
 *
 * @package    symfony
 * @subpackage widget
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id: sfWidgetFormSelectRadio.class.php 11541 2008-09-14 16:31:57Z fabien $
 */
class LsWidgetFormSelectRadio extends sfWidgetFormSelectRadio
{
  /**
   * Constructor.
   *
   * Available options:
   *
   *  * choices:         An array of possible choices (required)
   *  * label_separator: The separator to use between the input radio and the label
   *  * separator:       The separator to use between each input radio
   *  * formatter:       A callable to call to format the radio choices
   *                     The formatter callable receives the widget and the array of inputs as arguments
   *
   * @param array $options     An array of options
   * @param array $attributes  An array of default HTML attributes
   *   
   * @see sfWidgetForm
   */
  protected function configure($options = array(), $attributes = array())
  {
    parent::configure($options, $attributes);

    $this->addOption('choices', array());
    $this->addOption('is_ternary', false);
    $this->setOption('separator', '&nbsp;');
    $this->setOption('label_separator', '');
    $this->setOption('formatter', array($this, 'ternaryFormat'));

    if (isset($options['is_ternary']) && $options['is_ternary'] == true)
    {
      $this->setOption('choices', array('1' => 'yes', '0' => 'no', '' => 'unknown'));
    }
  }


  public function formatter($widget, $inputs)
  {
    $rows = array();
    foreach ($inputs as $input)
    {
      $rows[] = $this->renderContentTag('li', $input['input'].$this->getOption('label_separator').$input['label']);
    }

    return $this->renderContentTag('ul', implode($this->getOption('separator'), $rows), array('class' => 'radio_list'));
  }

  
  public function ternaryFormat($widget, $inputs)
  {
    $rows = array();
    
    foreach ($inputs as $input)
    {
      $rows[] = $input['input'].$this->getOption('label_separator').$input['label'];
    }

    return implode($this->getOption('separator'), $rows);
  }


  /**
   * Prepares an attribute key and value for HTML representation.
   *
   * @param  string $k  The attribute key
   * @param  string $v  The attribute value
   *
   * @return string The HTML representation of the HTML key attribute pair.
   */
  protected function attributesToHtmlCallback($k, $v)
  {
    return is_null($v) || ('' === $v && $k != 'value') ? '' : sprintf(' %s="%s"', $k, $this->escapeOnce($v));
  }
  
}
