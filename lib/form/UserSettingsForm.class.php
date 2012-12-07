<?php

class UserSettingsForm extends BasesfGuardUserProfileForm
{
  public function configure()
  {
    $choices = LsListTable::getNetworksForSelect();

    $this->setWidgets(array(
      'email'                       => new sfWidgetFormInput(array(), array('size' => 30)),
      'home_network_id'             => new sfWidgetFormSelect(array('choices' => $choices)),
      'enable_announcements'        => new sfWidgetFormInputCheckBox(),
      'enable_notes_notifications'  => new sfWidgetFormInputCheckBox(),
      'enable_pointers'             => new sfWidgetFormInputCheckbox(),
      'enable_recent_views'         => new sfWidgetFormInputCheckbox(),
      'enable_notes_list'           => new sfWidgetFormInputCheckbox(),
      'ranking_opt_out'             => new sfWidgetFormInputCheckbox(),
      'watching_opt_out'            => new sfWidgetFormInputCheckbox()
    ));

    $this->setValidators(array(
      'email'                       => new sfValidatorEmail(array('required' => true)),
      'home_network_id'             => new sfValidatorChoice(array('choices' => array_keys($choices), 'required' => true)),
      'enable_announcements'        => new sfValidatorBoolean(),
      'enable_notes_notifications'  => new sfValidatorBoolean(),
      'enable_pointers'             => new sfValidatorBoolean(),
      'enable_recent_views'         => new sfValidatorBoolean(),
      'enable_notes_list'           => new sfValidatorBoolean(),
      'ranking_opt_out'             => new sfValidatorBoolean(),
      'watching_opt_out'            => new sfValidatorBoolean()
    ));


    $this->widgetSchema->setLabels(array(
      'home_network_id' => 'Local network',
      'enable_announcements' => 'Receive announcements',
      'enable_notes_notifications' => 'Receive Notes notifications',
      'enable_pointers' => 'Show pointers',
      'enable_recent_views' => 'Show Recent Views',
      'enable_notes_list' => 'Show Notes list',
      'ranking_opt_out' => 'Opt out of rankings',
      'watching_opt_out' => 'Opt out of Who\'s Watching'
    ));

    $this->widgetSchema->setHelps(array(
      'email' => 'use this to login and recieve notifications',
      'home_network_id' => 'Default network for new entities',
      'enable_announcements' => 'send me important announcements about LittleSis',
      'enable_notes_notifications' => 'email me when an analyst writes me a note',
      'enable_pointers' => 'display helpful hints when available',
      'enable_recent_views' => 'show recently viewed entities',
      'enable_notes_list' => 'list your notes about the entity, relationship, or list you\'re viewing',
      'ranking_opt_out' => 'if checked, your name will not appear in the Analyst Rankings box on the front page',
      'watching_opt_out' => 'if checked, your name will not appear in the "Who\'s Watching" box on the lower left of entity profile pages'
    ));

    $this->widgetSchema->setNameFormat('user_settings[%s]');    
  }
}