[?php

/**
 * <?php echo $this->modelName ?> form base class.
 *
 * @package    form
 * @subpackage <?php echo $this->underscore($this->modelName) ?>

 * @version    SVN: $Id: sfDoctrineFormGeneratedTemplate.php 8508 2008-04-17 17:39:15Z fabien $
 */
class Base<?php echo $this->modelName ?>Form extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
<?php foreach ($this->table->getColumns() as $name => $column): ?>
      '<?php echo $this->table->getFieldName($name) ?>'<?php echo str_repeat(' ', $this->getColumnNameMaxLength() - strlen($name)) ?> => new <?php echo $this->getWidgetClassForColumn($name) ?>(<?php echo $this->getWidgetOptionsForColumn($name) ?>),
<?php endforeach; ?>
<?php foreach ($this->getManyToManyRelations() as $relation): ?>
      '<?php echo $this->underscore($relation['alias']) ?>_list'<?php echo str_repeat(' ', $this->getColumnNameMaxLength() - strlen($this->underscore($relation['refTable']->getOption('name')).'_list')) ?> => new sfWidgetFormDoctrineSelectMany(array('model' => '<?php echo $relation['table']->getOption('name') ?>')),
<?php endforeach; ?>
    ));

    $this->setValidators(array(
<?php foreach ($this->table->getColumns() as $name => $column): ?>
      '<?php echo $this->table->getFieldName($name) ?>'<?php echo str_repeat(' ', $this->getColumnNameMaxLength() - strlen($name)) ?> => new <?php echo $this->getValidatorClassForColumn($name) ?>(<?php echo $this->getValidatorOptionsForColumn($name) ?>),
<?php endforeach; ?>
<?php foreach ($this->getManyToManyRelations() as $relation): ?>
      '<?php echo $this->underscore($relation['alias']) ?>_list'<?php echo str_repeat(' ', $this->getColumnNameMaxLength() - strlen($this->underscore($relation['refTable']->getOption('name')).'_list')) ?> => new sfValidatorDoctrineChoiceMany(array('model' => '<?php echo $relation['table']->getOption('name') ?>', 'required' => false)),
<?php endforeach; ?>
    ));

    $this->widgetSchema->setNameFormat('<?php echo $this->underscore($this->modelName) ?>[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    parent::setup();
  }

  public function getModelName()
  {
    return '<?php echo $this->modelName ?>';
  }

<?php if ($this->getManyToManyRelations()): ?>
  public function updateDefaultsFromObject()
  {
    parent::updateDefaultsFromObject();

<?php foreach ($this->getManyToManyRelations() as $relation): ?>
    if (isset($this->widgetSchema['<?php echo $this->underscore($relation['alias']) ?>_list']))
    {
      $values = array();
      foreach ($this->object-><?php echo $relation['alias']; ?> as $obj)
      {
        $values[] = current($obj->identifier());
      }
      $this->object->clearRelated('<?php echo $relation['alias']; ?>');
      $this->setDefault('<?php echo $this->underscore($relation['alias']) ?>_list', $values);
    }

<?php endforeach; ?>
  }

  protected function doSave($con = null)
  {
    parent::doSave($con);

<?php foreach ($this->getManyToManyRelations() as $relation): ?>
    $this->save<?php echo $relation['alias'] ?>List($con);
<?php endforeach; ?>
  }

<?php foreach ($this->getManyToManyRelations() as $relation): ?>
  public function save<?php echo $relation['alias'] ?>List($con = null)
  {
    if (!$this->isValid())
    {
      throw $this->getErrorSchema();
    }

    if (!isset($this->widgetSchema['<?php echo $this->underscore($relation['alias']) ?>_list']))
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
          ->from('<?php echo $relation['refTable']->getOption('name') ?> r')
          ->where('r.<?php echo $relation->getLocalFieldName() ?> = ?', current($this->object->identifier()))
          ->execute();

    $values = $this->getValue('<?php echo $this->underscore($relation['alias']) ?>_list');
    if (is_array($values))
    {
      foreach ($values as $value)
      {
        $obj = new <?php echo $relation['refTable']->getOption('name') ?>();
        $obj-><?php echo $relation->getLocalFieldName() ?> = current($this->object->identifier());
        $obj-><?php echo $relation->getForeignFieldName() ?> = $value;
        $obj->save();
      }
    }
  }

<?php endforeach; ?>
<?php endif; ?>
}