<?php if (isset($entity)) : ?>
<?php $tabs = array(
  'Connections Map' => array(
    'url' => EntityTable::getInternalUrl($entity, "map"),
    'actions' => array("map")
  ),
  'Interlocks Map' => array(
    'url' => EntityTable::getInternalUrl($entity, 'interlocksMap'),
    'actions' => array('interlocksMap')
  ),
  'Saved Maps' => array(
    'url' => "map/list",
    'actions' => array()
  )
); ?>

<?php include_partial('global/tabs', array(
  'tabs' => $tabs
)) ?>
<br />
<?php endif; ?>

<div id="netmap"></div>
<div id="netmap_controls">

<input id="netmap_prune" type="button" value="prune" /><br />
<input id="netmap_wheel" type="button" value="wheel" /><br />
<input id="netmap_grid" type="button" value="grid" /><br />
<input id="netmap_shuffle" type="button" value="shuffle" /><br />
<input id="netmap_short_force" type="button" value="force" /><br />
<input id="netmap_rezoom" type="button" value="rezoom" /><br />

<br />

Cats:
<select multiple id="netmap_cat_ids" size="10">
  <option value="1">Pos</option>
  <option value="2">Edu</option>
  <option value="3">Mem</option>
  <option value="4">Fam</option>
  <option value="5">Don</option>
  <option value="6">Trn</option>
  <option value="7">Lob</option>
  <option value="8">Soc</option>
  <option value="9">Pro</option>
  <option value="10">Own</option>
</select>

<br />
<br />

Current:
<input id="netmap_current_only" type="checkbox" /><br />

<?php if ($sf_user->hasCredential('admin')) : ?>
<br />
<?php if (isset($id)) : ?>
  <?php echo button_to('edit', "map/edit?id=" . $id) ?><br />
<?php endif; ?>
<input id="netmap_save" type="button" value="save" /><br />
<?php endif; ?>

<div id="netmap_add_entity">
<div id="netmap_add_entity_hide"><a id="netmap_add_entity_hide_link" onclick="$('#netmap_add_entity').css('display', 'none'); return false;">hide</a></div>
<?php include_partial('global/section', array('title' => 'Add Entity')) ?>
<form id="netmap_add_entity_form">
  <input id="netmap_add_entity_search" type="text" />
  <input id="netmap_add_entity_button" type="submit" value="search" />
</form>
<div id="netmap_add_entity_results"></div>
</div>

<div id="netmap_add_related_entities">
<div id="netmap_add_related_entities_hide"><a id="netmap_add_related_entities_hide_link" onclick="$('#netmap_add_related_entities').css('display', 'none'); return false;">hide</a></div>
<input id="netmap_add_related_entities_entity_id" type="hidden" />
<?php include_partial('global/section', array('title' => 'Add Related')) ?>
Num: <input id="netmap_add_related_entities_num" type="text" value="10" size="2" /><br />
Cats:<br />
<select multiple id="netmap_add_related_entities_cat_ids" size="10">
  <option value="1">Pos</option>
  <option value="2">Edu</option>
  <option value="3">Mem</option>
  <option value="4">Fam</option>
  <option value="5">Don</option>
  <option value="6">Trn</option>
  <option value="7">Lob</option>
  <option value="8">Soc</option>
  <option value="9">Pro</option>
  <option value="10">Own</option>
</select>
<input id="netmap_add_related_entities_button" type="button" value="add" />
</div>

<br />

<div id="netmap_control_key">
CLICK: select<br />
<br />
SCROLL: zoom<br />
<br />
D: delete<br />
A: add<br />
</div>

<script>
$("#netmap_current_only").on("change", function() {
  if ($(this).is(':checked')) {
    netmap.limit_to_current();
  } else {
    netmap.show_all_rels();
  }
});

$("#netmap_cat_ids").on("change", function() {
  var cat_ids = $(this).val() == null ? 
    [] : $(this).val().map(function(i) { return Number(i); });
  if (cat_ids.length == 0) {
    netmap.show_all_rels();
  } else {
    netmap.limit_to_cats(cat_ids);
  }
});

<?php if ($sf_user->hasCredential('admin')) : ?>
$("#netmap_save").on("click", function() {
  netmap.remove_hidden_rels();

  var id = netmap.get_network_map_id();
  if (id) {
    var url = '<?php echo url_for("map/update?id=999999") ?>'.replace("999999", id);
  } else {
    var url = '<?php echo url_for("map/create") ?>';
  }

  $.ajax({
    url: url,
    type: "POST",
    data: netmap.data_for_save(),
    success: function(data) { 
      window.location.href = '<?php echo url_for("map/view?id=999999") ?>'.replace("999999", data.id);    
    },
    error: function() { 
      alert("There was an error saving the map"); 
    },
    dataType: "json"
  })  
});
<?php endif; ?>

$("#netmap_reload").on("click", function() {
  netmap.reload_map();
});

$("#netmap_rezoom").on("click", function() {
  netmap.reset_zoom();
});

$("#netmap_prune").on("click", function() {
  netmap.prune();
});

$("#netmap_wheel").on("click", function() {
  netmap.wheel();
});

$("#netmap_grid").on("click", function() {
  netmap.grid();
});

$("#netmap_shuffle").on("click", function() {
  netmap.shuffle();
});

$("#netmap_short_force").on("click", function() {
  netmap.one_time_force();
});

$("#netmap_add_entity_form").on("submit", function() {
  var q = $("#netmap_add_entity_search").val();
  netmap.search_entities(q, function(entities) {
    var results = $("#netmap_add_entity_results");
    results.text("");

    if (entities.length == 0)
    {
      results.html("<strong>No results found.</strong>");
    }

    $(entities).each(function(i, e) {
      var result = $('<div class="netmap_add_entity_result" /></div>');
      add = $('<a>add</a>');
      add.data("entity_id", e.id)
      add.on("click", function(e) {
        position = [e.pageX - $("#svg").offset().left, e.pageY - $("#svg").offset().top]
        netmap.add_entity($(this).data("entity_id"), position);
        $("#netmap_add_entity").css("display", "none");
      })
      result.append(add);
      result.append("&nbsp;&nbsp;");
      var name = $('<a>' + e.name + '</a>');
      name.attr("href", e.url);
      name.attr("target", "_blank");
      result.append(name);
      results.append(result);
    });
  });  
  return false;
});

$("#netmap_add_related_entities_button").on("click", function() {
  var entity_id = $("#netmap_add_related_entities_entity_id").val();
  var cat_ids = $("#netmap_add_related_entities_cat_ids").val();    
  var num = $("#netmap_add_related_entities_num").val();    
  netmap.add_related_entities(entity_id, num, cat_ids);
  $("#netmap_add_related_entities").css("display", "none");
});
</script>
</div>
