<?php if (isset($possible_duplicates) && count($possible_duplicates)) : ?>
<div class="cleanup text_big">
<?php echo (count($possible_duplicates) > 1) ? 'Persons with similar names have been found:' : 'A person with a similar name has been found:' ?>

<?php $links = array() ?>
<?php foreach ($possible_duplicates as $duplicate) : ?>
  <?php $links[] = link_to($duplicate['name'], EntityTable::getInternalUrl($duplicate)) ?>
<?php endforeach; ?>
<?php echo implode(', ', $links) ?>.

Please <?php echo link_to('let us know', '@contact') ?> if these are duplicates.
</div>
<br />
<?php endif; ?>