<?php

/**
 * Entity form base class.
 *
 * @package    form
 * @subpackage entity
 * @version    SVN: $Id: sfDoctrineFormGeneratedTemplate.php 8508 2008-04-17 17:39:15Z fabien $
 */
class BaseEntityForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'id'                         => new sfWidgetFormInputHidden(),
      'name'                       => new sfWidgetFormInput(),
      'blurb'                      => new sfWidgetFormInput(),
      'summary'                    => new sfWidgetFormTextarea(),
      'notes'                      => new sfWidgetFormTextarea(),
      'website'                    => new sfWidgetFormInput(),
      'parent_id'                  => new sfWidgetFormDoctrineSelect(array('model' => 'Entity', 'add_empty' => true)),
      'primary_ext'                => new sfWidgetFormInput(),
      'merged_id'                  => new sfWidgetFormInput(),
      'created_at'                 => new sfWidgetFormDateTime(),
      'updated_at'                 => new sfWidgetFormDateTime(),
      'start_date'                 => new sfWidgetFormInput(),
      'end_date'                   => new sfWidgetFormInput(),
      'is_current'                 => new sfWidgetFormInputCheckbox(),
      'last_user_id'               => new sfWidgetFormDoctrineSelect(array('model' => 'sfGuardUser', 'add_empty' => true)),
      'is_deleted'                 => new sfWidgetFormInputCheckbox(),
      'ls_list_list'        => new sfWidgetFormDoctrineSelectMany(array('model' => 'LsList')),
      'industry_list'     => new sfWidgetFormDoctrineSelectMany(array('model' => 'Industry')),
      'lobby_filing_list' => new sfWidgetFormDoctrineSelectMany(array('model' => 'LobbyFiling')),
    ));

    $this->setValidators(array(
      'id'                         => new sfValidatorDoctrineChoice(array('model' => 'Entity', 'column' => 'id', 'required' => false)),
      'name'                       => new sfValidatorString(array('max_length' => 200)),
      'blurb'                      => new sfValidatorString(array('max_length' => 200)),
      'summary'                    => new sfValidatorString(),
      'notes'                      => new sfValidatorString(),
      'website'                    => new sfValidatorString(array('max_length' => 100, 'required' => false)),
      'parent_id'                  => new sfValidatorDoctrineChoice(array('model' => 'Entity', 'required' => false)),
      'primary_ext'                => new sfValidatorString(array('max_length' => 50)),
      'merged_id'                  => new sfValidatorInteger(array('required' => false)),
      'created_at'                 => new sfValidatorDateTime(array('required' => false)),
      'updated_at'                 => new sfValidatorDateTime(array('required' => false)),
      'start_date'                 => new sfValidatorString(array('max_length' => 10, 'required' => false)),
      'end_date'                   => new sfValidatorString(array('max_length' => 10, 'required' => false)),
      'is_current'                 => new sfValidatorBoolean(array('required' => false)),
      'last_user_id'               => new sfValidatorDoctrineChoice(array('model' => 'sfGuardUser', 'required' => false)),
      'is_deleted'                 => new sfValidatorBoolean(),
      'ls_list_list'        => new sfValidatorDoctrineChoiceMany(array('model' => 'LsList', 'required' => false)),
      'industry_list'     => new sfValidatorDoctrineChoiceMany(array('model' => 'Industry', 'required' => false)),
      'lobby_filing_list' => new sfValidatorDoctrineChoiceMany(array('model' => 'LobbyFiling', 'required' => false)),
    ));

    $this->widgetSchema->setNameFormat('entity[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    parent::setup();
  }

  public function getModelName()
  {
    return 'Entity';
  }

  public function updateDefaultsFromObject()
  {
    parent::updateDefaultsFromObject();

    if (isset($this->widgetSchema['ls_list_list']))
    {
      $values = array();
      foreach ($this->object->LsList as $obj)
      {
        $values[] = current($obj->identifier());
      }
      $this->object->clearRelated('LsList');
      $this->setDefault('ls_list_list', $values);
    }

    if (isset($this->widgetSchema['industry_list']))
    {
      $values = array();
      foreach ($this->object->Industry as $obj)
      {
        $values[] = current($obj->identifier());
      }
      $this->object->clearRelated('Industry');
      $this->setDefault('industry_list', $values);
    }

    if (isset($this->widgetSchema['lobby_filing_list']))
    {
      $values = array();
      foreach ($this->object->LobbyFiling as $obj)
      {
        $values[] = current($obj->identifier());
      }
      $this->object->clearRelated('LobbyFiling');
      $this->setDefault('lobby_filing_list', $values);
    }

  }

  protected function doSave($con = null)
  {
    parent::doSave($con);

    $this->saveLsListList($con);
    $this->saveIndustryList($con);
    $this->saveLobbyFilingList($con);
  }

  public function saveLsListList($con = null)
  {
    if (!$this->isValid())
    {
      throw $this->getErrorSchema();
    }

    if (!isset($this->widgetSchema['ls_list_list']))
    {
      // somebody has unset this widget
      return;
    }

    if (is_null($con))
    {
      $con = $this->getConnection();
    }

    $q = Doctrine_Query::create()
          ->delete()
          ->from('LsListEntity r')
          ->where('r.entity_id = ?', current($this->object->identifier()))
          ->execute();

    $values = $this->getValue('ls_list_list');
    if (is_array($values))
    {
      foreach ($values as $value)
      {
        $obj = new LsListEntity();
        $obj->entity_id = current($this->object->identifier());
        $obj->list_id = $value;
        $obj->save();
      }
    }
  }

  public function saveIndustryList($con = null)
  {
    if (!$this->isValid())
    {
      throw $this->getErrorSchema();
    }

    if (!isset($this->widgetSchema['industry_list']))
    {
      // somebody has unset this widget
      return;
    }

    if (is_null($con))
    {
      $con = $this->getConnection();
    }

    $q = Doctrine_Query::create()
          ->delete()
          ->from('BusinessIndustry r')
          ->where('r.business_id = ?', current($this->object->identifier()))
          ->execute();

    $values = $this->getValue('industry_list');
    if (is_array($values))
    {
      foreach ($values as $value)
      {
        $obj = new BusinessIndustry();
        $obj->business_id = current($this->object->identifier());
        $obj->industry_id = $value;
        $obj->save();
      }
    }
  }

  public function saveLobbyFilingList($con = null)
  {
    if (!$this->isValid())
    {
      throw $this->getErrorSchema();
    }

    if (!isset($this->widgetSchema['lobby_filing_list']))
    {
      // somebody has unset this widget
      return;
    }

    if (is_null($con))
    {
      $con = $this->getConnection();
    }

    $q = Doctrine_Query::create()
          ->delete()
          ->from('LobbyFilingLobbyist r')
          ->where('r.lobbyist_id = ?', current($this->object->identifier()))
          ->execute();

    $values = $this->getValue('lobby_filing_list');
    if (is_array($values))
    {
      foreach ($values as $value)
      {
        $obj = new LobbyFilingLobbyist();
        $obj->lobbyist_id = current($this->object->identifier());
        $obj->lobby_filing_id = $value;
        $obj->save();
      }
    }
  }

}