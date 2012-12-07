<?php

/**
 * sfGuardUserProfile form base class.
 *
 * @package    form
 * @subpackage sf_guard_user_profile
 * @version    SVN: $Id: sfDoctrineFormGeneratedTemplate.php 8508 2008-04-17 17:39:15Z fabien $
 */
class BasesfGuardUserProfileForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'id'                         => new sfWidgetFormInputHidden(),
      'user_id'                    => new sfWidgetFormDoctrineSelect(array('model' => 'sfGuardUser', 'add_empty' => false)),
      'name_first'                 => new sfWidgetFormInput(),
      'name_last'                  => new sfWidgetFormInput(),
      'email'                      => new sfWidgetFormInput(),
      'reason'                     => new sfWidgetFormTextarea(),
      'analyst_reason'             => new sfWidgetFormTextarea(),
      'is_visible'                 => new sfWidgetFormInputCheckbox(),
      'invitation_code'            => new sfWidgetFormInput(),
      'enable_html_editor'         => new sfWidgetFormInputCheckbox(),
      'enable_recent_views'        => new sfWidgetFormInputCheckbox(),
      'enable_favorites'           => new sfWidgetFormInputCheckbox(),
      'enable_pointers'            => new sfWidgetFormInputCheckbox(),
      'public_name'                => new sfWidgetFormInput(),
      'bio'                        => new sfWidgetFormTextarea(),
      'is_confirmed'               => new sfWidgetFormInputCheckbox(),
      'confirmation_code'          => new sfWidgetFormInput(),
      'filename'                   => new sfWidgetFormInput(),
      'ranking_opt_out'            => new sfWidgetFormInputCheckbox(),
      'watching_opt_out'           => new sfWidgetFormInputCheckbox(),
      'enable_notes_list'          => new sfWidgetFormInputCheckbox(),
      'enable_announcements'       => new sfWidgetFormInputCheckbox(),
      'enable_notes_notifications' => new sfWidgetFormInputCheckbox(),
      'score'                      => new sfWidgetFormInput(),
      'show_full_name'             => new sfWidgetFormInputCheckbox(),
      'unread_notes'               => new sfWidgetFormInput(),
      'home_network_id'            => new sfWidgetFormInput(),
      'created_at'                 => new sfWidgetFormDateTime(),
      'updated_at'                 => new sfWidgetFormDateTime(),
    ));

    $this->setValidators(array(
      'id'                         => new sfValidatorDoctrineChoice(array('model' => 'sfGuardUserProfile', 'column' => 'id', 'required' => false)),
      'user_id'                    => new sfValidatorDoctrineChoice(array('model' => 'sfGuardUser')),
      'name_first'                 => new sfValidatorString(array('max_length' => 50)),
      'name_last'                  => new sfValidatorString(array('max_length' => 50)),
      'email'                      => new sfValidatorString(array('max_length' => 50)),
      'reason'                     => new sfValidatorString(array('required' => false)),
      'analyst_reason'             => new sfValidatorString(array('required' => false)),
      'is_visible'                 => new sfValidatorBoolean(),
      'invitation_code'            => new sfValidatorString(array('max_length' => 50, 'required' => false)),
      'enable_html_editor'         => new sfValidatorBoolean(),
      'enable_recent_views'        => new sfValidatorBoolean(),
      'enable_favorites'           => new sfValidatorBoolean(),
      'enable_pointers'            => new sfValidatorBoolean(),
      'public_name'                => new sfValidatorString(array('max_length' => 50)),
      'bio'                        => new sfValidatorString(array('required' => false)),
      'is_confirmed'               => new sfValidatorBoolean(),
      'confirmation_code'          => new sfValidatorString(array('max_length' => 100, 'required' => false)),
      'filename'                   => new sfValidatorString(array('max_length' => 100, 'required' => false)),
      'ranking_opt_out'            => new sfValidatorBoolean(),
      'watching_opt_out'           => new sfValidatorBoolean(),
      'enable_notes_list'          => new sfValidatorBoolean(),
      'enable_announcements'       => new sfValidatorBoolean(),
      'enable_notes_notifications' => new sfValidatorBoolean(),
      'score'                      => new sfValidatorInteger(array('required' => false)),
      'show_full_name'             => new sfValidatorBoolean(),
      'unread_notes'               => new sfValidatorInteger(),
      'home_network_id'            => new sfValidatorInteger(),
      'created_at'                 => new sfValidatorDateTime(array('required' => false)),
      'updated_at'                 => new sfValidatorDateTime(array('required' => false)),
    ));

    $this->widgetSchema->setNameFormat('sf_guard_user_profile[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    parent::setup();
  }

  public function getModelName()
  {
    return 'sfGuardUserProfile';
  }

}