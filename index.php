<?php 
	$q = isset($_REQUEST["q"]) ? $_REQUEST["q"] : "MVS";
	$title = array("MVS" => "MVS Price List", "AES" => "AES Price List", "KIT" => "MVS Kit Price List");
	include_once 'neogeo_util.php';
	$nu = new NeoGeoUtil("object");
	$mvspl = $nu->getPriceList(($q == "KIT" ? "MVS" : $q), ($q == "KIT" ? true : false));
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<title><?php echo $title[$q]; ?></title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<link type="text/css" rel="stylesheet" href="http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.23/themes/smoothness/jquery-ui.css" media="screen" />
	<link type="text/css" rel="stylesheet" href="css/neogeo.css" media="screen" />
	<script src="//ajax.googleapis.com/ajax/libs/jquery/1.8.2/jquery.min.js"></script>
 	<script src="//ajax.googleapis.com/ajax/libs/jqueryui/1.8.23/jquery-ui.min.js"></script>
	<script type="text/javascript" src="js/tblsort.js"></script>
	<script type="text/javascript" src="js/jquery.flot.js"></script>
	<script type="text/javascript" src="js/jquery.flot.time.js"></script>
	<script type='text/javascript'>
    (function ($) {
        $.fn.styleTable = function (options) {
            var defaults = {
                css: 'styleTable'
            };
            options = $.extend(defaults, options);

            return this.each(function () {

                input = $(this);
                input.addClass(options.css);

                input.find("tr").live('mouseover mouseout', function (event) {
                    if (event.type == 'mouseover') {
                        $(this).children("td").addClass("ui-state-hover");
                    } else {
                        $(this).children("td").removeClass("ui-state-hover");
                    }
                });

                input.find("th").addClass("ui-state-default");
                input.find("td").addClass("ui-widget-content");

                input.find("tr").each(function () {
                    $(this).children("td:not(:first)").addClass("first");
                    $(this).children("th:not(:first)").addClass("first");
                });
            });
        };
    })(jQuery);	

    var fl_opts = { 
    	    series: {
    	        lines: { show: true , fill: true, fillColor: { colors: ["#9999FF","#0000EE"] } },
    	        points: { show: true, fill: false }
    	    },
    	    xaxis: {
    	        show: true,
    	        position: "bottom",
    	        mode: "time",
    	        timezone: null, 
    	        timeformat: "%b/%y",

    	        color: null,
    	        tickColor: null,
    	        font: null,
    	        minTickSize: [1, "month"],
    	        min: null,
    	        max: null,
    	        autoscaleMargin: null
    	    },
    	    yaxis: {
    	        show: true,
    	        position: "left",
    	        mode: null,
    	        timezone: null, 

    	        color: null,
    	        tickColor: null,
    	        font: null,

    	        min: null,
    	        max: null,
    	        autoscaleMargin: null
    	    },
    	    grid: {
    	        show: true,
    	        aboveData: false,
    	        color: "#9999FF",
    	        backgroundColor: null
    	    }    	    	    
    	    
       	};
    
    $(document).ready(function() {
		$("#plist").styleTable();
		//$("ul.menu li a").addClass("ui-state-default");
		$("ul.menu li a").button();
		$(".lnk_plot").click(function(e) {
			var getdata = { q: "getGraphData", sys: $("#sys").val(), ngh: $(this).parents("tr").attr("id") };
			$.ajax({
				  type: "GET",
				  url: "ajax.php",
				  data: getdata,
				  dataType: "json",
				  error: function(xhr, status, error) {
					  alert(xhr.responseText);
				  }
			}).done(function(data) {
				if (data.length > 0) {
					var g = $("#graph");
					g.css({ top: e.pageY, left: e.pageX });
					$.plot(g, [ data ], fl_opts);
				}
				else {
					alert("no sales data for this game");
				}
			});
			return false;
		});
	});
	</script>
</head>
<body>
	<ul class='menu ui-widget-content'>
		<li><a href='index.php?q=MVS'>MVS</a></li>
		<li><a href='index.php?q=KIT'>MVS Kit</a></li>
		<li><a href='index.php?q=AES'>AES</a></li>
		<li class="right"><a href="admin/admin.php">Adm</a></li>
		</ul>
	<h1 class='hdr'><?php echo $title[$q]; ?></h1>
	<table id='plist' class='std sortable'>
		<thead>
			<tr>
				<th><a href='#'>NGH</a></th><th><a href='#'>Title</a></th><th><a href='#'>Developer</a></th>
				<th><a href='#'>Low</a></th><th><a href='#'>High</a></th><th><a href='#'>Avg</a></th>
			</tr>
		</thead>
		<tbody>
			<?php 
			foreach ($mvspl as $p) {
				echo "<tr id='" . $p->ngh . "'><td>" . $p->ngh . "</td>" . 
				"<td style='width: 250px'><a href='#' class='lnk_plot'>" . $p->title . "</a></td>" .
				"<td>" . $p->developer . "</td><td>" . $p->low . "</td>" . 
				"<td>" . $p->high . "</td><td><b>" . $p->avg . "</b></td></tr>";
			}
			?>
		</tbody>
	</table>
	
	<div id="graph" style="width: 400px; height: 250px; position: absolute; background: #000"></div>
	<input type="hidden" id="sys" value="<?php echo $q; ?>" />
</body>
</html>
