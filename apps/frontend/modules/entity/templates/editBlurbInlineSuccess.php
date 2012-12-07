<?php use_helper('LsText') ?>

<?php if ($entity['blurb'] && $entity['blurb'] != '') : ?>  

  <div id="entity_blurb" onmouseover="showBlurbEdit();" onmouseout="hideBlurbEdit();">
  <span class="entity_blurb"><?php echo excerpt($entity['blurb'], 90) ?></span>
  
  <?php if ($sf_user->hasCredential('editor')) : ?>
    <a href="javascript:void(0);" id="entity_blurb_edit" style="display: none;" onClick="showEditBlurbForm('<?php echo str_replace("'", "\'", $entity['blurb']) ?>');">
      <?php echo image_tag('system/edit-pencil.png') ?>
    </a>
  <?php endif; ?>
  </div>
<?php elseif ($sf_user->hasCredential('editor')) : ?>
  <div id="entity_blurb">
    <a href="javascript:void(0);" onClick="showEditBlurbForm('');">[add a short description]</a>
  </div>
<?php endif; ?>