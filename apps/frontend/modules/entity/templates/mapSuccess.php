<?php use_helper('LsText') ?>

<?php include_partial('entity/header', array('entity' => $entity, 'show_actions' => true)) ?>

<?php slot('header_subtext') ?>
<?php if ($entity['blurb'] && $entity['blurb'] != '') : ?>  

  <div id="entity_blurb_container">
  <div id="entity_blurb" onmouseover="showBlurbEdit();" onmouseout="hideBlurbEdit();">
  <span class="entity_blurb"><?php echo excerpt($entity['blurb'], 90) ?></span>  
  </div>
  </div>
<?php endif; ?>
<?php end_slot(); ?>

<!--
<?php include_partial('global/section', array(
  'title' => 'Network Map'
)) ?>
-->

<div id="svg_container"></div>

<script>

var data = <?php echo $data ?>;

var width = 900,
    height = 500;

var svg = d3.select("#svg_container").append("svg")
    .attr("id", "svg")
    .attr("width", width)
    .attr("height", height);

var force = d3.layout.force()
    .gravity(.3)
    .distance(150)
    .charge(-5000)
    .size([width, height])
    .nodes(data.nodes)
    .links(data.links)
    .start();

var link = svg.selectAll(".link")
    .data(data.links)
    .enter().append("g")
    .attr("class", "link");
    
link.append("line")
    .attr("class", "line")
    .attr("opacity", 0.6)
    .style("stroke-width", function(d) { 
      return Math.sqrt(d.value) * 10; 
    });

link.append('text')
    .attr("dy", function(d) { return Math.sqrt(d.value) * 10 / 2 - 1; })
    .attr("text-anchor", "middle") 
    .text(function(d) {return d.label;}); 

var node_drag = d3.behavior.drag()
    .on("dragstart", dragstart)
    .on("drag", dragmove)
    .on("dragend", dragend);

function dragstart(d, i) {
    force.stop() // stops the force auto positioning before you start dragging
}

function dragmove(d, i) {
    d.px += d3.event.dx;
    d.py += d3.event.dy;
    d.x += d3.event.dx;
    d.y += d3.event.dy; 
    tick(); // this is the key to make it work together with updating both px,py,x,y on d !
}

function dragend(d, i) {
    d.fixed = true; // of course set the node to fixed so the force doesn't include the node in its auto positioning stuff
    tick();
    force.resume();
}

var node_background_opacity = 0.6;
var node_background_color = "#eee";
var node_background_corner_radius = 5;

var node = svg.selectAll(".node")
    .data(data.nodes)
    .enter().append("g")
    .attr("class", "node")
    .call(node_drag);

var has_image = function(d) {
  return d.image.indexOf("anon") == -1;
}

node.append("rect")
    .attr("fill", node_background_color)
    .attr("opacity", node_background_opacity)
    .attr("rx", node_background_corner_radius)
    .attr("ry", node_background_corner_radius)
    .attr("width", function(d) { return has_image(d) ? 58 : 43; })
    .attr("height", function(d) { return has_image(d) ? 58 : 43; })
    .attr("x", function(d) { return has_image(d) ? -29 : -21; })
    .attr("y", function(d) { return has_image(d) ? -29 : -29; });

node.append("image")
    .attr("class", "image")
    .attr("opacity", function(d) { return has_image(d) ? 1 : 0.5; })
    .attr("xlink:href", function(d) { return d.image } )
    .attr("x", function(d) { return has_image(d) ? -25 : -17; })
    .attr("y", function(d) { return has_image(d) ? -25 : -25; })
    .attr("width", function(d) { return has_image(d) ? 50 : 35; })
    .attr("height", function(d) { return has_image(d) ? 50 : 35; });

var a = node.append("a")
    .attr("xlink:href", function(d) { return d.url })
    .attr("title", function(d) { return d.description });
    
a.append("text")
    .attr("dx", 0)
    .attr("dy", function(d) { return has_image(d) ? 40 : 25; })
    .attr("text-anchor","middle")
    .text(function(d) { return d.name.replace(/^(.{8,}[\s-]+).+$/, "$1").trim(); });

a.append("text")
    .attr("dx", 0)
    .attr("dy", function(d) { return has_image(d) ? 57 : 42; })
    .attr("text-anchor","middle")
    .text(function(d) {
      var second_part = d.name.replace(/^.{8,}[\s-]+(.+)$/, "$1");
      return d.name == second_part ? "" : second_part;
    });

node.filter(function(d) { return d.name.match(/^(.{8,})[\s-]+.+$/) != null; })
    .insert("rect", ":first-child")
    .attr("fill", node_background_color)
    .attr("opacity", node_background_opacity)
    .attr("rx", node_background_corner_radius)
    .attr("ry", node_background_corner_radius)
    .attr("x", function(d) { 
      return -$(this).closest(".node").find("text:nth-child(2)").width()/2 - 3;
    })
    .attr("y", function(d) { 
      var image_offset = $(this).closest(".node").find("image").attr("height")/2;
      var text_offset = $(this).closest(".node").find("text").height();
      var extra_offset = has_image(d) ? 4 : -3;
      return image_offset + text_offset + extra_offset;
    })
    .attr("width", function(d) { 
      return $(this).closest(".node").find("text:nth-child(2)").width() + 6;
    })
    .attr("height", function(d) { 
      return $(this).closest(".node").find("text:nth-child(2)").height() + 4;
    });

node.insert("rect", ":first-child")
    .attr("fill", node_background_color)
    .attr("opacity", node_background_opacity)
    .attr("rx", node_background_corner_radius)
    .attr("ry", node_background_corner_radius)
    .attr("x", function(d) { 
      return -$(this).closest(".node").find("text").width()/2 - 3;
    })
    .attr("y", function(d) { 
      var image_offset = $(this).closest(".node").find("image").attr("height")/2;
      var extra_offset = has_image(d) ? 1 : -6;
      return image_offset + extra_offset;
    })
    .attr("width", function(d) { 
      return $(this).closest(".node").find("text").width() + 6;
    })
    .attr("height", function(d) { 
      return $(this).closest(".node").find("text").height() + 4;
    });

force.on("tick", tick);

function tick() {
  node.attr("transform", function(d) { return "translate(" + d.x + "," + d.y + ")"; });
  link.attr("transform", function(d) { return "translate(" + (d.source.x + d.target.x)/2 + "," + (d.source.y + d.target.y)/2 + ")"; });

  svg.selectAll(".line")
    .attr("x1", function(d) { return d.source.x - (d.source.x + d.target.x)/2; })
    .attr("y1", function(d) { return d.source.y - (d.source.y + d.target.y)/2; })
    .attr("x2", function(d) { return d.target.x - (d.source.x + d.target.x)/2; })
    .attr("y2", function(d) { return d.target.y - (d.source.y + d.target.y)/2; });
    
  svg.selectAll(".link text")
    .attr("transform", function(d) { 
      var x_delta = d.target.x - d.source.x;
      var y_delta = d.target.y - d.source.y;
      var angle = Math.atan2(y_delta, x_delta) * 180 / Math.PI;
      if (d.source.x >= d.target.x) {
        angle += 180;
      }
      return "rotate(" + angle + ")";
    });
}

</script>