<?php use_helper('LsText') ?>

<?php include_partial('entity/header', array('entity' => $entity, 'show_actions' => true)) ?>

<!--
<?php include_partial('global/section', array(
  'title' => 'Network Map'
)) ?>
-->

<div id="netmap"></div>

<script>

var data = <?php echo $data ?>;
var center_entity_id = <?php echo $entity->id ?>;

var netmap = new Netmap(960, 550, "#netmap");
netmap.set_data(data, center_entity_id);
netmap.build();
netmap.wheel();

</script>