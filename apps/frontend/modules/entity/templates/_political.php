
<?php use_helper('Javascript') ?>
<?php use_helper('Pager') ?>

<form action="<?php echo url_for(EntityTable::getInternalUrl($entity, 'political')) ?>" method="GET">

<em>Showing US campaign contribution data for cycles <?php echo select_tag('start_cycle', options_for_select(array_combine($all_cycles,$all_cycles), $start_cycle),array('id'=>'startselect','onchange' => "setOptions(this.value,'endselect');")) ?> thru <?php echo select_tag('end_cycle', options_for_select(array_combine($all_cycles,$all_cycles),$end_cycle),array('id'=>'endselect','onchange' => "setOptions(this.value,'startselect');")) ?> <?php echo submit_tag('go',array("style" =>"background-color: #eee; color: #008")) ?></em> <a href="#about">learn more</a>
</form><br><br>
<!-- IF ENTITY IS PERSON -->


<?php if ($entity['primary_ext'] == 'Person') : ?>


<?php if ($repTotal > 0 || $demTotal > 0) : ?>

<?php include_partial('global/section', array(
  'title' => 'Campaign Contributions to Democrats v. Republicans',
  'pointer' => 'Federal campaign contributions by ' . $entity['name']
)) ?>

<div id="donation-trends">
</div>

<?php include_partial('global/polColGraph', array(
  'dataSet1' =>  $demAmts,
  'dataSet2' => $repAmts,
  'graphName' => "#donation-trends",
  'cycles' => $cycles
)) ?>



<?php endif; ?>

<?php if (count($personRecipients)) : ?>


<?php include_partial('global/section', array(
  'title' => 'Politicians Supported',
  'pointer' => 'US politicians supported by ' . $entity['name']
)) ?>

<div id="top-person-recipients">
</div>

<?php $personRecipients = LsArray::flip($personRecipients) ?>

<?php $dataLabels = array_map(function($a,$b) { if($b) {return $a . ' (' . $b . ')';} else return $a;}, array_slice($personRecipients['recipient_name'],0,10),array_slice($personRecipients['party'],0,10)); ?>

<?php include_partial('global/polBarGraph', array(
  'dataSet' =>  array_slice($personRecipients['recipient_amount'],0,10),
  'graphName' => "#top-person-recipients",
  'dataLabels' => $dataLabels,
  'dataUrls' => array_slice($personRecipients['recipient_url'],0,10)
)) ?>

<?php endif; ?>

<?php if (count($orgRecipients)) : ?>


<?php include_partial('global/section', array(
  'title' => 'Political Organizations Supported',
  'pointer' => 'US Political organizations (such as PACs and party committees) supported by ' . $entity['name']
)) ?>

<div id="top-org-recipients">
</div>

<?php $orgRecipients = LsArray::flip($orgRecipients) ?>

<?php include_partial('global/polBarGraph', array(
  'dataSet' =>  array_slice($orgRecipients['recipient_amount'],0,10),
  'graphName' => "#top-org-recipients",
  'dataLabels' => array_slice($orgRecipients['recipient_name'],0,10),
  'dataUrls' => array_slice($orgRecipients['recipient_url'],0,10)
)) ?>

<?php endif;?>





<!-- IF ENTITY IS ORG -->
<?php else : ?>


<?php if ($repTotal > 0 || $demTotal > 0) : ?>

<?php include_partial('global/section', array(
  'title' => 'Campaign Contributions to Democrats v. Republicans',
  'pointer' => 'Federal campaign contributions by people with positions at ' . $entity['name']
)) ?>

<div id="donation-trends">
</div>

<?php include_partial('global/polColGraph', array(
  'dataSet1' =>  $demAmts,
  'dataSet2' => $repAmts,
  'graphName' => "#donation-trends",
  'cycles' => $cycles
)) ?>



<?php endif; ?>

<?php if (count($personRecipients)) : ?>


