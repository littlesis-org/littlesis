<?php slot('header_text', 'Remove Cache') ?>


<?php if (count($patterns)) : ?>
<div style="padding: 1em; background-color: #ccc;">
  Cache keys mattching the following patterns have been cleared:
  <ul>
  <?php foreach ($patterns as $pattern) : ?>
    <li><strong><?php echo $pattern ?></strong></li>
  <?php endforeach; ?>
  </ul>
</div>
<br />
<br />
<?php endif; ?>


Complete any of the following fields.
<br />
<br />


<form action="<?php echo url_for('cache/remove') ?>" method="POST">
<table class="cache-remove-table">
  <tr>
    <td class="form_label">Page</td>
    <td><?php echo input_tag('page', $sf_request->getParameter('page'), 'size=50') ?> </td>
  </tr>
  <tr>
    <td class="form_label">Entity ID</td>
    <td><?php echo input_tag('entity_id', $sf_request->getParameter('entity_id'), 'size=20') ?></td>
  </tr>
  <tr>
    <td class="form_label">Relationship ID</td>
    <td><?php echo input_tag('relationship_id', $sf_request->getParameter('relationship_id'), 'size=20') ?></td>
  </tr>
  <tr>
    <td class="form_label">List ID</td>
    <td><?php echo input_tag('list_id', $sf_request->getParameter('list_id'), 'size=20') ?></td>
  </tr>
  <tr>
    <td class="form_label">Group Name</td>
    <td><?php echo input_tag('groupname', $sf_request->getParameter('groupname'), 'size=20') ?></td>
  </tr>
  <tr>
    <td class="form_label">Username</td>
    <td><?php echo input_tag('username', $sf_request->getParameter('username'), 'size=20') ?></td>
  </tr>
  <tr>
    <td></td>
    <td><?php echo submit_tag('Remove Cache') ?></td>
  </tr>
</table>
</form>


