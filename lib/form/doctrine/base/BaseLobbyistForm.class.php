<?php

/**
 * Lobbyist form base class.
 *
 * @package    form
 * @subpackage lobbyist
 * @version    SVN: $Id: sfDoctrineFormGeneratedTemplate.php 8508 2008-04-17 17:39:15Z fabien $
 */
class BaseLobbyistForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'id'                         => new sfWidgetFormInputHidden(),
      'lda_registrant_id'          => new sfWidgetFormInput(),
      'entity_id'                  => new sfWidgetFormDoctrineSelect(array('model' => 'Entity', 'add_empty' => false)),
      'lobby_filing_list' => new sfWidgetFormDoctrineSelectMany(array('model' => 'LobbyFiling')),
    ));

    $this->setValidators(array(
      'id'                         => new sfValidatorDoctrineChoice(array('model' => 'Lobbyist', 'column' => 'id', 'required' => false)),
      'lda_registrant_id'          => new sfValidatorInteger(array('required' => false)),
      'entity_id'                  => new sfValidatorDoctrineChoice(array('model' => 'Entity')),
      'lobby_filing_list' => new sfValidatorDoctrineChoiceMany(array('model' => 'LobbyFiling', 'required' => false)),
    ));

    $this->widgetSchema->setNameFormat('lobbyist[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    parent::setup();
  }

  public function getModelName()
  {
    return 'Lobbyist';
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