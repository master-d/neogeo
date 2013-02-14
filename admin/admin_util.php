<?php
include_once '../dbconn.php';


// use this class to interact with the database
class NeoGeoAdminUtil {

	private $conn;
	private $ret_type = "object";
	private $page_size = 50;
	
	public function __construct($ret_type) {
		$this->conn = new DbConn();
		$this->ret_type = $ret_type;
	}
	public function closeConn() {
		$this->conn = null;
	}
	
	public function getPriceList($system, $kit) {
		$sql = "select g.ngh,g.title,g.developer, round(avg(a.price),2) as avg, min(a.price) as low, max(a.price) as high " .
		"from games g left join auctions a on a.ngh = g.ngh " .
		"where a.sys=? and a.kit=? and a.valid='C' " .
		"group by g.ngh, g.title";
		$results = $this->conn->selectQuery($sql,array($system, ($kit ? 'Y' : 'N')));
		return $this->getRetValue($results);
	}
	
	public function getUnvalidatedAuctions($sys) {
		$sql = "select g.ngh,g.title,a.img_url,a.itemid,a.kit,a.sys, a.auction_title, a.price as price " .
		"from games g join auctions a on g.ngh = a.ngh and a.valid='Y' and a.sys=? order by a.price desc";
		$results = $this->conn->selectQuery($sql, array($sys));
		return $results;
	}
	public function getDeletedAuctions($sys) {
		$sql = "select g.ngh,g.title,a.img_url,a.itemid,a.kit,a.sys, a.auction_title, a.price as price " .
		"from games g join auctions a on g.ngh = a.ngh and a.valid='N' and a.sys=? order by a.price desc";
		$results = $this->conn->selectQuery($sql, array($sys));
		return $results;
	}
	public function getGraphData($ngh, $sys) {
		$sql = "select unix_timestamp(a.auction_date)*1000 as ts, a.price from auctions a " .
				"where a.sys=? and a.ngh=?";
		$results = $this->conn->selectQuery($sql, array($sys, $ngh));
		return results;
	}
	
	
	public function validateAuction($id) {
		$sql = "update auctions set valid='C' where itemid=?";
		$results = $this->conn->insupQuery($sql, array($id));
		return  array("success" => true, "msg" => "Successfully deleted auction #$id", "id" => $id);
	}
	public function deleteAuction($id) {
		$sql = "update auctions set valid='N' where itemid=?";
		$results = $this->conn->insupQuery($sql, array($id));
		return  array("success" => true, "msg" => "Successfully deleted auction #$id");
	}
	public function updateAuction($id, $kit, $sys) {
		$sql = "update auctions set kit=?, sys=? where itemid=?";
		$results = $this->conn->insupQuery($sql, array($kit, $sys, $id));
		return  array("success" => true, "msg" => "Successfully update auction #$id");
	}
	
	private function getRetValue($results) {
		if ($this->ret_type == "json")
			return json_encode($results);
		else 
			return $results;
	}
	
	
}

?>
