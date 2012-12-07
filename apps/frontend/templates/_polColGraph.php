
		  <style type="text/css">

.axis path {
   fill: none;
   stroke: #B2B2B2;
   stroke-width: 1;
   shape-rendering: crispEdges;
}

.axis line {
    fill: none;
    stroke: #B2B2B2;
    stroke-width: 1;
    shape-rendering: crispEdges;
}

.axis text {
    font-family: sans-serif;
    font-size: 11px;
    fill: gray;
}

.keytext {
    font-family: sans-serif;
    font-size: 11px;
    fill: gray;
  }
  
.cycletext {
    font-family: sans-serif;
    font-size: 14px;

  }

</style>

<script type="text/javascript">
				//Width and height
			var w = 580;
			var h = 185;
			
			//graph margins (external)
      var leftMargin = 70;
      var rightMargin = 160;
      var topMargin = 15;
      var bottomMargin = 40;

//graph size and margins (internal)

      var graphSidePadding = 4;
      var graphTopPadding = 20;
      var xAxisPadding = 16;
      var yAxisPadding = 10;
      


			var graphHeight = h-topMargin-bottomMargin;
			var graphWidth = w-leftMargin-rightMargin;
			
			var dataSet1 = [<?php echo implode(",",$dataSet1) ?>];
      var dataSet2 = [<?php echo implode(",",$dataSet2) ?>];
			var cycles = ["<?php echo implode('", "',$cycles) ?>"];
			
			var barPadding = 7;
			var barWidth = 10;
			var betweenBars = 2;
			var barInterval = barPadding + barWidth*2 + betweenBars
			
			var labelPadding = 5;
		
		  var numFormat = d3.format(",.0f");
		  var maxLen = 40;
		  var max = d3.max(dataSet1);
		  if (max < d3.max(dataSet2))
		  {
		    max = d3.max(dataSet2);
		  }
						
			var yScale = d3.scale.linear()
            .domain([0, max])
            .range([0, graphHeight]);
                     
      var yScaleAxis = d3.scale.linear()
            .domain([0, max])
            .range([graphHeight, 0]);
            
      var repColor = "#c21717";
      var demColor = "#1e4868";
            
			//Create SVG element
			var svgCol = d3.select("<?php echo $graphName?>")
						.append("svg")
						.attr("width", w)
						.attr("height", h);

			svgCol.selectAll("text")
			   .data(cycles)
			   .enter()
			   .append("text")
			   .text(function(d) {
			   		return d;
			   })
			   .attr("y", topMargin + graphHeight + xAxisPadding)
			   .attr("x", function(d, i) {
			   		return (i * barInterval + leftMargin + graphSidePadding);
			   })
			   .attr("font-family", "sans-serif")
			   .attr("font-size", "11px")
			   .attr("fill", "gray");
			   
			var series1 = svgCol.selectAll("series-1")
        .data(dataSet1)
        .enter()
        .append("rect")
        .attr("y", function(d) {
            return topMargin + graphHeight - yScale(d);
        })
        .attr("x", function(d, i) {
          return (i * barInterval + leftMargin + graphSidePadding);
        })
        .attr("width", barWidth)
        .attr("height", function(d) {
          return (yScale(d));
        })
        .attr("fill", "#1e4868");
			
			var series2 = svgCol.selectAll("series-2")
        .data(dataSet2)
        .enter()
        .append("rect")
        .attr("y", function(d) {
            return topMargin + (graphHeight - yScale(d));
        })
        .attr("x", function(d, i) {
          return (i * barInterval + leftMargin + graphSidePadding + betweenBars + barWidth);
        })
        .attr("width", barWidth)
        .attr("height", function(d) {
          return (yScale(d));
        })
        .attr("fill", "#c21717");
			
			svgCol.append("line")
			    .attr("x1",leftMargin)
			    .attr("y1",topMargin+graphHeight)
			    .attr("x2",w-rightMargin)
			    .attr("y2",topMargin+graphHeight)
			    .attr("stroke","#B2B2B2")
			    .attr("fill","none")
			    .attr("stroke-width","1")
			    .attr("shape-rendering","crispEdges");
			    
			var yAxis = d3.svg.axis()
                  .scale(yScaleAxis)
                  .orient("left")
                  .ticks(5);
      
      yAxis.tickSize(yAxis.tickSize(),0);
                  
		  svgCol.append("g")
        .attr("class", "axis")
        .attr("transform", "translate(" + leftMargin + "," + topMargin + ")")
        .call(yAxis);
        
      //setup the pie chart container
      svgCol.append("g")
          .attr("id", "pie")
          .attr("transform", "translate(" + [w - 130, 20] + ")");
      
      var r = 40;
      
      var arc = d3.svg.arc()
        .innerRadius(0)
        .outerRadius(r);
      var pie = d3.layout.pie()
       .value(function(d) { return d.value });
      
      var colors = [demColor, repColor];
      
      series1.on("mouseover", function(d,i) {
          var pie_data = [
            {"value":d, "label":"Democrat"},
            {"value":dataSet2[i], "label":"Republican"}
          ];
          cycleText.text(cycles[i]);
          onMousePie(pie_data);
      });
      series2.on("mouseover", function(d,i) {
          var pie_data = [
            {"value":dataSet1[i], "label":"Democrat"},
            {"value":d, "label":"Republican"}
          ];
          cycleText.text(cycles[i]);
          onMousePie(pie_data);
      });


    function onMousePie (pie_data)
    {

        var apple = svgCol.select("#pie")
            .data([pie_data]);
        var sum = pie_data[0].value + pie_data[1].value;
        slicepaths.data(pie)
            .attr("d", arc)
            .attr("fill", function(c, j) { return colors[j]; });
        
        keyLabels1.data(pie)
            .text(function(d, i) { return  formatAsPercentage(pie_data[i].value / sum) + " to " + pie_data[i].label + "s"; });
            
        keyLabels2.data(pie)
            .text(function(d, i) { return "$" + formatAsNum(pie_data[i].value); })
        
        mouseOverText.text("");
    }  
    
    var ds1Sum = eval(dataSet1.join('+'));
    var ds2Sum = eval(dataSet2.join('+'));
    var pie_data = [
      {"value":ds1Sum, "label":"Democrat","cycle":"1990-2012"},
      {"value":ds2Sum, "label":"Republican","cycle":"1990-2012"}
    ];
          
    
    var keyLabelX = -33;
    var keyLabelY = [60,100];
    var keyLineHt = 14;
    var keyCircleRadius = 5;
    var keyCircleCx = -44;
    var keyCircleCy = [60,100];
    var formatAsPercentage = d3.format(".1%");
    var formatAsNum = d3.format(",");
    
    var apple = svgCol.select("#pie")
            .data([pie_data]);
    var sum = pie_data[0].value + pie_data[1].value;
    
    var arcs = apple.selectAll("g.slice")
        .data(pie)
        .enter().append("svg:g")
          .attr("class", "slice")
          .attr("transform", "translate(" + [r, r] + ")");
          
    var slicepaths = arcs.append("svg:path")
        .attr("d", arc)
        .attr("class","paths")
        .attr("fill", function(c, j) { return colors[j]; });

    var keyLabels1 = arcs.append("svg:text")                                     
            .attr("x",keyLabelX)
            .attr("y",function(d,i){
              return keyLabelY[i];
             })
            .attr("class","keytext")                      
            .text(function(d, i) { return  formatAsPercentage(pie_data[i].value / sum) + " to " + pie_data[i].label + "s"; });
            
    var keyLabels2 = arcs.append("svg:text")
            .attr("x",keyLabelX)
            .attr("y",function(d,i){
              return (keyLabelY[i]+keyLineHt);
             })
            .attr("class","keytext")
            .text(function(d, i) { return "$" + formatAsNum(pie_data[i].value); })
    
    var keyCircles = arcs.append("svg:circle")
            .attr("cx",keyCircleCx)
            .attr("cy",function(d,i) {
              return (keyCircleCy[i]); })
            .attr("r",keyCircleRadius)
            .attr("fill",function(d,i) { return colors[i]; });
       
    var cycleText = apple.append("svg:text")
            .attr("x",100 )
            .attr("y",-9)
            .attr("text-anchor","end")
            .attr("class","cycletext")
            .text(cycles[0] + " - " + cycles[cycles.length-1]);

    var resetText = apple.append("a")
            .append("text")
            .attr("x",100)
            .attr("y",5)
            .attr("text-anchor","end")
            .attr("font-family", "sans-serif")
			      .attr("font-size", "10px")
            .text("(reset)")
            .attr("fill", "#000088");
    
    resetText.on("click",function() {
        cycleText.text(cycles[0] + " - " + cycles[cycles.length-1]);
        onMousePie(pie_data);});
        
    var mouseOverText = svgCol.append("text")
            .attr("x",100)
            .attr("y",40)
            .attr("font-size","11px")
            .attr("fill","#b2b2b2")
            .text("(mouse over bars to see cycle figures)");
</script>