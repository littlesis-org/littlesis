<?php use_helper('Pager', 'LsText') ?>

<?php slot('header_text', 'Simple Search') ?>


<form action="<?php echo url_for('search/simple') ?>">

<?php if (isset($networks)) : ?>

<span class="text_big">
Searching 
<?php foreach ($networks as $network) : ?>
  <strong><?php echo $network['name'] ?></strong>
<?php endforeach; ?>
network(s). <strong><?php echo link_to('Search all &raquo;', '@search', array('query_string' => 'q=' . $sf_request->getParameter('q'))) ?></strong>
</span>

<br />
<br />

<?php endif; ?>

<?php $existing = $sf_request->getParameter('q') && ($sf_request->getParameter('action') == 'simple') ?>
<input type="text" id="simple_search_terms" name="q" value="<?php echo $sf_request->getParameter('q') ?>" size="25" />
<?php if (isset($networks)) : ?>
<input type="hidden" name="network_ids[]" value="<?php echo join($sf_request->getParameter('network_ids'), ',') ?>" />
<?php endif; ?>
<input type="submit" value="Search" />
</form>

<br />
<br />


<?php if (isset($groups) && count($groups)) : ?>

<?php use_helper('LsText') ?>

<?php include_partial('global/section', array(
  'title' => 'Research Groups'
))?>

<?php foreach ($groups as $group) : ?>

<span class="text_big"><strong><?php echo rails_group_link($group) ?></strong></span>&nbsp;
<em><?php echo excerpt($group['tagline'], 100) ?></em>
<br />
    
<?php endforeach; ?>

<br />
<br />

<?php endif; ?>


<?php if (isset($lists) && count($lists)) : ?>

<?php include_partial('global/section', array(
  'title' => 'Lists'
))?>

<?php foreach ($lists as $list) : ?>
  <?php include_partial('list/oneliner', array('list' => $list)) ?>
<?php endforeach; ?>

<br />
<br />

<?php endif; ?>


<?php if (isset($results_pager)) : ?>

<?php include_partial('global/section', array(
  'title' => 'Entities',
  'pointer' => null, // 'If you need more precise results, try the ' . link_to('advanced search','search/advanced'),
  'pager' => $results_pager
)) ?>

<div class="padded">
  <?php foreach ($results_pager->execute() as $entity) : ?>
    <div class="search-spacer">
      <?php include_partial('entity/oneliner', array('entity' => $entity, 'search' => $sf_request->getParameter('q'))) ?>
    </div>
  <?php endforeach; ?>
  <?php echo pager_noresults($results_pager) ?>


<?php include_partial('search/cantfind') ?>

    
</div>
<?php endif; ?>
