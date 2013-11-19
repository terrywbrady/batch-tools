<?php
header("Content-type: text");
include '../web/util.php';

if (false) {
  $json_a = util::json_get("http://demo.dspace.org/rest/communities/?expand=subCommunities");

  //var_dump($json_a);
  foreach($json_a as $k=>$comm) {
	showComm(0, $comm);
	if (!isset($comm["subcommunities"])) continue;
	foreach($comm["subcommunities"] as $scomm) {
		showComm($comm["id"], $scomm);
	}
  }
	
} else {
  $json_a = util::json_get("http://demo.dspace.org/rest/communities/?expand=all");

  //var_dump($json_a);
  foreach($json_a as $k=>$comm) {
	showColl($comm);
	if (!isset($comm["subcommunities"])) continue;
	foreach($comm["subcommunities"] as $scomm) {
		showColl($scomm);
	}
  }
	
}

function showComm($pid, $comm) {
	echo $comm["id"] . "\t" . $comm["name"] . "\t" . $comm["handle"] . "\t" . $pid . "\n";
	//var_dump($comm);
}

function showColl($comm) {
	if ($comm["collections"] != null) {
		foreach($comm["collections"] as $coll) {
			showComm($comm["id"], $coll);
		}
	}
}

?>

