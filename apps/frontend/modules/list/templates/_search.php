<?php include_partial('global/section', array(
  'title' => 'Search for a Member',
  'pointer' => 'Enter a name to see if they\'re on this list'
)) ?>

<div class="padded">
<form action="<?php echo url_for($list->getInternalUrl('findMember', null, true)) ?>">
<?php echo input_hidden_tag('id', $list->id) ?>
<?php echo input_tag('member_search_terms', $sf_request->getParameter('member_search_terms')) ?> 
<?php echo submit_tag('Search', 'class=button_small') ?>
</form>  
</div>
