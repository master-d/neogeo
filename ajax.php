<?php
include_once 'neogeo_util.php';

	$q = isset($_REQUEST["q"]) ? $_REQUEST["q"] : null;
	$resp = array("success" => false, "msg" => "unknown operation");
	$nau = new NeoGeoUtil("object");
	
	try {
		if ($q == "getGraphData") {
			$resp = $nau->getGraphData($_REQUEST["ngh"], $_REQUEST["sys"]);
		}
	}
	catch (Exception $e) {
		$resp = array("success" => false, "msg" => $e->getTraceAsString());
	}
	echo json_encode($resp, JSON_NUMERIC_CHECK);
	
?>