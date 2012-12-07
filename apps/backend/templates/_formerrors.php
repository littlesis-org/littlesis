<?php $formErrorCount = 0 ?>


<?php if (isset($form)) : ?>
  <?php $forms = is_array($form) ? $form : array($form) ?>
<?php endif; ?>

<?php if (isset($forms)) : ?>
  <?php foreach ($forms as $form) : ?>
    <?php $formErrorCount += count($form->getErrorSchema()->getErrors()) ?>
  <?php endforeach; ?>
<?php else : ?>
  <?php $forms = array() ?>
<?php endif; ?>


<?php $errors = $sf_request->getErrors() ?>


<?php if ($formErrorCount || count($errors)) : ?>

<div class="form_errors"
<?php if (isset($width)) : ?>
	<?php echo 'style="width: ' . $width . ';"' ?>
<?php endif; ?>
>
<?php echo image_tag(
	'system' . DIRECTORY_SEPARATOR . 'error.png',
	'align=absbottom'
) ?> There were errors with your submission:
<ul>
  <?php foreach ($forms as $form) : ?>

    <?php foreach ($form->getErrorSchema()->getErrors() as $name => $error) : ?>
      <li class="form_errors_message">
      <?php switch ((string) $error) :
        case 'Invalid.': ?>
          <?php echo $form[$name]->renderLabel() . ' is invalid' ?>
          <?php break; ?>
        <?php case 'Required.' : ?>
          <?php echo $form[$name]->renderLabel() . ' is required' ?>
          <?php break; ?>
        <?php default : ?>
          <?php echo $error ?>
          <?php break; ?>    
      <?php endswitch; ?>
      </li>
    <?php endforeach; ?>
	
	<?php endforeach; ?>
	
  <?php foreach ($errors as $error) : ?>
    <li class="form_errors_message">
      <?php echo $error ?>
    </li>
	<?php endforeach; ?>	

</ul>
</div>
<br />

<?php endif; ?>