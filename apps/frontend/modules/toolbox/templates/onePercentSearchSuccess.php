<?php use_helper('LsText', 'Javascript') ?>

<h3><?php echo link_to("TOOLBOX",'toolbox/index') ?> > One Percent Search</h3>

<div style="width: 60%">
<p>Use this tool to find names of elites in blocks of text. Enter a url and the tool will look for proper names in the text of that page and then search LittleSis for those names; enter a block of text or list of names and the tool will parse that text and search LittleSis for the names it finds. Use it for: articles; event listings; other documents.</p>
</p>
</div>
<?php include_partial('global/formerrors', array('form' => array($search_form))) ?>

<form action="onePercentSearch" method="POST">
<?php echo $search_form['_csrf_token'] ?>
<table>
    <?php include_partial('global/formfield', array(
      'field' => $search_form['url']
    )) ?>
    <?php include_partial('global/formfield', array(
      'field' => $search_form['text']
    )) ?>
    <tr>
    <td></td>
    <td>
      <?php echo submit_tag('Submit') ?>
    </td>
    </tr>
</table>
</form>

</table>
<br><br>
<?php if ($sf_request->isMethod('post') && count($search_form->getErrorSchema()->getErrors()) == 0) : ?>
<h3>Search Results</h3>
<?php if (!isset($matches) && count($matches) == 0) : ?>
  Sorry, no matches found.
<?php else : ?>
<table class="donor-table" width="70%">
  <tr>
    <th>name</th>
    <th>possible matches</th>
  </tr>
  <?php foreach($matches as $match) : ?>
    <tr>
      <td width="30%">
        <?php echo $match['name'] ?>
      </td>
      <td>
        <?php foreach($match['search_results'] as $entity) : ?>
          <?php echo link_to($entity['name'], EntityTable::getInternalUrl($entity), array('target' => '_new')) ?>
          &nbsp;
          <span style="font-size: 11px;"><?php echo excerpt($entity['blurb'], 100) . '&nbsp;' . PersonTable::getRelatedOrgSummary($entity) ?></span>
          <br>
        <?php endforeach; ?>
      </td>
    </tr>
  <?php endforeach; ?>  
</table>
<?php endif; ?>
<?php endif; ?>
<br><br>

