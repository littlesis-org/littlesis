<option value="" selected></option>
<?php foreach ($categories as $category) : ?>
<option value="<?php echo $category['name'] ?>"><?php echo $category['name'] ?></option>
<?php endforeach; ?>
