<div id="netmap"></div>
<div id="netmap_controls">

<?php if (isset($id)) : ?>
  <?php echo button_to('Edit', "map/edit?id=" . $id) ?><br />
<?php endif; ?>
<input id="netmap_save" type="button" value="save" /> <a id="network_map_id"><?php echo isset($id) ? $id : "" ?></a><br />
<input id="netmap_reload" type="button" value="reload" /><br />
<input id="netmap_prune" type="button" value="prune" /><br />
force: <input id="netmap_force" type="button" value="off" />

<script>
$("#netmap_force").on("click", function() {
  if ($(this).val() == "off") {
    netmap.use_force();
    $(this).val("on");
  } else {
    netmap.deny_force();
    $(this).val("off");
  }
});

$("#netmap_save").on("click", function() {
  netmap.save_map(function(id) {
    $("#network_map_id").attr("href", "http://littlesis.org/map/" + id);
    $("#network_map_id").text(id);
  });
});

$("#netmap_reload").on("click", function() {
  netmap.reload_map();
});

$("#netmap_prune").on("click", function() {
  netmap.prune();
});

</script>
</div>
