<?php use_helper('LsText') ?>

<?php include_partial('entity/header', array('entity' => $entity, 'show_actions' => true)) ?>

<!--
<?php include_partial('global/section', array(
  'title' => 'Network Map'
)) ?>
-->

<?php include_partial('global/map_and_controls') ?>

<script>
var data = <?php echo $data ?>;
var center_entity_id = <?php echo $entity->id ?>;
var netmap = new Netmap(850, 550, "#netmap", '<?php echo sfConfig::get("app_netmap_api_key") ?>');
netmap.set_data(data, center_entity_id);
netmap.build();
netmap.wheel();
</script>