<?php

class Trimmable extends Doctrine_Template
{
  protected $_options = array(
    'fields' =>  array('name')
  );

  public function __construct(array $options = array())
  {
    $this->_options = Doctrine_Lib::arrayDeepMerge($this->_options, $options);
  }

  public function getOption($name)
  {
    return $this->_options[$name];
  }

  public function setUp()
  {
    $this->addListener(new TrimmableListener($this->_options));
  }
}