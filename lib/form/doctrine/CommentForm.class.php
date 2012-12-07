<?php

/**
 * Comment form.
 *
 * @package    form
 * @subpackage Comment
 * @version    SVN: $Id: sfPropelFormTemplate.php 6174 2007-11-27 06:22:40Z fabien $
 */
class CommentForm extends BaseCommentForm
{
  public function configure()
  {
    $this->setWidgets(array(
      'title'        => new sfWidgetFormInput(array(), array('size' => 30)),
      'body'         => new sfWidgetFormTextarea(array(), array('cols' => 50, 'rows' => 10))
    ));

    $this->setValidators(array(
      'title'        => new sfValidatorString(array('max_length' => 50)),
      'body'         => new sfValidatorString()
    ));

    $this->widgetSchema->setHelps(array(
      'title' => '50 chars max'
    ));

    $this->widgetSchema->setNameFormat('comment[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);
  }
}