<table>
  <tbody>
<?php foreach ($this->getAllColumns() as $column): ?>
    <tr>
      <th><?php echo sfInflector::humanize(sfInflector::underscore($column->getPhpName())) ?>:</th>
      <td>[?= $<?php echo $this->getSingularName() ?>['<?php echo $column->getName() ?>'] ?]</td>
    </tr>
<?php endforeach; ?>
  </tbody>
</table>

<hr />

<a href="[?php echo url_for('<?php echo $this->getModuleName() ?>/edit?<?php echo $this->getPrimaryKeyUrlParams() ?>) ?]">Edit</a>
&nbsp;
<a href="[?php echo url_for('<?php echo $this->getModuleName() ?>/index') ?]">List</a>
