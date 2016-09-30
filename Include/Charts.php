<?php

// $chartId - Id for the chart, using which it will be recognized in the HTML page. Each chart on the page needs to have a unique Id.
// $chartWidth - Intended width for the chart (in pixels)
// $chartHeight - Intended height for the chart (in pixels)
function renderChart($chartType, $dataJson, $chartId, $chartWidth, $chartHeight) {

    $chartIdDiv = "Div$chartId";

    // create a string for outputting by the caller
	$render_chart = <<<RENDERCHART
	<!-- START Script Block for Chart $chartId -->
	<canvas id="$chartIdDiv" style="width:{$chartWidth}px; height:{$chartHeight}px"></canvas>
	<script type="text/javascript">	 
    var chart_$chartId = document.getElementById("$chartIdDiv");
    var option_$chartId = {}; 
    var data_$chartId = $dataJson;
    var myChart_$chartId = new Chart(chart_$chartId, {
        type: '$chartType',
        data: data_$chartId,
        options:option_$chartId, 
    });

	</script>	
	<!-- END Script Block for Chart $chartId -->
RENDERCHART;

  return $render_chart;
}
