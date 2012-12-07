<?php if ($address->latitude && $address->longitude) : ?>

<div id="address_map_small" class="address_map_small">
</div>

<div class="padded">
<?php echo link_to('View on Google Maps', 'http://maps.google.com/maps?f=q&hl=en&geocode=&q=' . urlencode($address->getOneLiner()) . '&ie=UTF8&z=' . ($address->postal ? '14' : '10')) ?>
</div>

<script src="http://maps.google.com/maps?file=api&amp;v=2&amp;key=<?php echo sfConfig::get('app_google_maps_key') ?>"
	type="text/javascript"></script>

<script type="text/javascript">

function initialize()
{
	if (GBrowserIsCompatible())
	{
		var map = new GMap2(document.getElementById("address_map_small"));

		map.setCenter(new GLatLng(
			<?php echo $address->latitude ?>,
			<?php echo $address->longitude ?>
		), <?php echo $address->postal ? '14' : '10' ?>);

		map.setMapType(<?php echo $address->postal ? 'G_SATELLITE_MAP' : 'G_NORMAL_MAP' ?>);

		map.addControl(new GSmallZoomControl());

		var point = new GLatLng(
			<?php echo $address->latitude ?>,
			<?php echo $address->longitude ?>
		);		

		map.addOverlay(new GMarker(point));
	}
}

initialize();

</script>
<?php else : ?>

<div class="redback" style="padding: 20px;">
<big>No map available for this <?php echo link_to('address', 'http://maps.google.com/?q=' . $address->getOneLiner()) ?>.</big>
</div>
<?php endif; ?>