<?php 

class LsValidatorString extends sfValidatorString
{
  protected function configure($options = array(), $messages = array())
  {
    parent::configure($options, $messages);

    $this->setOption('empty_value', null);
  }
}