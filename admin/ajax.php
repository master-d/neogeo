<?php
include_once 'admin_util.php';

	$q = isset($_REQUEST["q"]) ? $_REQUEST["q"] : null;
	$tbl = isset($_REQUEST["tbl"]) ? $_REQUEST["tbl"] : null;
	$resp = array("success" => false, "msg" => "unknown operation");
	$nau = new NeoGeoAdminUtil("object");
	
	try {
		if ($tbl == "plist") {
			if ($q == "save") {
				$resp = $nau->updateAuction($_REQUEST["id"], $_REQUEST["kit"], $_REQUEST["system"]);
			}
			else if ($q == "insert") {
				$resp = array("success" => false, "msg" => "Operation not supported");
			}
			else if ($q == "delete") {
				$resp = $nau->deleteAuction($_REQUEST["id"]);
			}
		}
		else if ($q == "validateAuction") {
			$resp = $nau->validateAuction($_REQUEST["id"]);
		}
	}
	catch (Exception $e) {
		$resp = array("success" => false, "msg" => $e->getTraceAsString());
	}
	echo json_encode($resp);
?>