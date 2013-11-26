<?php
/*
DSpace Tools Landing Page

Note that several paths to institution-specific resources would need to be set.  This code assumes that presence of PROD, TEST, DEV and AUX servers.
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
include 'header.php';
include 'query/queries.php';

$CUSTOM = custom::instance();
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<?php 
$header = new LitHeader("Home");
$header->litPageHeader();
?>
</head>
<body>
<?php 
$header->litHeader(array());
?>

<?php 
if ($CUSTOM->showQueryTools()) {
	getQueryCols();
}
?>
<h4>Reporting Tools*</h4>
<ul>
<li><a href="queue.php">Job Queue</a></li>
<?php 
if ($CUSTOM->showQueryTools()) {
?>
<li>
  <a href="javascript:qcLink('query/qcReportCollection.php?foo')">QC Overview for Collections</a>
</li>
<li>
  <a href="javascript:qcLink('query/qcReportCommunity.php?foo')">QC Overview for Communities</a>
</li>
<?php 
}
?>
</ul>
<?php
if ($CUSTOM->showBatchTools()) { 
?>
<h4>Admin Access Only</h4>
<ul>
<li>
  <a href="../auth/bulkIngest.php">Initiate Bulk Ingest</a>
</li>
<li><a href="../auth/undoBulkIngest.php">Undo Bulk Ingest</a></li>
<li><a href="../auth/changeParent.php">Move Community</a></li>
<li><a href="../auth/changeParentColl.php">Move Collection</a></li>
<li><a href="../auth/mediaFilter.php">Initiate Media Filter</a></li>
<li><a href="../auth/refreshStatistics.php">Refresh Statistics</a></li>
<li><a href="../auth/updateMetadata.php">Update Metadata</a></li>
</ul>
<?php 
} 

function getQueryCols() {
initQueries(false);
echo <<< HERE
  <div id="queryCols">
  <fieldset>
    <legend>Query Options</legend>
    <input type="radio" name="qcallcol" id="qcallcolon"><label for="qcallcolon">Show All Columns</label>
    <input type="radio" checked name="qcallcol" id="qcallcoloff"><label for="qcallcoloff">Show Selected Columns</label>
    <input name="warnonly" type="checkbox" id="warnonly"><label for="warnonly">Filter Warnings</label>
HERE;
  query::getQueryList();
  echo <<< HERE
  </fieldset>
  </div>
HERE;
}

$header->litFooter();
?>
</body>
</html>

