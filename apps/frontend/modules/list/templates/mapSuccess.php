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
var width = <?php echo sfConfig::get('app_netmap_deafult_width') ?>;
var height = <?php echo sfConfig::get('app_netmap_deafult_height') ?>;
var key = '<?php echo sfConfig::get("app_netmap_api_key") ?>';
var netmap = new Netmap(width, height, "#netmap", key);
netmap.set_user_id(<?php echo $sf_user->getGuardUser()->id ?>);
netmap.set_data(data);
netmap.build();
netmap.wheel();
</script>