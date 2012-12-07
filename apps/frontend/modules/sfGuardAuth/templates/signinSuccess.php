<?php slot('header_text', 'Login') ?>
<?php slot('header_link', '@sf_guard_signin') ?>


<?php $requestPath = $sf_request->getParameter('module') . '/' . $sf_request->getParameter('target', $sf_request->getParameter('action')) ?>
<?php if ($requestPath != sfConfig::get('sf_login_module') . '/' . sfConfig::get('sf_login_action')) : ?>
You must log in or 
<strong><?php echo link_to('sign up', 'home/join') ?></strong> 
to view the page you requested.
<br />
<br />
<?php endif; ?>


<?php include_partial('global/formerrors', array('form' => $form)) ?>

<?php if (!$referer = $sf_request->getParameter('referer')) : ?>
  <?php if (!$referer = $sf_request->getReferer()) : ?>
    <?php $queryStr = http_build_query($sf_request->getGetParameters()) ?>
    <?php $referer = substr($sf_request->getPathInfo(), 1) . ($queryStr ? '?' . $queryStr : '') ?>
  <?php endif; ?>
<?php endif; ?>

<?php include_partial('global/login', array(
  'form' => $form, 
  'request_path' => $referer,
  'lean' => $sf_request->getParameter('no_layout') ? true : null
)) ?>


<script>
document.getElementById('signin_username').focus();
</script>