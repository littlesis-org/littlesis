<?php use_helper('Pager') ?>

<?php slot('header_text', 'Address Search') ?>
<?php slot('rightcol', '') ?>

<span class="text_big">
Enter a location to search for nearby people and organizations.
You can enter a full address or just a city or zip code.
</span>

<br />
<br />

<?php include_partial('global/formerrors') ?>

<form action="<?php echo url_for('search/address') ?>">
<?php echo input_tag('address_search', $sf_request->getParameter('address_search'), 'size=30') ?> <?php echo submit_tag('Search', 'class=button_small') ?>
</form>

<br />
<br />


<?php if (isset($results_pager)) : ?>

Searching for people and orgs near <strong><?php echo $address->getOneLiner() ?></strong> ...
<br />
<br />

<?php include_partial('global/section', array(
  'title' => 'Results'
)) ?>

<?php include_partial('global/table', array(
  'columns' => array('Miles', 'Entity', 'Address'),
  'pager' => $results_pager,
  'row_partial' => 'entity/addresslistrow',
  'base_object' => $address
)) ?>

<?php endif; ?>