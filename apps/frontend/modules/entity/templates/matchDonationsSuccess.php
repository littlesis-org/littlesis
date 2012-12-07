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


<h2>Match Donations From OpenSecrets.org</h2>

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

<?php if (isset($updated_at) && $updated_at) : ?>
<div style="background-color: #faa; padding: 0.5em; font-size: 14px;"><strong><em>Possible matches updated at <?php echo $updated_at ?></em></strong></div>
<br />
<?php endif; ?>

<form id="donors" action="<?php echo url_for(EntityTable::getInternalUrl($entity, 'matchDonations')) ?>" method="POST">
<?php if (isset($next_entity) && $next_entity) : ?>
  <?php echo input_hidden_tag('next_id', $next_entity['id']) ?>
<?php endif; ?>
<table class="donor-table">
  <tbody>
    <tr class="donor-table-header">
      <td style="width: 30px;"><input type="checkbox" onclick="swapAll(this);"></td>
      <td>Names</td>
      <td>Employers</td>
      <td>Addresses</td>
      <td style="width: 80px;">Sources</td>
    </tr>
  </tbody>
<?php $donorIds = array() ?>
<?php foreach ($donors as $donorId => $infoAry) : ?>
  <?php /* HIDE DONORS WITH ONE INCOMPATIBLE MIDDLE NAME */ ?>
  <?php $middles = array_keys($infoAry['middles']) ?>
  <?php if (count($middles) == 1) : ?>
    <?php if (!PersonTable::middleNamesAreCompatible($middles[0], $entity['name_middle'])) : ?>
      <?php continue ?>
    <?php endif; ?>    
  <?php endif; ?>
  <?php $donorIds[] = $donorId ?>

  <tbody id="donor_group_<?php echo $donorId ?>">
    <tr class="text_small" style="margin-bottom: 0.5em;">
      <td>
        <?php if (count($infoAry['donations']) > 1) : ?>
        <a href="javascript:void(0);" onclick="swapView('<?php echo $donorId?>');"><?php echo image_tag('system/down-arrow.gif', 'class=donor-table-arrow') ?></a>
        <?php endif; ?>
        <input type="checkbox" id="donor_group_<?php echo $donorId ?>_checkbox" onclick="swapDonations(this, '<?php echo $donorId ?>');" />
      </td>
      <td style="font-size: 9px;"><?php echo implode('<br />', array_keys($infoAry['names'])) ?></td>
      <td style="font-size: 9px;"><?php echo implode('<br />', array_filter(array_keys($infoAry['orgs']))) ?></td>
      <td style="font-size: 9px;"><?php echo implode('<br />', array_keys($infoAry['addresses'])) ?></td>    
      <td style="font-size: 9px;">
        <?php foreach (array_slice($infoAry['image_ids'], 0, 5, true) as $imageId => $cycle) : ?>
          <?php if ($imageId) : ?>
            <?php echo link_to($imageId, 'http://images.nictusa.com/cgi-bin/fecimg/?' . $imageId, 'target=_new') ?>
            ('<?php echo substr($cycle, -2) ?>)
            <br />
          <?php endif; ?>
        <?php endforeach; ?>
      </td>
    </tr>
  </tbody>
  <tbody id="donations_group_<?php echo $donorId ?>" style="display: none;">
    <tr id="donations_hide_<?php echo $donorId ?>">
      <td>
        <input type="checkbox" id="donations_group_<?php echo $donorId ?>_checkbox" onclick="swapDonations(this, '<?php echo $donorId ?>');" />
        <a href="javascript:void(0);" onclick="swapView('<?php echo $donorId?>');"><?php echo image_tag('system/up-arrow.gif', 'class=donor-table-arrow') ?></a>
      </td>
      <td></td>
      <td></td>
      <td></td>
      <td></td>
    </tr>
  <?php foreach ($infoAry['donations'] as $key => $donation) : ?>
    <tr class="text_small donor-table-donation">
      <td>
        <input type="checkbox" name="trans[]" class="donor_<?php echo $donorId ?>_donation" value="<?php echo $donation['cycle'] . ':' . $donation['row_id'] ?>" onclick="checkDonorGroup('<?php echo $donorId ?>');" <?php echo in_array($donation['cycle'] . ':' . $donation['row_id'], $verified_trans) ? 'checked' : '' ?> />
        <?php if ($key == count($infoAry['donations']) - 1) : ?>
          <a href="javascript:void(0);" onclick="swapView('<?php echo $donorId?>');"><?php echo image_tag('system/up-arrow.gif', 'class=donor-table-arrow') ?></a>          
        <?php endif; ?>
      </td>
      <td style="font-size: 9px;"><?php echo $donation['donor_name'] ?></td>
      <td style="font-size: 9px;"><?php echo $donation['employer_raw'] ?></td>
      <td style="font-size: 9px;"><?php echo OsDonationTable::buildAddress($donation) ?></td>    
      <td style="font-size: 9px;">
        <?php if ($donation['fec_id']) : ?>
          <?php echo link_to($donation['fec_id'], 'http://images.nictusa.com/cgi-bin/fecimg/?' . $donation['fec_id'], 'target=_new') ?>
          ('<?php echo substr($donation['cycle'], -2) ?>)
        <?php endif; ?>
      </td>
    </tr>
  <?php endforeach; ?>
  </tbody>
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
  <?php echo button_to('Find More', EntityTable::getInternalUrl($entity, 'matchDonations', array('preprocess' => 1))) ?> 
<?php endif; ?>
<?php echo submit_tag('Cancel') ?>
</form>

<?php if (isset($remaining)) : ?>
 <br>
 <?php echo "<em>There are $remaining individuals left in your donation matching queue.</em>" ?>
<?php endif; ?>


<?php else : ?>

No matching donors found.

<ul>
  <li><strong><?php echo link_to('Go back', EntityTable::getInternalUrl($entity)) ?></strong> to <?php echo $entity['name'] ?>'s profile page.</li>
  <?php if (isset($next_entity) && $next_entity) : ?>
    <li><strong><?php echo link_to('Match donations', EntityTable::getInternalUrl($next_entity, 'matchDonations')) ?></strong> for another person.</li>
  <?php endif; ?>
  <?php if (!$sf_request->getParameter('preprocess')) : ?>
    <li><strong><?php echo link_to('Find more', EntityTable::getInternalUrl($entity, 'matchDonations', array('preprocess' => 1))) ?></strong> possible donor matches.</li>
  <?php endif; ?>
</ul>

<?php endif; ?>


<script>

function swapView(donorId)
{
  donorGroup = $('donor_group_' + donorId);
  donationsGroup = $('donations_group_' + donorId);
  
  if (donorGroup.style.display == 'none')
  {
    donorGroup.style.display = 'table-row-group';
    donationsGroup.style.display = 'none';
  }
  else
  {
    donorGroup.style.display = 'none';
    donationsGroup.style.display = 'table-row-group';  
  }
}

function swapDonations(master, donorId)
{
  $('donor_group_' + donorId + '_checkbox').checked = master.checked ? true : false;
  $('donations_group_' + donorId + '_checkbox').checked = master.checked ? true : false;
  
  $$('.donor_' + donorId + '_donation').each(function(input) {
    input.checked = master.checked;
  });
}

function checkDonorGroup(donorId, expand)
{
  if (typeof(expand) == 'undefined') { expand = false; }
  all = true;
  some = false;

  $$('.donor_' + donorId + '_donation').each(function(input) {
    all = all && input.checked;
    some = some || input.checked;
  });

  if (all)
  {
    $('donor_group_' + donorId + '_checkbox').checked = true;
    $('donations_group_' + donorId + '_checkbox').checked = true;  
  }
  else
  {
    $('donor_group_' + donorId + '_checkbox').checked = false;
    $('donations_group_' + donorId + '_checkbox').checked = false;
    
    if (expand && some)
    {
      swapView(donorId);
    }
  }
}

<?php foreach ($donorIds as $donorId) : ?>
checkDonorGroup('<?php echo $donorId ?>', true);
<?php endforeach; ?>

</script>