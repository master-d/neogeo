<?php 
	$q = isset($_REQUEST["q"]) ? $_REQUEST["q"] : "INVALID";
	$sys = isset($_REQUEST["sys"]) ? $_REQUEST["sys"] : "MVS";
	$title = array("INVALID" => "$sys Unvalidated Auctions", "DELETED" => "$sys deleted Auctions");
	include_once 'admin_util.php';
	$nu = new NeoGeoAdminUtil("object");
	$al = array();
	if ($q == "INVALID")
		$al = $nu->getUnvalidatedAuctions($sys);
	else if ($q == "DELETED") 
		$al = $nu->getDeletedAuctions($sys);
		
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<title><?php echo $title[$q]; ?></title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link type="text/css" rel="stylesheet" href="http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.23/themes/smoothness/jquery-ui.css" media="screen" />
		<link type="text/css" rel="stylesheet" href="../css/neogeo.css" media="screen" />
	<script src="//ajax.googleapis.com/ajax/libs/jquery/1.8.2/jquery.min.js"></script>
 	<script src="//ajax.googleapis.com/ajax/libs/jqueryui/1.8.23/jquery-ui.min.js"></script>
 	<script type="text/javascript" src="../js/tbl_editor.js"></script>
	<script type="text/javascript" src="../js/tblsort.js"></script>
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

    $(document).ready(function() {
		$("#plist").styleTable();
		//$("ul.menu li a").addClass("ui-state-default");
		$("ul.menu li a").button();
		$("#plist").tblEditor();
		$(".lnk_validate").click(function() {
			var getdata = { q: "validateAuction", id: $(this).parents("tr").attr("id") };
			$.ajax({
				  type: "GET",
				  url: "ajax.php",
				  data: getdata,
				  dataType: "json",
				  error: function(xhr, status, error) {
					  alert(xhr.responseText);
				  }
			}).done(function(data) {
				if (data.success) {
					// remove the row
					$("#" + data.id).fadeOut(function() { $(this).remove(); });
				}
				else {
					alert("failed to validate item #" + data.id);
				}
			});
			return false;
		});
	});
	</script>
</head>
<body>
	<ul class='menu ui-widget-content'>
		<li><a href='admin.php?q=INVALID&sys=MVS'>MVS Unvalidated</a></li>
		<li><a href='admin.php?q=DELETED&sys=MVS'>MVS Deleted</a></li>
		<li><a href='admin.php?q=INVALID&sys=AES'>AES Unvalidated</a></li>
		<li><a href='admin.php?q=DELETED&sys=AES'>AES Deleted</a></li>
		<li class="right"><a href="../index.php">Home</a></li>
	</ul>
	<h1 class='hdr'><?php echo $title[$q]; ?></h1>
	<table id='plist' class='std sortable'>
		<thead>
			<tr>
				<th data-type='none'><a href='#'>NGH</a></th><th data-type='none'><a href='#'>Cart</a></th>
				<th data-type='none'><a href='#'>Auction Title</a></th><th data-type='none'><a href='#'>Auction ID</a></th>
				<th data-type='none'><a href='#'>Img</a></th><th data-type='none'><a href='#'>Price</a></th>
				<th data-ddl='[["Y","Y"],["N","N"]]'><a href='#'>Kit</a></th>
				<th data-ddl='[["MVS","MVS"],["AES","AES"]]'><a href='#'>System</a></th>
			</tr>
		</thead>
		<tbody>
			<?php 
			foreach ($al as $p) {
				echo "<tr id='" . $p->itemid . "'>" .
				"<td><a href='#' class='lnk_validate'>" . $p->ngh . "</a></td>" . 
				"<td style='width: 250px'>" . $p->title . "</td>" .
						"<td style='width: 250px'>" . $p->auction_title . "</td>" .
				"<td><a href='http://www.ebay.com/itm/" . $p->itemid . "?pt=LH_DefaultDomain_0' target='BLANK'>" . $p->itemid . "</a></td><td>" . 
				"<img src='" . $p->img_url . "'/></td>" .
				"<td>" . $p->price . "</td>" . 
				"<td>" . $p->kit . "</td><td>" . $p->sys . "</td></tr>";
			}
			?>
		</tbody>
	</table>
</body>
</html>
