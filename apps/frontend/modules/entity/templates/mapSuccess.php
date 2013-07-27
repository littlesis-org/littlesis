<?php include_partial('entity/header', array('entity' => $entity, 'show_actions' => true)) ?>

<?php include_partial('global/section', array(
  'title' => 'Network Map'
)) ?>

<script>

var data = <?php echo $data ?>;

var width = 900,
    height = 500;

var svg = d3.select("#content").append("svg")
    .attr("width", width)
    .attr("height", height);

var force = d3.layout.force()
    .gravity(.1)
    .distance(150)
    .charge(-400)
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
    .style("stroke-width", function(d) { 
      return Math.sqrt(d.value); 
    });

link.append('text')
    .attr("text-anchor", "middle") 
    .text(function(d) {return d.label;}); 

/*
var edgepath = svg.selectAll(".edgepath")
    .data(data.links)
    .enter()
    .append('path')
    .attr({'d': function(d) {return 'M '+d.source.x+' '+d.source.y+' L '+ d.target.x +' '+d.target.y},
           'class':'edgepath',
           'id':function(d,i) {return 'edgepath'+i}})
    .style("pointer-events", "none");

var edgelabel = svg.selectAll(".edgelabel")
    .data(data.links)
    .enter()
    .append('text')
    .style("pointer-events", "none")
    .attr({'class':'edgelabel',
     'id':function(d,i){return 'edgelabel'+i},
     'dx':80,
     'dy':0,
     'font-size':12,
     'fill':'#aaa'});

edgelabel.append('textPath')
    .attr('xlink:href',function(d,i) {return '#edgepath'+i})
    .style("pointer-events", "none")
    .text(function(d,i){return d.label});
*/

/*
link.append("svg:text") 
    .text(function(d){ return d.text; }); 
*/

/*
link.append("text")
    .attr("x", function(d) { return (d.source.x + d.target.x) / 2; })
    .attr("y", function(d) { return (d.source.y + d.target.y) / 2; })
    .attr("text-anchor","middle")
    .text(function(d) { return d.text });
*/

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

var node = svg.selectAll(".node")
    .data(data.nodes)
    .enter().append("g")
    .attr("class", "node")
    .call(node_drag);

node.append("image")
    .attr("xlink:href", function(d) { return d.image } )
    .attr("x", -25)
    .attr("y", -25)
    .attr("width", 50)
    .attr("height", 50);

node.append("a")
    .attr("xlink:href", function(d) { return d.url })
    .append("text")
    .attr("dx", 0)
    .attr("dy", 46)
    .attr("text-anchor","middle")
    .text(function(d) { return d.name });
        
force.on("tick", tick);

function tick() {
  node.attr("transform", function(d) { return "translate(" + d.x + "," + d.y + ")"; });
  link.attr("transform", function(d) { return "translate(" + (d.source.x + d.target.x)/2 + "," + (d.source.y + d.target.y)/2 + ")"; });

  svg.selectAll(".line")
    .attr("x1", function(d) { return d.source.x - (d.source.x + d.target.x)/2; })
    .attr("y1", function(d) { return d.source.y - (d.source.y + d.target.y)/2; })
    .attr("x2", function(d) { return d.target.x - (d.source.x + d.target.x)/2; })
    .attr("y2", function(d) { return d.target.y - (d.source.y + d.target.y)/2; });
}

</script>