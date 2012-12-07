<h3>TOOLBOX > Parse NYS Donation Data</h3>


<form action="parseNyDonations" method="POST">

<table>
  <?php include_partial('reference/required', array(
    'form' => $reference_form,
    'hide_bypass' => true
  )) ?>
</table>

<?php if ($sf_request->isMethod('post')) : ?>
Parsed text:<br><br>
<textarea rows="100" cols="200"><?php echo $parsed_text ?></textarea><br>

<?php endif; ?>
<br><br>
<?php echo submit_tag('Submit') ?>

</form>