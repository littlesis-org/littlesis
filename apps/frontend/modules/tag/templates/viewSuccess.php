<?php slot('header_text', 'Tagged with '. $tag) ?>
<?php slot('header_link', 'tag/view?name=' . $tag->getName()) ?>

<?php slot('header_actions', array(
  'remove' => array(
    'credential' => 'deleter',
    'url' => 'tag/remove?name=' . $tag->getName(),
    'options' => 'post=true confirm=Are you sure you want to remove this tag?'
  )
)) ?>

<?php foreach ($models as $model) : ?>

<?php $pager = eval('return $' . strtolower($model) . '_pager;') ?>

<?php include_partial('global/section', array(
  'title' => LsLanguage::pluralize($model),
  'pager' => $pager,
  'more' => 'tag/objects?name=' . $tag->getName() . '&model=' . $model
)) ?>

<div class="padded">
  <?php foreach ($pager->execute() as $object) : ?>
    <strong><?php echo link_to($object, strtolower($model) . '/view?id=' . $object->id) ?></strong>
    <br />
  <?php endforeach; ?>
</div>

<br />
<br />

<?php endforeach; ?>