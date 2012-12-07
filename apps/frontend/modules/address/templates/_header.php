<?php slot('header_text', $address->getOneLiner()) ?>
<?php slot('header_link', 'entity/address?id=' . $address->id) ?>

<?php if (isset($show_actions) && $show_actions) : ?>
  <?php slot('header_actions', array(
    'edit' => array(
      'url' => 'entity/editAddress?id=' . $address->id,
      'credential' => 'editor'
    ),
    'remove' => array(
      'url' => 'entity/removeAddress?id=' . $address->id,
      'options' => 'post=true confirm=Are you sure you want to remove this address?',
      'credential' => 'deleter'
    ),
    'changes' => array(
      'url' => 'entity/addressModifications?id=' . $address->id
    )
  )) ?>
<?php endif; ?>