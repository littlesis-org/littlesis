<?php use_helper('Javascript') ?>

<?php include_partial('entity/header', array('entity' => $entity, 'show_actions' => true)) ?>

<?php include_partial('entity/edittabs', array('entity' => $entity)) ?>


<div class="section">
<span class="section_title">API KEYS</span>
</div>
<br>
<?php if (isset($existing_keys)) : ?>
  
  <form action="<?php echo url_for($entity->getInternalUrl('editKeys',null,true)) ?>" method="POST">
  <input type="hidden" name="domain_id" value="<?php echo $domain->id ?>">
  <input type="hidden" name="id" value="<?php echo $entity->id ?>">
  <span class="form_help">Choose possible external key matches from <?php echo $domain->url ?> (or its API).</span><br><br>
  <table>
  <tr>
    <td class="form_label"><strong><?php echo $domain->name ?>:</strong></td>
    <td class="form_field">
  <?php if ($matches && count($matches)) : ?>
    <?php foreach ($matches as $match) : ?>
      <?php $checked = isset($existing_keys[$domain->name]) && in_array($match['id'],$existing_keys[$domain->name]) ? "checked" : ""; ?>
      <input type="checkbox" name="key[]" value="<?php echo $match['id']?>" <?php echo $checked?>/>
      <?php echo $match['id'] ?>
      <?php echo $match['name'] != '' ? " (" . $match['name'] . ")" : "" ?>
      <br>
    <?php endforeach; ?>
    
  <?php else : ?>
    <em>no results found</em><br>
  <?php endif; ?>
  or add one here: <input type="text" name="manual_key" size="30"/>
  <br><br>
    <input type="submit" name="submit" value="submit">&nbsp;
    <input type="submit" name="submit" value="cancel">
   </td>
   </tr>
  </form>
  </table>
<?php else : ?>
  <table style="width: 50%;">
  <?php foreach($domains as $domain) : ?>
    <tr>
      <td style="width: 20%"><strong><?php echo $domain->name ?></strong></td>
      <td class="form_field"><a href="<?php echo url_for($entity->getInternalUrl('editKeys',array('domain_id' => $domain->id),false))?>">edit...</a></td>
      <td>
        <?php foreach($keys as $key) : ?>
          <?php if($key->Domain->name == $domain->name) : ?>
            <?php echo $key->external_id ?> <br>
          <?php endif; ?>
        <?php endforeach; ?>
    </tr>
  <?php endforeach; ?>
  </table>

<?php endif; ?>

