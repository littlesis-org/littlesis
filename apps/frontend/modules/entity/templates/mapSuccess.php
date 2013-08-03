<?php use_helper('LsText') ?>

<?php include_partial('entity/header', array('entity' => $entity, 'show_actions' => true)) ?>

<!--
<?php include_partial('global/section', array(
  'title' => 'Network Map'
)) ?>
-->

<?php include_partial('global/map_and_controls', array('entity' => $entity)) ?>

<script>
var data = <?php echo $data ?>;
var center_entity_id = <?php echo $entity->id ?>;
var width = <?php echo sfConfig::get('app_netmap_default_width') ?>;
var height = <?php echo sfConfig::get('app_netmap_default_height') ?>;
var key = '<?php echo sfConfig::get("app_netmap_api_key") ?>';
var netmap = new Netmap(width, height, "#netmap", key);
netmap.set_user_id(<?php echo $sf_user->isAuthenticated() ? $sf_user->getGuardUser()->id : "" ?>);
netmap.set_data(data, center_entity_id);
netmap.build();
netmap.halfwheel();
</script>