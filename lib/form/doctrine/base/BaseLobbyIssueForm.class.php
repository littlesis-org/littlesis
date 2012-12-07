<?php

/**
 * LobbyIssue form base class.
 *
 * @package    form
 * @subpackage lobby_issue
 * @version    SVN: $Id: sfDoctrineFormGeneratedTemplate.php 8508 2008-04-17 17:39:15Z fabien $
 */
class BaseLobbyIssueForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'id'                            => new sfWidgetFormInputHidden(),
      'name'                          => new sfWidgetFormInput(),
      'lobby_filing_list' => new sfWidgetFormDoctrineSelectMany(array('model' => 'LobbyFiling')),
    ));

    $this->setValidators(array(
      'id'                            => new sfValidatorDoctrineChoice(array('model' => 'LobbyIssue', 'column' => 'id', 'required' => false)),
      'name'                          => new sfValidatorString(array('max_length' => 50)),
      'lobby_filing_list' => new sfValidatorDoctrineChoiceMany(array('model' => 'LobbyFiling', 'required' => false)),
    ));

    $this->widgetSchema->setNameFormat('lobby_issue[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    parent::setup();
  }

  public function getModelName()
  {
    return 'LobbyIssue';
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
          ->from('LobbyFilingLobbyIssue r')
          ->where('r.issue_id = ?', current($this->object->identifier()))
          ->execute();

    $values = $this->getValue('lobby_filing_list');
    if (is_array($values))
    {
      foreach ($values as $value)
      {
        $obj = new LobbyFilingLobbyIssue();
        $obj->issue_id = current($this->object->identifier());
        $obj->lobby_filing_id = $value;
        $obj->save();
      }
    }
  }

}