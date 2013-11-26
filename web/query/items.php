<?php
include '../header.php';
include 'queries.php';
include 'auxFields.php';

$CUSTOM = custom::instance();
$CUSTOM->getCommunityInit()->initCommunities();
$CUSTOM->getCommunityInit()->initCollections();


initQueries(false);
auxFields::initAuxFields();

function inval() {
	echo "<h1>Invalid Parameters</h1>";
	die();
}

try {
	$id = collectionArg::getId();
	if ($id == null) inval();
	
	$qname = $_GET['qname'];
	$rptquery = query::getQuery($qname);
} catch (exception $e){
	echo "Err {$e}";
	inval();
}

$header = new LitHeader($rptquery->header. ' for ' . collectionArg::getName());
header('Content-type: text/html; charset=UTF-8');
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<?php $header->litPageHeader();?>
</head>
<body>
<div>
<?php
$qargs = query::getFilterArgs();
if (collectionArg::isCollection())
	$header->litHeader(array("<a href='qcReportCollection.php{$qargs}' onclick='loadMsg()'>QC Overview for Collections</a>"));
else
	$header->litHeader(array("<a href='qcReportCommunity.php{$qargs}' onclick='loadMsg()'>QC Overview for Communities</a>"));

$handleContext =  isset($GLOBALS['handleContext']) ? $GLOBALS['handleContext'] : "";

$cols = "";
foreach(auxFields::$SHOWARR as $k => $v) {
	$cols .= "{$v} as {$k},\n";
}
$cols .= "1";

$sel = <<< EOF
select 
  i.item_id,
  regexp_replace(m64.text_value,E'[\r\n\t ]+',' ','g') as title,
  handle,
  {$cols}
from 
  item i
inner join 
  handle on i.item_id = resource_id and resource_type_id = 2
left join
  metadatavalue m64 on m64.item_id = i.item_id and m64.metadata_field_id = 64
EOF;

if (collectionArg::isCollection()) {
$sql = <<< EOF
{$sel}
where
  i.owning_collection = :pid
  {$rptquery->subq};  
EOF;
} else if (collectionArg::isCommunity()) {
$sql = <<< ZZZ
{$sel}
inner join
  communities2item c2i on i.item_id = c2i.item_id
where
  c2i.community_id = :pid
  {$rptquery->subq};  
ZZZ;
}

$dbh = $CUSTOM->getPdoDb();
$stmt = $dbh->prepare($sql);

$result = $stmt->execute(array(':pid' => $id));

$result = $stmt->fetchAll();

//$header->sqlDump($sql);

if (!$result) {
	print_r($dbh->errorInfo());
     die("Error in SQL query");
}       

$iname = collectionArg::getInputName();

$showopt = auxFields::getShowOptCb();

$form = <<< FORM
<input id="stools" type="checkbox" onclick="$('#tools').dialog('open')"/><label for="stools">Item Display Tools</label>
<div id="tools" style="display:none">
<form method="GET">
<input type="hidden" name="qname" value="{$qname}"/>
<input type="hidden" name="{$iname}" value="{$id}"/>
{$showopt}
<button type="submit">Refresh Display</button>
</form>

<form method="POST" action="ajaxMakeExcel.php">
<button type="submit" onclick="javascript:$('#data').val($('#export').html());">Export Table</button>
<input type="hidden" name="data" id="data"/>
</form>
</div>
FORM;

echo $form;
?>
<div id="export">
<table class="sortable">
<tbody>
<tr  class='header'>
  <th class="">Count</th>
  <th class="title">Title</th>
  <th class="">Handle</th>
  <?php
  foreach(auxFields::$SHOWARR as $k => $v) {
  	$title = auxFields::getTitleAttr($k);
    echo "<th class='mod' {$title}>{$k}</th>";	
  } 
  ?>
</tr>

<?php
  $handleContext =  isset($GLOBALS['handleContext']) ? $GLOBALS['handleContext'] : "";
 // iterate over result set
 // print each row
 $c = 0;
foreach ($result as $row) {
  	 $class = ($c++ % 2 == 0) ? "allrow even" : "allrow odd";
 	echo "<tr class='{$class}'>";
 	echo "<td>{$c}</td>";
 	echo "<td>{$row[1]}</td>";
 	$h = $row[2];
 	$href = $handleContext . '/handle/' .$h . '?show=full';
 	$disp = $h;
 	
 	echo "<td><a href='{$href}'>{$disp}</a></td>";
 	$i=0;
 	foreach(auxFields::$SHOWARR as $k => $v) {
 		$val = $row[$i+3];
 		if (isset(auxFields::$IMGKEY[$k])){
	 		echo "<td class='mod'>";
	 		$arr = explode("<hr/>", $val);
	 		foreach($arr as $k => $v) {
	 		  if ($k > 0) echo "<hr/>";				 			
	 		  echo "<img src='{$v}'/>"; 
	 		} 				 			
	 		echo "</td>"; 				 			
 		} else {
	 		echo "<td class='mod'>{$val}</td>"; 				
 		}
 		$i++;
 	}
 	echo "</tr>";
 }       

?>
</tbody>
</table>
</div>
<?php $header->litFooter()?>
</body>
</html>

