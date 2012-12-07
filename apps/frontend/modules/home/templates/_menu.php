<ul id="topmenu_links">
<?php foreach ($items as $name => $ary) : ?>
  <?php if (!is_null($ary)) : ?>
  <li>
    <?php if (isset($ary['highlighted']) && $ary['highlighted']) : ?>
      <span class="topmenu_highlight">
    <?php endif; ?>
    <?php if (isset($ary['url'])) : ?>
      <?php echo link_to($name, $ary['url'], isset($ary['items']) ? '' : 'class=topmenu_direct_link') ?>
    <?php else : ?>  
      <?php if (isset($ary['disabled'])) : ?>
        <a class="disabled" style="cursor: default;"><?php echo $name ?></a>      
      <?php else : ?>
        <a style="cursor: default;"><?php echo $name ?></a>
      <?php endif; ?>
    <?php endif; ?>
    <?php if (isset($ary['highlighted']) && $ary['highlighted']) : ?>
      </span>
    <?php endif; ?>
    <?php if (isset($ary['items'])) : ?>
    <ul>
    <?php foreach ($ary['items'] as $item => $url)  : ?>
      <li><?php echo link_to($item, $url) ?></li>  
    <?php endforeach; ?>
    </ul>
    <?php endif; ?>
  </li>
  <?php endif; ?>
<?php endforeach; ?>
</ul>