<?php include_partial('global/section', array(
  'title' => 'Politicians Supported',
  'pointer' => 'US politicians supported by people with positions at ' . $entity['name']
)) ?>

<div id="top-person-recipients">
</div>

<?php $personRecipients = LsArray::flip($personRecipients) ?>

<?php include_partial('global/polBarGraph', array(
  'dataSet' =>  array_slice($personRecipients['recipient_amount'],0,10),
    'graphName' => "#top-person-recipients",
  'dataLabels' => array_slice($personRecipients['recipient_name'],0,10),
  'dataUrls' => array_slice($personRecipients['recipient_url'],0,10)
)) ?>

<?php endif; ?>

<?php if (count($orgRecipients)) : ?>


<?php include_partial('global/section', array(
  'title' => 'Political Organizations Supported',
  'pointer' => 'US Political organizations (such as PACs and party committees) supported by people with positions at ' . $entity['name']
)) ?>

<div id="top-org-recipients">
</div>

<?php $orgRecipients = LsArray::flip($orgRecipients) ?>

<?php include_partial('global/polBarGraph', array(
  'dataSet' =>  array_slice($orgRecipients['recipient_amount'],0,10),
  'graphName' => "#top-org-recipients",
  'dataLabels' => array_slice($orgRecipients['recipient_name'],0,10),
  'dataUrls' => array_slice($orgRecipients['recipient_url'],0,10)
)) ?>

<?php endif;?>

<?php if (count($donors)) : ?>

<?php include_partial('global/section', array(
  'title' => 'Top Donors',
  'pointer' => 'Top donors to US politicians/PACs with positions/memberships at ' . $entity['name']
)) ?>

<div id="top-donors">
</div>

<?php $donors = LsArray::flip($donors) ?>

<?php include_partial('global/polBarGraph', array(
  'dataSet' =>  array_slice($donors['donor_amount'],0,10),
  'graphName' => "#top-donors",
  'dataLabels' => array_slice($donors['donor_name'],0,10),
  'dataUrls' => array_slice($donors['donor_url'],0,10)
)) ?>



<?php endif;?>

<?php endif; ?>

<a name="about"></a>
<?php include_partial('global/section', array(
  'title' => 'About the data'
)) ?>
<br><br>
<div>This data is compiled using <a href="http://opensecrets.org">OpenSecrets</a> bulk data downloads. LittleSis analysts match federal campaign contributions given by individuals in our database using the <a href="http://littlesis.org/videos#donation-matching">donation matching tool</a>.<br><br>Individual profile pages show giving information for the individual profiled; org pages show an aggregate analysis of giving information for individuals with positions or memberships at those orgs. Note that this information may differ substantially from data found at <a href="http://influenceexplorer.com">InfluenceExplorer</a> or OpenSecrets, because it only includes donations for individuals in our database who have been donor-matched by an analyst.</div>


<script>

function setOptions(value,selId)
{
  var sel = $(selId);
  
  var cycles = [<?php echo implode(",",$all_cycles)?>];
  var current = cycles[sel.selectedIndex];

  for(var i=0; i < sel.options.length; i++) 
  {
    var option = sel.options[i];

    if (selId == 'endselect' && cycles[i] < value)
    {
      option.disabled=true;
    }
    else if (selId == 'startselect' && cycles[i] > value)
    {
      option.disabled=true;
    }
    else if ((selId == 'endselect' && current > value) || (selId== 'startselect' && current < value))
    {
      option.disabled=false;
    }
  }
  if(selId == 'endselect')
  {
    if (current < value)
    {
      sel.selectedIndex = sel.options.length-1;      
    }
  }
  else
  {
    if (current > value)
    {
      sel.selectedIndex = 0;
    }
  }
}

setOptions(<?php echo $cycles[0]?>,'endselect');
setOptions(<?php echo $cycles[count($cycles)-1]?>,'startselect');

</script>
