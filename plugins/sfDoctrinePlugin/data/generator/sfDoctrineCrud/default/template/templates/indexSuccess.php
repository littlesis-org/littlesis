<?php $form = $this->getFormObject() ?>
<h1><?php echo sfInflector::humanize($this->getModuleName()) ?> List</h1>

<table>
  <thead>
    <tr>
<?php foreach ($this->getAllColumns() as $column): ?>
      <th><?php echo sfInflector::humanize(sfInflector::underscore($column->getPhpName())) ?></th>
<?php endforeach; ?>
    </tr>
  </thead>
  <tbody>
    [?php foreach ($<?php echo $this->getSingularName() ?>List as $<?php echo $this->getSingularName() ?>): ?]
    <tr>
<?php foreach ($this->getAllColumns() as $column): ?>
<?php if ($column->isPrimaryKey()): ?>
      <td><a href="[?php echo url_for('<?php echo $this->getModuleName() ?>/<?php echo isset($this->params['with_show']) && $this->params['with_show'] ? 'show' : 'edit' ?>?<?php echo $this->getPrimaryKeyUrlParams() ?>) ?]">[?php echo $<?php echo $this->getSingularName() ?>['<?php echo $column->getName() ?>'] ?]</a></td>
<?php else: ?>
      <td>[?php echo $<?php echo $this->getSingularName() ?>['<?php echo $column->getName() ?>'] ?]</td>
<?php endif; ?>
<?php endforeach; ?>
    </tr>
    [?php endforeach; ?]
  </tbody>
</table>

<a href="[?php echo url_for('<?php echo $this->getModuleName() ?>/<?php echo isset($this->params['non_atomic_actions']) && $this->params['non_atomic_actions'] ? 'edit' : 'create' ?>') ?]">Create</a>
