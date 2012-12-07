
<?php echo form_tag('operation/upload', 'multipart=true') ?>
  <?php echo input_file_tag('file') ?>
  <br />
  <br />
  <?php echo submit_tag('Add') ?>
</form>

<div style="padding:1em; width=400px">

  <p>Once you upload the file you will be taken to a form to finish the process.
  
  <p>File should be a tab-delimited csv or text file.  
  
  <p>Each row = 1 individual.
  
  <p>Required column headers: name (Lloyd Blankfein) & affiliation1 (eg Goldman Sachs)
  
  <p>Optional column headers:  affiliation1_extensions (eg School or NonProfit, assumed to be Business) and affiliation1_title (eg CEO).
  
  <p>Each affiliation is assumed to be a position.  If the affiliation is not set the person will not be added.

</div>