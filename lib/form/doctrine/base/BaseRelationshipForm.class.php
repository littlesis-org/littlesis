<?php

/**
 * Relationship form base class.
 *
 * @package    form
 * @subpackage relationship
 * @version    SVN: $Id: sfDoctrineFormGeneratedTemplate.php 8508 2008-04-17 17:39:15Z fabien $
 */
class BaseRelationshipForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'id'                             => new sfWidgetFormInputHidden(),
      'entity1_id'                     => new sfWidgetFormDoctrineSelect(array('model' => 'Entity', 'add_empty' => false)),
      'entity2_id'                     => new sfWidgetFormDoctrineSelect(array('model' => 'Entity', 'add_empty' => false)),
      'category_id'                    => new sfWidgetFormDoctrineSelect(array('model' => 'RelationshipCategory', 'add_empty' => false)),
      'description1'                   => new sfWidgetFormInput(),
      'description2'                   => new sfWidgetFormInput(),
      'amount'                         => new sfWidgetFormInput(),
      'goods'                          => new sfWidgetFormTextarea(),
      'filings'                        => new sfWidgetFormInput(),
      'notes'                          => new sfWidgetFormTextarea(),
      'created_at'                     => new sfWidgetFormDateTime(),
      'updated_at'                     => new sfWidgetFormDateTime(),
      'start_date'                     => new sfWidgetFormInput(),
      'end_date'                       => new sfWidgetFormInput(),
      'is_current'                     => new sfWidgetFormInputCheckbox(),
      'last_user_id'                   => new sfWidgetFormDoctrineSelect(array('model' => 'sfGuardUser', 'add_empty' => true)),
      'is_deleted'                     => new sfWidgetFormInputCheckbox(),
      'lobby_filing_list' => new sfWidgetFormDoctrineSelectMany(array('model' => 'LobbyFiling')),
    ));

    $this->setValidators(array(
      'id'                             => new sfValidatorDoctrineChoice(array('model' => 'Relationship', 'column' => 'id', 'required' => false)),
      'entity1_id'                     => new sfValidatorDoctrineChoice(array('model' => 'Entity')),
      'entity2_id'                     => new sfValidatorDoctrineChoice(array('model' => 'Entity')),
      'category_id'                    => new sfValidatorDoctrineChoice(array('model' => 'RelationshipCategory')),
      'description1'                   => new sfValidatorString(array('max_length' => 200, 'required' => false)),
      'description2'                   => new sfValidatorString(array('max_length' => 200, 'required' => false)),
      'amount'                         => new sfValidatorInteger(array('required' => false)),
      'goods'                          => new sfValidatorString(array('required' => false)),
      'filings'                        => new sfValidatorInteger(array('required' => false)),
      'notes'                          => new sfValidatorString(array('required' => false)),
      'created_at'                     => new sfValidatorDateTime(array('required' => false)),
      'updated_at'                     => new sfValidatorDateTime(array('required' => false)),
      'start_date'                     => new sfValidatorString(array('max_length' => 10, 'required' => false)),
      'end_date'                       => new sfValidatorString(array('max_length' => 10, 'required' => false)),
      'is_current'                     => new sfValidatorBoolean(array('required' => false)),
      'last_user_id'                   => new sfValidatorDoctrineChoice(array('model' => 'sfGuardUser', 'required' => false)),
      'is_deleted'                     => new sfValidatorBoolean(),
      'lobby_filing_list' => new sfValidatorDoctrineChoiceMany(array('model' => 'LobbyFiling', 'required' => false)),
    ));

    $this->widgetSchema->setNameFormat('relationship[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    parent::setup();
  }

  public function getModelName()
  {
    return 'Relationship';
  }

  public function updateDefaultsFromObject()
  {
    parent::updateDefaultsFromObject();

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

    $this->saveLobbyFilingList($con);
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
          ->from('LobbyFilingRelationship r')
          ->where('r.relationship_id = ?', current($this->object->identifier()))
          ->execute();

    $values = $this->getValue('lobby_filing_list');
    if (is_array($values))
    {
      foreach ($values as $value)
      {
        $obj = new LobbyFilingRelationship();
        $obj->relationship_id = current($this->object->identifier());
        $obj->lobby_filing_id = $value;
        $obj->save();
      }
    }
  }

}