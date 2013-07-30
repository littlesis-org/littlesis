<?php use_helper('LsText') ?>

<?php include_partial('list/header', array('list' => $list, 'show_actions' => true)) ?>

<!--
<?php include_partial('global/section', array(
  'title' => 'Network Map'
)) ?>
-->

<div id="netmap"></div>

<script>

var data = <?php echo $data ?>;
var netmap = new Netmap(960, 600, "#netmap");
netmap.set_data(data);
netmap.build();
netmap.wheel();

</script>