<div class="button_tabs">
  <span id="indicator" style="display: none; float: right;"><?php echo image_tag('system/ajax_waiting.gif') ?></span>

<?php foreach ($tabs as $text => $ary) : ?>

  <?php $options = array('return' => true) ?>

  <?php if (isset($remote) && (!isset($ary['remote']) || $ary['remote'])) : ?>
    <span id="button_tabs_<?php echo str_replace(' ', '', strtolower($text)) ?>" class="<?php echo (isset($active) && in_array($active, $ary['actions'])) ? 'active' : 'inactive' ?>">
    <?php use_helper('LsJavascript') ?>
    <?php $options['href'] = isset($ary['href']) ? $ary['href'] : null ?>


    <?php $updateActiveJs = "document.getElementById('button_tabs_" . str_replace(' ', '', strtolower($text)) . "').setAttribute('class', 'active');" ?>

    <?php foreach (array_keys($tabs) as $tabText) : ?>
      <?php if ($tabText != $text) : ?>
        <?php $updateActiveJs .= "document.getElementById('button_tabs_" . str_replace(' ', '', strtolower($tabText)) . "').setAttribute('class', 'inactive');" ?>
      <?php endif; ?>
    <?php endforeach; ?>


    <?php echo ls_link_to_remote(
      $text, 
      array(
        'url' => $ary['url'], 
        'update' => $update, 
        'method' => 'get',
        'loading' => "document.getElementById('indicator').style.display = 'block';" . $updateActiveJs,
        'complete' => "document.getElementById('indicator').style.display = 'none';"
      ),
      $options
    ) ?>
  <?php else : ?>
    <span id="button_tabs_<?php echo str_replace(' ', '', strtolower($text)) ?>" class="<?php echo in_array(sfContext::getInstance()->getActionName(), $ary['actions']) ? 'active' : 'inactive' ?>">
    <?php echo link_to($text, $ary['url'], $options) ?>    
  <?php endif; ?>

  </span>
  
<?php endforeach; ?>

</div>