<?php include_partial('map/header', array('map' => $map)) ?>

<form action="<?php echo url_for("map/edit?id=" . $map->id) ?>" method="POST">
<strong>Title:</strong> <input type="text" name="title" value="<?php echo $map->title ?>" size="50" />
<br />
<br />
<strong>Description:</strong><br />
<textarea name="description" cols="100" rows="10"x><?php echo $map->description ?></textarea>
<br />
<br />
<input type="submit" value="Save" />
</form>