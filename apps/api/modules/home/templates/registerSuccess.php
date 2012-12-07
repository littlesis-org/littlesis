<?php use_helper('Form') ?>

<h2>Register for a LittleSis API key</h2>

<?php if ($created == true) : ?>
<p>We've received your request for a LittleSis API key. We'll contact you with instructions shortly. In the meantime, read the API <?php echo link_to('documentation', '@documentation') ?>.</p>
<?php else : ?>

<p>A key is required for all requests to the LittleSis API. Each key owner is allowed 10,000 requests per day. We will use the email you supply to send you your API key and contact you in the future about any changes to the API.</p>

<form action="<?php echo url_for('home/register') ?>" method="POST">
<table class="api_form">

<?php if (count($user_form->getErrorSchema()->getErrors())) : ?>
  <tr>
    <td colspan="2" class="api_form_error">
      <ul>
      <?php foreach ($user_form->getErrorSchema()->getErrors() as $name => $error) : ?>
        <li>
          <?php switch ((string) $error) :
            case 'Invalid.': ?>
              <?php echo $user_form[$name]->renderLabel() . ' is invalid' ?>
              <?php break; ?>
            <?php case 'Required.' : ?>
              <?php echo $user_form[$name]->renderLabel() . ' is required' ?>
              <?php break; ?>
            <?php default : ?>
              <?php echo $error ?>
              <?php break; ?>    
          <?php endswitch; ?>
        </li>
      <?php endforeach; ?>
      </ul>
    </td>
  </tr>
<?php endif; ?>

<?php foreach (array('name_first', 'name_last', 'email', 'reason') as $key) : ?>
  <tr>
    <td class="api_form_label"><?php echo $user_form[$key]->renderLabel() ?></td>
    <td class="api_form_field"><?php echo $user_form[$key]->render() ?></td>
  </tr>
<?php endforeach; ?>
  <tr>
    <td class="api_form_label"><?php echo $user_form['user_agrees']->renderLabel() ?></td>
    <td class="api_form_field">
        <ol>
          <li>I understand data on LittleSis may not be 100% accurate, and that LittleSis should not be considered an original source of information. When accuracy counts, I should verify all data using the references supplied by LittleSis and other public resources.</li>
          <li>LittleSis data is licensed under a <a rel="license" href="http://creativecommons.org/licenses/by-sa/3.0/us/">Creative Commons Attribution-ShareAlike 3.0 United States License</a>. I will credit the LittleSis API in published works that use it.</li>
          <li>If I anticipate needing more than 10,000 requests per day, I will contact the LittleSis team in advance.</li>
          <li>If I want an instance of the full LittleSis data set, or a large slice of it, I will contact the LittleSis team for an SQL dump instead of using the API.</li>
        </ol>
        <?php echo $user_form['user_agrees']->render() ?> I agree to the above terms of use
    </td>
  </tr>
  <tr>
    <td class="api_form_label"><?php echo $user_form['captcha']->renderLabel() ?></td>
    <td class="api_form_field"><?php echo $user_form['captcha']->render() ?></td>
  </tr>
  <tr>
    <td></td>
    <td><?php echo submit_tag('Register') ?></td>
  </tr>
</table>
</form>

<?php endif; ?>