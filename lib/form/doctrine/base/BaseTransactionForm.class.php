<?php

/**
 * Transaction form base class.
 *
 * @package    form
 * @subpackage transaction
 * @version    SVN: $Id: sfDoctrineFormGeneratedTemplate.php 8508 2008-04-17 17:39:15Z fabien $
 */
class BaseTransactionForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'id'              => new sfWidgetFormInputHidden(),
      'contact1_id'     => new sfWidgetFormDoctrineSelect(array('model' => 'Entity', 'add_empty' => true)),
      'contact2_id'     => new sfWidgetFormDoctrineSelect(array('model' => 'Entity', 'add_empty' => true)),
      'district_id'     => new sfWidgetFormInput(),
      'is_lobbying'     => new sfWidgetFormInputCheckbox(),
      'relationship_id' => new sfWidgetFormDoctrineSelect(array('model' => 'Relationship', 'add_empty' => false)),
    ));

    $this->setValidators(array(
      'id'              => new sfValidatorDoctrineChoice(array('model' => 'Transaction', 'column' => 'id', 'required' => false)),
      'contact1_id'     => new sfValidatorDoctrineChoice(array('model' => 'Entity', 'required' => false)),
      'contact2_id'     => new sfValidatorDoctrineChoice(array('model' => 'Entity', 'required' => false)),
      'district_id'     => new sfValidatorInteger(array('required' => false)),
      'is_lobbying'     => new sfValidatorBoolean(array('required' => false)),
      'relationship_id' => new sfValidatorDoctrineChoice(array('model' => 'Relationship')),
    ));

    $this->widgetSchema->setNameFormat('transaction[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    parent::setup();
  }

  public function getModelName()
  {
    return 'Transaction';
  }

}