<?php use_helper('LsText') ?>

<?php slot('header_text', 'News Articles') ?>

<span style="font-size: 16px;">
<?php if ($sf_request->getParameter('all')) : ?>
  <?php echo link_to('reviewed only', 'article/admin') ?>
<?php else : ?>
  <?php echo link_to('see all', 'article/admin?all=1') ?>
<?php endif; ?>
</span>

<br />
<br />


<?php foreach ($article_pager->execute() as $article) : ?>
  <?php include_partial('article/matchform', array('article' => $article)) ?>
<?php endforeach; ?>



<script>
function insertEntityLink(entityId, articleId)
{
  input = $('description-' + articleId);
  linkText = $('entity-link-' + entityId).value;
  
  input.value += linkText;
  input.focus();
}
</script>