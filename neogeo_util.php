<?php
include_once 'dbconn.php';


// use this class to interact with the database
class NeoGeoUtil {

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
		"from games g left join auctions a on a.ngh = g.ngh and a.valid='C' " .
		"where a.sys=? and a.kit=? " .
		"group by g.ngh, g.title";
		$results = $this->conn->selectQuery($sql,array($system, ($kit ? 'Y' : 'N')));
		return $this->getRetValue($results);
	}
	public function getGraphData($ngh, $sys) {
		$sql = "select unix_timestamp(a.auction_date)*1000 as ats, a.price from auctions a " .
				"where a.sys=? and a.ngh=? and a.valid='C' order by a.auction_date asc";
		$results = $this->conn->selectQuery($sql, array($sys, $ngh));
		$json_arr = array();
		foreach ($results as $res) {
			//$json .= " [ " . $res->ats . ", " . $res->price . " ],";
			array_push($json_arr, array($res->ats, $res->price));
		}
		return $json_arr;
	}
	
	
	private function getRetValue($results) {
		if ($this->ret_type == "json")
			return json_encode($results);
		else 
			return $results;
	}
	
	
}

?>
