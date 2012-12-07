<h2>Find Connections</h2>

Search for the person or organization you want to connect this <?php echo $entity['primary_ext'] == 'Person' ? 'person' : 'organization' ?> to.
This search will look at positions and memberships only, up to four degrees away. 
Keep in mind that two people who sat on the same board at different times may not know each other whatsoever.
<br />
<br />

<form action="<?php echo url_for('entity/findConnections') ?>">
<?php echo input_hidden_tag('id', $entity['id']) ?>
<?php echo input_tag('q', $sf_request->getParameter('q')) ?>&nbsp;<input class="button_small" type="submit" value="Search" />
</form>

<br />
<br />



<?php if (isset($chain_pager)) : ?>

<br />

<?php include_partial('global/section', array(
  'title' => 'Connections',
  'pager' => $chain_pager
)) ?>

<br />

<?php foreach ($entities as $n => $entity) : ?>

  <?php if (count($entity['Relationships'])) : ?>
    &rarr;
    (
    <?php $titles = array() ?>
    <?php foreach ($entity['Relationships'] as $rel) : ?>
      <?php $titles[] = trim(get_partial('relationship/oneliner', array(
        'relationship' => $rel,
        'profiled_entity' => $entities[$n - 1],
        'related_entity' => $entity
      ))) ?>
    <?php endforeach; ?>
    <?php echo implode(' ', $titles) ?>
    )

    &rarr;
  <?php endif; ?>

  <?php echo entity_link($entity) ?>

<?php endforeach; ?>


<?php elseif ($sf_request->getParameter('id2')) : ?>

<strong>No connections found between <?php echo entity_link($entity) ?> and <?php echo entity_link($entity2) ?>.</strong>

<?php endif; ?>



<?php if (isset($entity_pager)) : ?>

<?php include_partial('global/section', array(
  'title' => 'Search Results',
  'pager' => $entity_pager
)) ?>

<div class="padded">
  <?php foreach ($entity_pager->execute() as $result) : ?>
    <?php include_partial('entity/oneliner', array(
      'entity' => $result,
      'profile_link' => true,
      'actions' => array(array(
        'name' => 'select',
        'url' => EntityTable::getInternalUrl($entity, 'findConnections', array('id2' => $result['id'])),
        'options' => 'class="text_big" style="font-weight: bold"'
      ))      
    )) ?>
  <?php endforeach; ?>
  <?php echo pager_noresults($entity_pager) ?>
</div>


<?php endif; ?>