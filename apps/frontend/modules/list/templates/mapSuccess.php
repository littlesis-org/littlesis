<?php use_helper('LsText') ?>

<?php include_partial('list/header', array('list' => $list, 'show_actions' => true)) ?>

<!--
<?php include_partial('global/section', array(
  'title' => 'Network Map'
)) ?>
-->

<?php include_partial('global/map_and_controls') ?>

<script>
var data = <?php echo $data ?>;
var netmap = new Netmap(850, 550, "#netmap", '<?php echo sfConfig::get("app_netmap_api_key") ?>');
netmap.set_user_id(<?php echo $sf_user->getGuardUser()->id ?>);
netmap.set_data(data);
netmap.build();
netmap.wheel();
</script>