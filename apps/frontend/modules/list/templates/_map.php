<div id="address_map_small" class="address_map_small" style="border: 1px solid #bbb; width: 300px; height: 300px;">
</div>

<script src="http://maps.google.com/maps?file=api&amp;v=2&amp;key=<?php echo sfConfig::get('app_google_maps_key') ?>"
	type="text/javascript"></script>

<script type="text/javascript">

function initialize()
{
	if (GBrowserIsCompatible())
	{
		var map = new GMap2(document.getElementById("address_map_small"));
    var geocoder = new GClientGeocoder();

    geocoder.getLatLng(
      "<?php echo $list['name'] ?>",
      function(point) {
        map.setCenter(point, <?php echo $list['id'] == LsListTable::US_NETWORK_ID ? '2' : '10' ?>);
      }
    );

		map.addControl(new GSmallZoomControl());
	}
}

initialize();

</script>