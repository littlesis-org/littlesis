<?php

/**
 * Lobbyist form.
 *
 * @package    form
 * @subpackage Lobbyist
 * @version    SVN: $Id: sfPropelFormTemplate.php 6174 2007-11-27 06:22:40Z fabien $
 */
class LobbyistForm extends BaseLobbyistForm
{
  public function configure()
  {
    /*
    $this->setWidgets(array(
      'lda_registrant_id'      => new sfWidgetFormInput(),
    ));

    $this->setValidators(array(
      'lda_registrant_id'      => new sfValidatorInteger(array('required' => false)),
    ));

    $this->widgetSchema->setLabels(array(
      'lda_registrant_id' => 'LDA Registrant ID'
    ));*/
    
    $this->setWidgets(array());
    $this->setValidators(array());
    $this->widgetSchema->setLabels(array());

    $this->widgetSchema->setNameFormat('entity[%s]');
  }
}