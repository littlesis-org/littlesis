<?php

class LsDb
{
  static function getDbConnection()
  {
    return Doctrine_Manager::getInstance()->getConnection('main');
  }

  static function getRawDbConnection()
  {
    return Doctrine_Manager::getInstance()->getConnection('raw');
  }
}