<?php use_helper('Javascript', 'Date') ?>

<?php slot('header_text', 'Network Maps') ?>
<?php slot('header_link', 'map/list') ?>

<table style="font-size: 1.2em;">
<thead style="text-align: left; border-bottom: 1px solid #ddd;">
  <th class="padded">ID</th>
  <th class="padded">Title</th>
  <th class="padded">Entities</th>
  <th class="padded">Rels</th>
  <th class="padded">User</th>
  <th class="padded">Updated</th>
  <th class="padded"></th>
</thead>
<?php foreach ($maps as $map) : ?>
<tr>
  <td class="padded" style="width: 100px;"><?php echo $map->id ?></td>
  <td class="padded" style="width: 300px;">
    <strong><?php echo link_to($map->title ? $map->title : "Map " . $map->id, "map/view?id=" . $map->id) ?></strong>
  </td>
  <td class="padded" style="width: 50px;"><?php echo count(explode(",", $map->entity_ids)) ?></td>
  <td class="padded" style="width: 50px;"><?php echo count(explode(",", $map->rel_ids)) ?></td>
  <td class="padded" style="width: 100px;"><?php echo user_link($map->getUser()) ?></td>
  <td class="padded" style="width: 200px;"><?php echo time_ago_in_words(strtotime($map->updated_at)) ?> ago</td>
  <td class="padded" stype="width: 100px;">
    <?php if ($sf_user->hasCredential('admin')) : ?>
      <?php echo link_to('remove', 'map/delete?id=' . $map->id, array('post' => true, 'confirm' => 'Are you sure?')) ?>
    <?php endif; ?>
  </td>
</tr>
<?php endforeach; ?>
</table>