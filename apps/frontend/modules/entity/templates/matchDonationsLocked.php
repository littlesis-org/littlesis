<?php slot('header_text', $entity['name']) ?>
<?php slot('header_link', EntityTable::getInternalUrl($entity)) ?>


<h2>Match Donors From OpenSecrets</h2>

Another analyst is currently matching OpenSecrets donors for this person.

<ul>
  <li><strong><?php echo link_to('Go back', EntityTable::getInternalUrl($entity)) ?></strong> to <?php echo $entity['name'] ?>'s profile page.</li>
  <?php if (isset($next_entity) && $next_entity) : ?>
    <li><strong><?php echo link_to('Match donations', EntityTable::getInternalUrl($next_entity, 'matchDonors')) ?></strong> for another person.</li>
  <?php endif; ?>
</ul>