<script type="text/javascript">
function swapAll(master) {
  var form = $('donors');
  checkboxes = form.getInputs('checkbox');
  checkboxes.each(function(e) { e.checked = master.checked });
}
</script>


<?php slot('header_text', $entity['name']) ?>
<?php slot('header_link', EntityTable::getInternalUrl($entity)) ?>


<?php slot('leftcol') ?>
  <div style="border: 1px solid #666; padding: 1em;">
  <?php if ($entity['blurb']) { echo $entity['blurb'] . '<br /><br />'; } ?>
  <?php if ($entity['summary']) : ?>
    <?php include_partial('global/section', array('title' => 'Bio')) ?>
    <div class="padded">
    <?php include_partial('global/excerpt', array('text' => $entity['summary'], 'id' => 'summary', 'less' => true, 'length' => 500)) ?>
    </div>
    <br />
    <br />
  <?php endif; ?>
  <?php include_partial('global/section', array('title' => 'Relationships')) ?>
  <div class="padded">
  <?php foreach ($related_entities as $related) : ?>
    <strong><?php echo $related['name'] ?></strong>
    &nbsp;
    <?php $descriptions = array() ?>
    <?php foreach ($related['Relationships'] as $rel) : ?>
      <?php $description = ($rel['entity1_id'] == $entity['id']) ? $rel['description2'] : $rel['description1'] ?>
      <?php if (!$description) { $description = RelationshipTable::getCategoryDefaultDescription($rel); } ?>
      <?php $descriptions[] = $description ?>
    <?php endforeach; ?>
    <?php echo implode(', ', $descriptions) ?>
    <br />
  <?php endforeach; ?>
  </div>
  </div>
<?php end_slot() ?>


<h2>Match Donors From OpenSecrets.org</h2>

<?php if (count($donors)) : ?>

<?php echo link_to(image_tag('system'.DIRECTORY_SEPARATOR.'opensecrets-logo-225.gif', 'align=left style="border: 0; padding-right: 0.5em;"'), 'http://opensecrets.org') ?>

Below are donor records from <?php echo link_to('OpenSecrets', 'http://opensecrets.org') ?> with the same or similar name.
Using the info already in LittleSis (displayed on the left), and the employer info, addresses, and filing sources from OpenSecrets, identify which of these donor records belong to this person and check the appropriate boxes.
<br />
<br />

<?php if ($reviewed_by_user) : ?>
<strong>Last reviewed by <?php echo user_link_by_public_name($reviewed_by_user, null) ?> at <?php echo $reviewed_at ?></strong>
<br />
<br />
<?php endif; ?>

<form id="donors" action="<?php echo url_for(EntityTable::getInternalUrl($entity, 'matchDonors')) ?>" method="POST">
<?php if (isset($next_entity) && $next_entity) : ?>
  <?php echo input_hidden_tag('next_id', $next_entity['id']) ?>
<?php endif; ?>
<table class="donor-table">
  <tr class="donor-table-header">
    <td><input type="checkbox" onclick="swapAll(this);"></td>
    <td>Names</td>
    <td>Employers</td>
    <td>Addresses</td>
    <td>Sources</td>
  </tr>
<?php foreach ($donors as $donorId => $infoAry) : ?>
  <?php /* HIDE DONORS WITH ONE INCOMPATIBLE MIDDLE NAME */ ?>
  <?php $middles = array_keys($infoAry['middles']) ?>
  <?php if (count($middles) == 1) : ?>
    <?php if (!PersonTable::middleNamesAreCompatible($middles[0], $entity['name_middle'])) : ?>
      <?php continue ?>
    <?php endif; ?>    
  <?php endif; ?>

  <tr class="text_small" style="margin-bottom: 0.5em;">
    <td><input type="checkbox" name="donor_ids[]" value="<?php echo $donorId ?>" <?php echo in_array($donorId, $verified_donor_ids) ? 'checked ' : '' ?>/></td>
    <td><?php echo implode('<br />', array_keys($infoAry['names'])) ?></td>
    <td><?php echo implode('<br />', array_filter(array_keys($infoAry['orgs']))) ?></td>
    <td><?php echo implode('<br />', array_keys($infoAry['addresses'])) ?></td>    
    <td>
      <?php foreach (array_slice(array_keys($infoAry['image_ids']), 0, 5) as $imageId) : ?>
        <?php if ($imageId) : ?>
          <?php echo link_to($imageId, 'http://images.nictusa.com/cgi-bin/fecimg/?' . $imageId, 'target=_new') ?><br />
        <?php endif; ?>
      <?php endforeach; ?>
  </tr>
<?php endforeach; ?>
</table>
<br />

Within a couple minutes of submitting this form, donation relationships will be generated between this person and the political candidates and committees this person gave money to, and will show up on this person's profile.
<br />
<br />

<?php echo submit_tag('Verify') ?> 
<?php if ($next_entity) : ?>
  <?php echo submit_tag('Verify and Match Another') ?> 
  <?php echo submit_tag('Skip and Match Another') ?> 
<?php endif; ?>
<?php if (!$sf_request->getParameter('preprocess')) : ?>
  <?php echo button_to('Find More', EntityTable::getInternalUrl($entity, 'matchDonors', array('preprocess' => 1))) ?> 
<?php endif; ?>
<?php echo submit_tag('Cancel') ?>
</form>


<?php else : ?>

No matching donors found.

<ul>
  <li><strong><?php echo link_to('Go back', EntityTable::getInternalUrl($entity)) ?></strong> to <?php echo $entity['name'] ?>'s profile page.</li>
  <?php if (isset($next_entity) && $next_entity) : ?>
    <li><strong><?php echo link_to('Match donations', EntityTable::getInternalUrl($next_entity, 'matchDonors')) ?></strong> for another person.</li>
  <?php endif; ?>
  <?php if (!$sf_request->getParameter('preprocess')) : ?>
    <li><strong><?php echo link_to('Find more', EntityTable::getInternalUrl($entity, 'matchDonors', array('preprocess' => 1))) ?></strong> possible donor matches.</li>
  <?php endif; ?>
</ul>

<?php endif; ?>