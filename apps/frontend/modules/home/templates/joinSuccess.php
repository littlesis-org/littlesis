<?php slot('header_text', 'Get Involved!') ?>

<?php slot('rightcol') ?>
<?php if (!cache('stats', 600)) : ?>
  <?php include_component('home', 'stats')?>
  <?php cache_save() ?>
<?php endif; ?>
<?php end_slot() ?>

<span class="about-text">
<p><strong>LittleSis is bringing together a community of citizens who believe in transparency and accountability where it matters most.</strong> Our task is to study, document, and expose the social networks that have our democracy in a stranglehold, so that grassroots efforts can more effectively challenge their claim to power.</p> 

<p><strong>We're looking for researchers, programmers, artists and organizers to lend a hand.</strong> If you want to get involved, <?php echo link_to('send us a note', '@contact') ?>, join our email list, <a href="#signup">sign up</a> to become a LittleSis analyst and start adding information to the site, or check out our <?php echo link_to('help pages', 'http://littlesis.org/help') ?> for information on how to use the site.</p>

<br />

<?php include_partial('global/section', array('title' => 'Join Our Announcement List')) ?>

<p style="text-align: center;"><a name="signup"></a>
<form method="post" action="http://oi.vresp.com?fid=ba4140c884" target="vr_optin_popup" onsubmit="window.open( 'http://www.verticalresponse.com', 'vr_optin_popup', 'scrollbars=yes,width=600,height=450' ); return true;" >
    &nbsp; &nbsp; Email: &nbsp;
    <input name="email_address" style="position: relative; top: -1px;"/>&nbsp;
    <input type="submit" value="Join" style="font-size: 12px;"/><br/>
</form>
</p>

<br />
<br />

<?php include_partial('global/section', array('title' => 'Become An Analyst')) ?>
</a>

<?php if ($sf_request->getParameter('group') == 'bubblebarons') : ?>

<div class="intro-box inner-box" style="font-size: 15px; margin-top: 15px;">
Welcome AlterNet community, and thanks for your interest in joining the Bubble Barons investigation!
Use the form below to sign up, and we'll be in touch with further instructions in the next 24 hours. 
</div>

<?php endif; ?>

<p>LittleSis presents an exciting opportunity:  editors (called "analysts" on LittleSis) are developing an unprecedented, authoritative database of key relationships between powerful Americans.  Sign up to edit the profile pages of your (least) favorite fatcats.</p>

<p>To request an account, please provide all of the following information. We promise not to share it with anyone, ever!</p>
</span>

<?php include_partial('global/formerrors', array('form' => $user_form)) ?>

<form id="join-form" action="nospam.php" method="POST">
<?php echo input_hidden_tag('code', $profile->invitation_code) ?>
<?php echo $user_form['_csrf_token'] ?>

<?php if (isset($group)) : ?>

  <?php if ($group = Doctrine::getTable('sfGuardGroup')->findOneByName($group)) : ?>
   <em>
   &raquo;&nbsp;Once you sign up, you will be able to join the <?php echo group_link($group) ?> research group.
   <br />
   &raquo;&nbsp;Already an analyst? <?php echo link_to('Login and join', 'http://littlesis.org/login?referer=group/' . $group) ?>
   </em>
   <br /><br />
    <?php echo input_hidden_tag('group', $group) ?>
  <?php endif; ?>
<?php endif; ?>

<table class="form_table" width="500">
  <?php if ($profile->home_network_id != LsListTable::US_NETWORK_ID) : ?>  
    <?php include_partial('global/formfield', array(
      'field' => $user_form['home_network_id'], 
      'help' => 'default local network for new data you contribute'
    )) ?>
  <?php else : ?>
    <?php echo input_hidden_tag('user[home_network_id]', LsListTable::US_NETWORK_ID) ?>
  <?php endif; ?>

  <tr id="join-username">
    <td class="form_label"><label for="user_username">Username</label></td>
    <td class="form_field"><input type="text" name="user[username]"/> (required)</td>
  </tr>

  <?php include_partial('global/formfield', array(
    'field' => $user_form['name_first'], 
    'help' => 'your name and email will not be public'
  )) ?>
  <?php include_partial('global/formfield', array('field' => $user_form['name_last'])) ?>
  <?php include_partial('global/formfield', array(
    'field' => $user_form['email'],
    'help' => 'for logging in and important notices'
  )) ?>
  <?php include_partial('global/formfield', array(
    'field' => $user_form['public_name'], 
    'help' => '4-30 chars; only letters, numbers, and periods'
  )) ?>
  <?php include_partial('global/formfield', array(
    'field' => $user_form['pw1'], 
    'help' => '6-20 chars, only letters and numbers'
  )) ?>
  <?php include_partial('global/formfield', array('field' => $user_form['pw2'])) ?>

  <?php if ($is_invited) : ?>
  	<?php echo input_hidden_tag('user[reason]', 'User was invited.') ?>
  <?php else : ?>
    <?php include_partial('global/formfield', array('field' => $user_form['reason'])) ?>
  <?php endif; ?>

  <!--
  <?php include_partial('global/formspacer') ?>
	<tr>
		<td colspan="2" class="text_big">
		  LittleSis is committed to keeping its data accurate and relevant, so we need to make sure
		  you're not a spambot and understand the guidelines for adding new content. Hence the
		  terms of use and spam test below. Thanks for understanding!
		</td>
	</tr>
  <?php include_partial('global/formspacer') ?>
  -->

	<tr>
		<td class="form_label <?php if ($user_form['accepts_terms']->hasError()) { echo ' form_label_error'; } ?>"><?php echo $user_form['accepts_terms']->renderLabel() ?></td>
		<td>
      <div class="terms_of_use">
         I understand that LittleSis's mission is to track people and groups with inordinate wealth, 
         influence on public policy, and access to government officials. 
         As a LittleSis analyst it is my responsibility to ensure that information I contribute is 
         relevant to the site's mission, accurate, and documented by publicly available original 
         sources on the internet. The LittleSis staff can revoke my editing privileges or disable 
         my account if they believe I am not following these guidelines in good faith.
        <br />        
        <br />
        <?php echo $user_form['accepts_terms']->render() ?> I accept the above terms of use
      </div>
		</td>
	</tr>

  <?php include_partial('global/formfield', array('field' => $user_form['captcha'])) ?>

	<tr>
		<td></td>
		<td class="submit">
			<?php echo submit_tag('Submit') ?>
		</td>
	</tr>
</table>

</form>
</span>
<script type="text/javascript">
  $(document).ready(function() {
    var waitTime = $(".form_errors").length == 0 ? 5000 : 0;

    setTimeout(function() {
      var form = $("#join-form")[0];
      form.setAttribute("action", "<?php echo url_for('home/join') ?>");
    }, waitTime);
  });
</script>