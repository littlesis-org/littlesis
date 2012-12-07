<?php use_helper('LsText') ?>

<span id="<?php echo $id ?>_excerpt">
<?php echo $excerpt = excerpt(preg_replace("/\n/", "<br />\n", $text), isset($length) ? $length : 200) ?>
<?php if (substr($excerpt, -3) == '...') : ?>
&nbsp;<a class="pointer" onclick="
  document.getElementById('<?php echo $id ?>_excerpt').style.display = 'none';
  document.getElementById('<?php echo $id ?>_full').style.display = 'inline';
">more&nbsp;&raquo;</a>
</span>
<span id="<?php echo $id ?>_full" style="display: none;">
<?php echo preg_replace("/\n/", "<br />\n", $text) ?>
<?php if (isset($less)) : ?>
&nbsp;<a class="pointer" onclick="
  document.getElementById('<?php echo $id ?>_full').style.display = 'none';
  document.getElementById('<?php echo $id ?>_excerpt').style.display = 'inline';
">&laquo;&nbsp;less</a>
<?php endif; ?>
</span>
<?php else : ?>
</span>
<?php endif; ?>