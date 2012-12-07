<table>
  <tr>
    <td id="logo">
      <?php echo link_to(image_tag('system' . DIRECTORY_SEPARATOR . 'littlesis_350.png', 'border=0'), '@homepage') ?>
    </td>
    <td>


<div id="topsearch">

<table style="width: 100%;">
  <tr>
    <td style="text-align: left;">

<form action="<?php echo url_for('search/simple') ?>">
<span class="text_small">
<input type="text" id="simple_search_terms" name="simple_search_terms" value="<?php $sf_request->getParameter('simple_search_terms') ?>" size="25" />
<input class="button_small" type="submit" value="Search" />
<?php //echo link_to('Advanced', 'search/advanced') ?>
</span>
<br />
<div id="search_examples">eg: tim geithner, gates, obama transition</div>
</form>

    </td>
    <td>
    </td>
  </tr>
</table>


</div>

    </td>
  </tr>
</table>