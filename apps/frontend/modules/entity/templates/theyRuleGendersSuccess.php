<?php slot('header_text', 'SET GENDERS') ?>

<form action="<?php echo url_for('entity/theyRuleGenders') ?>" method="POST">

<?php echo submit_tag('Submit') ?>

<br />
<br />

<table class="theyrule-genders">
  <tr>
    <th>Gender</th>
    <th>Name</th>
    <th>Photo</th>
    <th>Bio</th>
  </tr>
 
  <?php $row = 1 ?>
  <?php foreach ($entities as $entity) : ?>
  <tr style="border-bottom: 1px solid #000;">
  
    <td class="genders" style="width: 50px;">
      <?php $field = 'genders' . $row . '[]' ?>
      <?php echo radiobutton_tag($field, $entity['id'] . ':' . '1', false) ?> F <br />
      <?php echo radiobutton_tag($field, $entity['id'] . ':' . '2', false) ?> M <br />
      <?php echo radiobutton_tag($field, $entity['id'] . ':' . '0', false) ?> X <br />
      <?php echo radiobutton_tag($field, $entity['id'] . ':' . '?', false) ?> ? <br />
      <?php echo link_to('search', 'http://google.com/images?q=' . urlencode($entity['name']), 'target=_blank') ?>
    </td>
    
    <td>
      <?php echo entity_link($entity) ?>
    </td>
    
    <td>
      <?php if ($image = $entity['Image'][0]['filename']) : ?>
        <?php echo link_to(image_tag(ImageTable::getPath($image, 'profile'), array('alt' => '', 'style' => 'height: 80px; border: 0;')), EntityTable::getInternalUrl($entity)) ?>
      <?php endif; ?>
    </td>

    <td>
      <?php echo $entity['summary'] ?>
    </td>
    
  </tr>
    <?php $row++ ?>
  <?php endforeach; ?>
  
</table>

<br />

<?php echo submit_tag('Submit') ?>

</form>