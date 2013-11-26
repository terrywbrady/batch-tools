<?php
/*
User form for initiating a bulk ingest.  User must have already uploaded ingestion folders to a server-accessible folder.
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
include '../web/header.php';

$CUSTOM = custom::instance();
$CUSTOM->getCommunityInit()->initCommunities();
$CUSTOM->getCommunityInit()->initCollections();

$defuser =  $CUSTOM->getDefuser();
$ingestLoc =  $CUSTOM->getIngestLoc();

$status = "";
testArgs();
$user = $CUSTOM->getCurrentUser();
header('Content-type: text/html; charset=UTF-8');
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<?php 
$header = new LitHeader("Bulk Ingest");
$header->litPageHeader();
?>
</head>
<body>
<?php $header->litHeader(array());?>
<div id="formIngest">
<form method="POST" action="" onsubmit="jobQueue();return true;">
<p>This process will trigger a bulk ingest of content.</p>
<p>Assumptions:</p>
<ol>
<li>Content has been prepared into ingest folders</li>
<li>Ingest folder metadata has been validated</li>
<li>Ingest folders have been transferred to the staging area on the server</li>
</ol>
<div id="status"><?php echo $status?></div>
<?php collection::getCollectionWidget(util::getPostArg("community",""), util::getPostArg("collection",""));?>
<p>
<fieldset class="loc">
<legend>Ingest Folder Location * </legend>
<p>
  <label for="loc">Folder Location (Staging)</label>
  <br/>&#160;&#160;
  <?php echo $ingestLoc?><input type="text" id="loc" name="loc" size="70" value="<?php echo util::getPostArg("loc","")?>"/>
  <button type="button" onclick="doloc()" id="locbutton">...</button>
  <div id="locpick" style="display:none">
    <label for="locsel">Select the server folder to ingest</label>
    <button type="button" id="locclose" onclick="$('#locpick').hide()">close</button>
    </br>
    <select name="locsel" id="locsel" size="10" onclick="dolocSelect()">
    </select>
  </div>
</p>
</fieldset>
<p>
  <label for="user">User Id</label>
  <input type="text" id="user" name="user" readonly value="<?php echo $user?>" />
  <label for="domain" title="User's e-mail domain'">@</label>
  <select id="domain" name="domain">
    <?php echo $CUSTOM->getDomainOptions()?>
  </select>
</p>
</p>
<p align="center">
	<input id="ingestSubmit" type="submit" title="Submit Job" disabled/>
</p>
</form>
</div>

<?php $header->litFooter();?>
</body>
</html>
<?php 
function testArgs(){
	global $status;
	global $ingestLoc;
	$CUSTOM = custom::instance();
	$dspaceBatch = $CUSTOM->getDspaceBatch();
	$mroot =  $CUSTOM->getMapRoot();
	$bgindicator =  $CUSTOM->getBgindicator();
	
	if (count($_POST) == 0) return;
	$user = util::getPostArg("user","");
	if (preg_match("|^[a-z0-9]+$|", $user) == 0) {
		$status = "Invalid User: " . $user;
		return;
	}
	
	$domain = util::getPostArg("domain","");
	$status = custom::instance()->validateDomain($domain);
	if ($status != "") return;
	
	$coll = util::getPostArg("collection","");
	$status = custom::instance()->validateCollection($coll);
	if ($status != "") return;
	

	$loc  = util::getPostArg("loc","");

	if (preg_match("|\.\.|", $loc) == 1) {
		$status = "Location [" . $loc . "] may not contain '..'.";
		return;
	}
	if (preg_match("|;|", $loc) == 1) {
		$status = "Location [" . $loc . "] may not contain ';'.";
		return;
	}
	$batch = date("Ymd_H.i.s");
	$mapfile = $mroot . $batch;
	
	$user = escapeshellarg($user.$domain);
	$coll = escapeshellarg($coll);
	$loc = escapeshellarg($ingestLoc . $loc);
	$mapfile = escapeshellarg($mapfile);

	$u = escapeshellarg(preg_replace("@.*$","",$user));
	$cmd = <<< HERE
{$u} gu-ingest {$user} {$coll} {$loc} {$mapfile}
HERE;
    
    //echo($dspaceBatch . " " .$cmd);
    exec($dspaceBatch . " " . $cmd . " " . $bgindicator);
    header("Location: ../web/queue.php");
}
?>
