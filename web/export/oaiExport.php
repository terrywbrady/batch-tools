<?php
/*
User form for initiating the move of a collection to another community.  Note: in order to properly re-index the repository, 
DSpace will need to be taken offline after running this operation.
Author: Terry Brady, Georgetown University Libraries

License information is contained below.

Copyright (c) 2013, Georgetown University Libraries All rights reserved.

Redistribution and use in source and binary forms, with or without modification, are permitted provided that the following conditions are met:

Redistributions of source code must retain the above copyright notice, this list of conditions and the following disclaimer. 
in binary form must reproduce the above copyright notice, this list of conditions and the following disclaimer in the documentation and/or other materials 
provided with the distribution. THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, 
BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. 
IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES 
(INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) 
HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) 
ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
*/
include '../header.php';

$CUSTOM = custom::instance();
$CUSTOM->getCommunityInit()->initCommunities();
$CUSTOM->getCommunityInit()->initCollections();

$status = "";
testArgs();
header('Content-type: text/html; charset=UTF-8');
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<?php 
$header = new LitHeader("Export Metadata for a Collection or Community");
$header->litPageHeader();
?>
</head>
<body>
<?php $header->litHeader(array());?>
<div id="formReindex">
<form method="POST" action="" onsubmit="jobQueue();return true;">
<p>Select a community or collection to export</p>
<div id="status"><?php echo $status?></div>
<?php drawFormats(util::getPostArg("format",""));?>
<?php collection::getCollectionHandleWidget(util::getPostArg("coll",""), "coll", " to export*");?>
<?php collection::getSubcommunityWidget(util::getPostArg("comm",""), "comm", " to export*");?>
<p align="center">
	<input id="exportSubmit" type="submit" title="Submit Form"/>
</p>
<p><em>* One of the 2 selection fields is required</em></p>
</form>
</div>
<?php $header->litFooter();?>
</body>
</html>

<?php 

function getFormats() {
  $val = array("zzz");
  try {
    $req = "/oai/request?verb=ListMetadataFormats";
    //error_reporting(0);
    $ret = file_get_contents($req);
    $xml = new DOMDocument();
    $stat = $xml->loadXML($ret);
    if (!$stat) throw exception("no data");
    $nl = $xml->getElementsByTagName("metadataPrefix");
    for($i=0; $i<$nl->length; $i++) {
    	$el = $nl->get($i);
        array_push($ret, $el->getValue());	
    }  
  } catch(exception $ex) {
  	echo $ex;
  }
  

  if (count($val) == 0) {
    array_push($val, "oai_dc");	
    array_push($val, "marc");	  	  	
  }
  
  return $val;	
}


function drawFormats($format) {
	echo "<label for='format'>Select the desired export format</label>";
	echo "<select id='format' name='format'>";
	echo "<option/>";
	foreach(getFormats() as $k) {
		$sel = ($format == $k) ? "selected" : "";
		echo "<option val='{$k}' {$sel}>{$k}</option>";
	}
	echo "</select>";
}



function testArgs(){
	global $status;
	$CUSTOM = custom::instance();
	
	if (count($_POST) == 0) return;
	$coll = util::getPostArg("coll","");
	$comm = util::getPostArg("comm","");
	$format = util::getPostArg("format","");
	
	$set = "";

    $statColl = $CUSTOM->validateCollection($coll); 
    $statComm = $CUSTOM->validateCollection($comm);

	if ($statColl == "") {
  	    $set = "col_" . str_replace("/", "_",$coll);
	} else if ($statComm == "") {
  	    $set = "com_" . str_replace("/", "_",$comm);
	} else {
		$status = "A valid collection or community must be selected";
		return;
	}
	
	if ($format == "") {
		$status = "A format must be selected";
		return;
	}

    header('Content-type: application/xml; charset=UTF-8');
    echo "<foo>Data will go here {$set} {$format}</foo>";
    exit;
}

?>