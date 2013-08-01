<div id="netmap"></div>
<div id="netmap_controls">
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
</script>
</div>
