<ul class="nav navbar-nav">
<?php foreach ($items as $name => $ary) : ?>
  <?php if (!is_null($ary)) : ?>
    <?php if (isset($ary['url']) && (!isset($ary['items']) || count($ary['items']) == 0)) : ?>
      <li><?php echo link_to($name, $ary['url']) ?></li>
    <?php else : ?>
      <li class="dropdown">
        <?php $class = @$ary['highlighted'] ? "dropdown-toggle highlighted" : "dropdown-toggle" ?>
        <a class="<?php echo $class ?>" data-toggle="dropdown"><?php echo $name ?></a>
        <ul class="dropdown-menu">
          <?php foreach ($ary['items'] as $item => $url)  : ?>
            <?php if ($url == 'divider') : ?>
              <li class="divider"></li>
            <?php else : ?>
              <li><?php echo link_to($item, $url) ?></li>
            <?php endif; ?>
          <?php endforeach; ?>
        </ul>
      </li>
    <?php endif; ?>
  </li>
  <?php endif; ?>
<?php endforeach; ?>
</ul>