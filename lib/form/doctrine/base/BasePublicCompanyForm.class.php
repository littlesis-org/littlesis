<?php

/**
 * PublicCompany form base class.
 *
 * @package    form
 * @subpackage public_company
 * @version    SVN: $Id: sfDoctrineFormGeneratedTemplate.php 8508 2008-04-17 17:39:15Z fabien $
 */
class BasePublicCompanyForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'id'        => new sfWidgetFormInputHidden(),
      'ticker'    => new sfWidgetFormInput(),
      'sec_cik'   => new sfWidgetFormInput(),
      'entity_id' => new sfWidgetFormDoctrineSelect(array('model' => 'Entity', 'add_empty' => false)),
    ));

    $this->setValidators(array(
      'id'        => new sfValidatorDoctrineChoice(array('model' => 'PublicCompany', 'column' => 'id', 'required' => false)),
      'ticker'    => new sfValidatorString(array('max_length' => 10)),
      'sec_cik'   => new sfValidatorInteger(array('required' => false)),
      'entity_id' => new sfValidatorDoctrineChoice(array('model' => 'Entity')),
    ));

    $this->widgetSchema->setNameFormat('public_company[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    parent::setup();
  }

  public function getModelName()
  {
    return 'PublicCompany';
  }

}