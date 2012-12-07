<?php if ($featured_post) : ?>

<div class="blog_feed inner-box">

<span style="font-size: 18px; color: #666;"><strong>FROM THE BLOG</strong></span>
&nbsp;<?php echo link_to(image_tag('system/feed-14.png', 'style="border: 0;"'), $feed_link) ?>
<br />
<br />
<span class="blog_title"><?php echo link_to($featured_post->getTitle(), $featured_post->getLink()) ?></span>
&nbsp;
<span class="blog_date"><nobr><?php echo date('M d', $featured_post->getPubDate()) ?></nobr></span>
<br />
<?php $content = preg_split('#(<p>)?<span id="more-\d+"></span>(</p>|<br />)#i', $featured_post->getContent()) ?>
<?php if (count($content) > 1) : ?>
  <?php $content = preg_replace('#(width|height)="\d*"#i', '', $content[0]) ?>
  <?php $content = str_replace('<img', '<img style="width: 200px; float: right; display:inline; margin:0 0 1em 1em;"', $content) ?>
  <?php echo preg_replace('#(littlesis\.org/(person|org|list)/\d+/[^>]+>)([^<]+)(</a>)#i', '\\1<strong>\\3</strong>\\4', $content) ?>
  <strong><?php echo link_to('Read more &raquo;', $featured_post->getLink()) ?></strong>
<?php else : ?>
  <?php echo preg_replace('#(littlesis\.org/(person|org|list)/\d+/[^>]+>)([^<]+)(</a>)#i', '\\1<strong>\\3</strong>\\4', $content[0]) ?>  
<?php endif; ?>


<div style="font-size: 17px; margin-top: 0.5em;"><strong>More from the Blog</strong></div>

<ul class="blog_list">
<?php foreach ($posts as $post) : ?>
  <li>
    &raquo;
    <span class="blog_title"><?php echo link_to($post->getTitle(), $post->getLink()) ?></span>
    &nbsp;
    <span class="text_small blog_date"><?php echo date('M d', $post->getPubDate()) ?></span>
  </li>
<?php endforeach; ?>

  <li>
    &raquo; 
    <span class="blog_title"><?php echo link_to('More...', $more_link) ?></span>
  </li>

</ul>

</div>

</div>

<br />
<br />

<?php endif; ?>