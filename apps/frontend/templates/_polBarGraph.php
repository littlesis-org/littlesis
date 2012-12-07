<script type="text/javascript">
				//Width and height
			var w = 540;
			var leftMargin = 170;
			var rightMargin = 80;
			var topMargin = 10;
			var bottomMargin = 10;
			
			var graphHeight = h-topMargin-bottomMargin;
			var graphWidth = w-leftMargin-rightMargin;
			
			var dataSet = [<?php echo implode(",",$dataSet) ?>];
			var dataLabels = ["<?php echo implode('", "',$dataLabels) ?>"];
			var dataUrls = ["<?php echo implode('", "',$dataUrls) ?>"];
			
			var barPadding = 7;
			var barHeight = 10;
			var barInterval = barPadding + barHeight;
			var h = topMargin + bottomMargin + dataSet.length*barInterval;
			
			var labelPadding = 5;
		
		  var numFormat = d3.format(",.0f");
		  var maxLen = 40;
						
			var xScale = d3.scale.linear()
            .domain([0, d3.max(dataSet, function(d) { return d; })])
            .range([0, graphWidth]);
                     
			//Create SVG element
			var svg = d3.select("<?php echo $graphName?>")
						.append("svg")
						.attr("width", w)
						.attr("height", h);

			svg.selectAll("text")
			   .data(dataLabels)
			   .enter()
			   .append("a")
			   .append("text")
			   .text(function(d) {
			   		return d;
			   })
			   .attr("x", labelPadding)
			   .attr("y", function(d, i) {
			   		return (i * barInterval + topMargin + barHeight-2);
			   })
			   .text(function(d)
			   {
			      if(d.length > 27)
			      {
			        return d.substr(0,25) + "...";
			      }
			      else return d;
			   })
			   .attr("font-family", "sans-serif")
			   .attr("font-size", "11px")
			   .attr("fill", "#000088");
			   
			svg.selectAll("rect")
			   .data(dataSet)
			   .enter()
			   .append("rect")
			   .attr("y", function(d, i) {
			   		return (i * barInterval + topMargin);
			   })
			   .attr("x", leftMargin)
			   .attr("width", function(d) {
            return xScale(d);
          })
			   .attr("height", barHeight)
			   .attr("fill", function(d) {
					return "#92B796";
			   });
			   
			
			svg.selectAll("#dataPoints")
			   .data(dataSet)
			   .enter()
			   .append("text")
			   .text(function(d) {
			      return "$" + numFormat(d)
			    })
			   .attr("y", function(d, i) {
			   		return (i * barInterval + topMargin + barHeight-2);
			     })
			   .attr("x", function(d) {
			      return xScale(d) + leftMargin + 5;
			   })
			   .attr("font-family", "sans-serif")
			   .attr("font-size", "10px")
			   .attr("fill", "gray");

			   
      svg.selectAll("a")
         .data(dataUrls)
         .attr("xlink:href",function(d) {
            return d;
         });

</script>