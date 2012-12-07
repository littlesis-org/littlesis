<?php include_partial('entity/header', array('entity' => $entity, 'show_actions' => true)) ?>


<?php include_partial('entity/edittabs', array('entity' => $entity)) ?>



<h2>Edit Contact Info</h2>


<?php include_partial('global/section', array(
  'title' => 'Addresses',
  'action' => array(
    'credential' => 'contributor',
    'text' => 'add',
    'url' => $entity->getInternalUrl('addAddress')
  )
)) ?>

<div class="padded">
<?php foreach ($entity->Address as $address) : ?>
  <?php echo address_link($address) ?>
  <?php if ($address->category_id) : ?>
  	<?php echo " (" . $address->Category . ") " ?>
  <?php endif; ?>
  <?php if ($sf_user->hasCredential('editor')) : ?>
    <?php echo link_to('edit', 'entity/editAddress?id=' . $address->id) ?>
  <?php endif; ?>
  <?php if ($sf_user->hasCredential('deleter')) : ?>
    <?php echo link_to('remove', 'entity/removeAddress?id=' . $address->id, 'post=true confirm=Are you sure?') ?>
  <?php endif; ?>
  <br />
<?php endforeach; ?>
</div>

<br />
<br />


<?php include_partial('global/section', array(
  'title' => 'Phones',
  'action' => array(
    'credential' => 'contributor',
    'text' => 'add',
    'url' => $entity->getInternalUrl('addPhone')
  )
)) ?>

<div class="padded">
<?php foreach ($entity->Phone as $phone) : ?>
  <?php echo $phone ?>
  <?php if ($sf_user->hasCredential('editor')) : ?>
    <?php echo link_to('edit', 'entity/editPhone?id=' . $phone->id) ?>
   <?php endif; ?>
  <?php if ($sf_user->hasCredential('deleter')) : ?>
    <?php echo link_to('remove', 'entity/removePhone?id=' . $phone->id, 'post=true confirm=Are you sure?') ?>
  <?php endif; ?>
  <br />
<?php endforeach; ?>
</div>

<br />
<br />


<?php include_partial('global/section', array(
  'title' => 'Emails',
  'action' => array(
    'credential' => 'contributor',
    'text' => 'add',
    'url' => $entity->getInternalUrl('addEmail')
  )
)) ?>

<div class="padded">
<?php foreach ($entity->Email as $email) : ?>
  <?php echo $email->address ?>
  <?php if ($sf_user->hasCredential('editor')) : ?>
    <?php echo link_to('edit', 'entity/editEmail?id=' . $email->id) ?>
  <?php endif; ?>
  <?php if ($sf_user->hasCredential('editor')) : ?>
    <?php echo link_to('remove', 'entity/removeEmail?id=' . $email->id, 'post=true confirm=Are you sure?') ?>
  <?php endif; ?>
  <br />
<?php endforeach; ?>
</div>

<br />
<br />

  
<?php include_partial('global/section', array(
  'title' => 'Source Links',
  'pointer' => 'Articles documenting info on this page'
  
)) ?>


<div class="padded reference-list">
<?php $refs = EntityTable::getContactReferencesById($entity->id) ?>
<?php foreach ($refs as $ref) : ?>
  <?php echo reference_link($ref, 50) ?><br />
<?php endforeach; ?>
</div>


<p><?php echo button_to('Done', $entity->getInternalUrl('view')) ?></p>
