<?php if (isset($entity)) : ?>
<?php $tabs = array(
  'Connections Map' => array(
    'url' => EntityTable::getInternalUrl($entity, "map"),
    'actions' => array("map")
  ),
  'Interlocks Map' => array(
    'url' => EntityTable::getInternalUrl($entity, 'interlocksMap'),
    'actions' => array('interlocksMap')
  ),
  'Saved Maps' => array(
    'url' => "map/list",
    'actions' => array()
  )
); ?>

<?php include_partial('global/tabs', array(
  'tabs' => $tabs
)) ?>
<br />
<?php endif; ?>

<div id="netmap"></div>
<div id="netmap_controls">

<input id="netmap_prune" type="button" value="prune" /><br />
<input id="netmap_wheel" type="button" value="wheel" /><br />
<input id="netmap_grid" type="button" value="grid" /><br />
<input id="netmap_shuffle" type="button" value="shuffle" /><br />
<input id="netmap_short_force" type="button" value="force" /><br />

<?php if ($sf_user->hasCredential('admin')) : ?>
<?php if (isset($id)) : ?>
  <?php echo button_to('edit', "map/edit?id=" . $id) ?><br />
<?php endif; ?>
<input id="netmap_save" type="button" value="save" /><br />
<?php endif; ?>

<script>
<?php if ($sf_user->hasCredential('admin')) : ?>
$("#netmap_save").on("click", function() {
  netmap.save_map();
});
<?php endif; ?>

$("#netmap_reload").on("click", function() {
  netmap.reload_map();
});

$("#netmap_prune").on("click", function() {
  netmap.prune();
});

$("#netmap_wheel").on("click", function() {
  netmap.wheel();
});

$("#netmap_grid").on("click", function() {
  netmap.grid();
});

$("#netmap_shuffle").on("click", function() {
  netmap.shuffle();
});

$("#netmap_short_force").on("click", function() {
  netmap.one_time_force();
});
</script>
</div>
