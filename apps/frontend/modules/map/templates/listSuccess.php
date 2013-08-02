<?php use_helper('Date') ?>

<?php slot('header_text', 'Network Maps') ?>
<?php slot('header_link', 'map/list') ?>

<table style="font-size: 1.2em;">
<thead style="text-align: left; border-bottom: 1px solid #ddd;">
  <th class="padded">ID</th>
  <th class="padded">Title</th>
  <th class="padded">User ID</th>
  <th class="padded">Updated At</th>
</thead>
<?php foreach ($maps as $map) : ?>
<tr>
  <td class="padded" style="width: 100px;"><?php echo $map->id ?></td>
  <td class="padded" style="width: 300px;">
    <strong><?php echo link_to($map->title ? $map->title : "Map " . $map->id, "map/view?id=" . $map->id) ?></strong>
  </td>
  <td class="padded" style="width: 100px;"><?php echo $map->getUser()->getProfile()->public_name ?></td>
  <td class="padded" style="width: 200px;"><?php echo time_ago_in_words(strtotime($map->updated_at)) ?> ago</td>
</tr>
<?php endforeach; ?>
</table>