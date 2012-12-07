<?php

/**
 * LsList form base class.
 *
 * @package    form
 * @subpackage ls_list
 * @version    SVN: $Id: sfDoctrineFormGeneratedTemplate.php 8508 2008-04-17 17:39:15Z fabien $
 */
class BaseLsListForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'id'                  => new sfWidgetFormInputHidden(),
      'name'                => new sfWidgetFormInput(),
      'description'         => new sfWidgetFormTextarea(),
      'is_ranked'           => new sfWidgetFormInputCheckbox(),
      'is_admin'            => new sfWidgetFormInputCheckbox(),
      'is_featured'         => new sfWidgetFormInputCheckbox(),
      'is_network'          => new sfWidgetFormInputCheckbox(),
      'display_name'        => new sfWidgetFormInput(),
      'featured_list_id'    => new sfWidgetFormDoctrineSelect(array('model' => 'LsList', 'add_empty' => true)),
      'created_at'          => new sfWidgetFormDateTime(),
      'updated_at'          => new sfWidgetFormDateTime(),
      'last_user_id'        => new sfWidgetFormDoctrineSelect(array('model' => 'sfGuardUser', 'add_empty' => true)),
      'is_deleted'          => new sfWidgetFormInputCheckbox(),
      'entity_list' => new sfWidgetFormDoctrineSelectMany(array('model' => 'Entity')),
    ));

    $this->setValidators(array(
      'id'                  => new sfValidatorDoctrineChoice(array('model' => 'LsList', 'column' => 'id', 'required' => false)),
      'name'                => new sfValidatorString(array('max_length' => 100)),
      'description'         => new sfValidatorString(array('required' => false)),
      'is_ranked'           => new sfValidatorBoolean(),
      'is_admin'            => new sfValidatorBoolean(),
      'is_featured'         => new sfValidatorBoolean(),
      'is_network'          => new sfValidatorBoolean(),
      'display_name'        => new sfValidatorString(array('max_length' => 50, 'required' => false)),
      'featured_list_id'    => new sfValidatorDoctrineChoice(array('model' => 'LsList', 'required' => false)),
      'created_at'          => new sfValidatorDateTime(array('required' => false)),
      'updated_at'          => new sfValidatorDateTime(array('required' => false)),
      'last_user_id'        => new sfValidatorDoctrineChoice(array('model' => 'sfGuardUser', 'required' => false)),
      'is_deleted'          => new sfValidatorBoolean(),
      'entity_list' => new sfValidatorDoctrineChoiceMany(array('model' => 'Entity', 'required' => false)),
    ));

    $this->widgetSchema->setNameFormat('ls_list[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    parent::setup();
  }

  public function getModelName()
  {
    return 'LsList';
  }

  public function updateDefaultsFromObject()
  {
    parent::updateDefaultsFromObject();

    if (isset($this->widgetSchema['entity_list']))
    {
      $values = array();
      foreach ($this->object->Entity as $obj)
      {
        $values[] = current($obj->identifier());
      }
      $this->object->clearRelated('Entity');
      $this->setDefault('entity_list', $values);
    }

  }

  protected function doSave($con = null)
  {
    parent::doSave($con);

    $this->saveEntityList($con);
  }

  public function saveEntityList($con = null)
  {
    if (!$this->isValid())
    {
      throw $this->getErrorSchema();
    }

    if (!isset($this->widgetSchema['entity_list']))
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
          ->where('r.list_id = ?', current($this->object->identifier()))
          ->execute();

    $values = $this->getValue('entity_list');
    if (is_array($values))
    {
      foreach ($values as $value)
      {
        $obj = new LsListEntity();
        $obj->list_id = current($this->object->identifier());
        $obj->entity_id = $value;
        $obj->save();
      }
    }
  }

}