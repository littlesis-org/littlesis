<?php slot('header_text', $map["title"] ? $map["title"] : "Map " . $map["id"]) ?>
<?php slot('header_link', "map/view?id=" . $map["id"]) ?>