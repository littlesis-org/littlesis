<?php include_partial('list/header', array('list' => $list, 'show_actions' => ($list->is_admin && !$sf_user->hasCredential('admin')) ? false : true)) ?>


<?php slot('rightcol') ?>
  <?php include_partial('global/modifications', array(
    'object' => $list,
    'model' => 'LsList',
    'more_uri' => LsListTable::getInternalUrl($list, 'modifications')
  )) ?>
  <?php include_partial('list/search', array('list' => $list)) ?>
  <br />
  <?php include_component('reference', 'list', array(
    'object' => $list,
    'model' => 'LsList',
    'more_uri' => LsListTable::getInternalUrl($list, 'references')
  )) ?>
  <br />
  
<?php if ($sf_user->hasCredential('bulker')) : ?>
  <?php include_partial('list/expertlinks', array('list' => $list)) ?>
  <br />
  
<?php endif; ?>
  <?php include_partial('global/notes', array(
    'record' => $list,
    'model' => 'LsList',
    'more_uri' => LsListTable::getInternalUrl($list, 'notes')
  )) ?>
<?php end_slot() ?>



<?php if ($list->is_admin) : ?>
  <em>This list is maintained by admin users. Please 
  <?php echo link_to('contact us','home/contact') ?>
  if you would like to suggest changes.</em>
  <br />
  <br />
<?php endif; ?>

<?php if ($list['description']) : ?>
<span class="profile_summary">
  <?php include_partial('global/excerpt', array('text' => $list['description'], 'id' => 'description', 'less' => true)) ?>
  <br />
  <br />
</span>
<?php endif; ?>

<!-- TABS -->

<?php include_partial('list/membertabs', array('list' => $list)) ?>

<div id="member_tabs_content">
</div>

<?php echo javascript_tag("

  hash = parseHash();
  tab = $('button_tabs_' + hash.action);  

  if (tab)
  {
    tab.setAttribute('class', 'active');
  }
  
" . ls_remote_function(array(
      'update' => 'member_tabs_content',
      'url' => $list->getInternalUrl(),
      'posturl' => "'/' + hash.action + (hash.page ? '?page=' + hash.page : '')",
      'method' => 'get',
      'loading' => "document.getElementById('indicator').style.display = 'block';",
      'complete' => "document.getElementById('indicator').style.display = 'none';"
    ))
) ?>


<script type="text/javascript">
function show_rank_form(id)
{
  span = document.getElementById('set_rank_' + id);
  span.style.display = 'inline';

  link = document.getElementById('show_rank_' + id);
  link.style.display = 'none';
}
</script>