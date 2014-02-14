<?php
include '../header.php';

$sslproto =  isset($GLOBALS['sslproto']) ? $GLOBALS['sslproto'] : "https";

$status = "";
header('Content-type: text/html; charset=UTF-8');

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<?php 
$header = new LitHeader("MashupApi: Permits Blackboard Navigation of DigitalGeorgetown");
$header->litPageHeader();
//phpinfo();
?>

<script type="text/javascript">
$(document).ready(function(){$("#accordion").accordion({heightStyle: 'content'});});
</script>
<style type="text/css">
body {margin: 20px;}
div.desc {margin-left: 40px; font-style: italic;}
#accordion {
	padding: 10px 40px;
	width: 90%;
}
</style>

</head>
<body>
<?php $header->litHeader(array());?>
<div>
  <p>The purpsose of this API is to allow Blackboard to navigate DigitalGeorgetown. </p>
  <p>The Blackboard customizations are maintained by Georgetown University Information Systems Scholarly Systems.</p>
  <p>This API and DigitalGeorgetown are maintained by Georgetown Library Information Technology.</p>
</div>
<div id="accordion">
  <h3>No action specified</h3>
  <div>
    <p>If no action parameter is specified, the API will return a simple structure referencing this documentation.</p>
    <form action="MashupApi.php" method="GET">
      <input type="submit"/>
    </form>
    <textarea READONLY DISABLED cols="120" rows="7">
      <service>
	    <api-url>URL to the API documentation</api-url>
        <request/>
	    <status success="false">Action parameter is reqired: getCollections|getItemsByHandle|search</status>
	    <description>The purpose of this service is to permit Blackboard to navigate DigitalGeorgetown</description>
      </service>
    </textarea>
  </div>
  <h3>action=getCollections</h3>
  <div>
    <p>Return the hierarchy of communities and collections for the repository.  Each level of hierarchy is identified by a handle.  Communities may contain sub-communities and collections.  Collections may only contain items.</p>
    <form action="MashupApi.php" method="GET">
      <label for="action">Action:</label>
      <input id="action" type="text" READONLY name="action" value="getCollections"/>
      <br/>
      <input type="submit"/>
    </form>
    <textarea READONLY DISABLED cols="120" rows="17">
      <collections>
        <api-url>URL to the API documentation<api-url>
        <request>getCollections</request>
        <status success="true"></status>
        <repository>
          <handle>10822/0</handle>
          <name>All of DigitalGeorgetown</name>
          <community>
            <handle>...</handle>
            <name>...</name>
            <community>...</community>
            <collection>
	          <handle>...</handle>
	          <name>...</name>
            </collection>
          </community>
        </repository>
      </collections>
    </textarea>
  </div>
  <h3>action=getItemsByHandle</h3>
  <div>
    <p>Return an item or list of items identified by handle</p>
    <form action="MashupApi.php" method="GET">
      <label for="action">Action:</label>
      <input id="action" type="text" READONLY name="action" value="getItemsByHandle"/>
      <br/>
      <label for="itemHandles">itemHandles:</label>
      <input id="itemHandles" type="text" name="itemHandles" value="10822/550856,10822/559284" size="80"/>
      <div class="desc">Comma spearated list of item handles.  Note, this request is translated to the search handle:(X OR Y)</div>
      <br/>
      <input type="submit"/>
    </form>
    <textarea READONLY DISABLED cols="120" rows="14">
      <results numFound="..." from="..." rows="...">
        <api-url>URL to the API documentation<api-url>
        <request>getItemsByHandle&itemsHandles=...</request>
        <status success="true">Status message</status>
        <item index="n">
          <handle>...</handle>
          <title>...</title>
          <creator>...</creator>
          <date-created>...</date-created>
          <item-url>...</item-url>
          <thumbnail-url>...</thumbnail-url>
          </item>
        </results>
    </textarea>
  </div>
  <h3>action=search</h3>
  <div>
    <p>Return an item or list of items identified by handle</p>
    <form action="MashupApi.php" method="GET">
      <label for="action">Action:</label>
      <input id="action" type="text" READONLY name="action" value="search"/>
      <br/>
      <label for="scopeHandle">scopeHandle:</label>
      <input id="scopeHandle" type="text" name="scopeHandle" value="10822/0" size="20"/>
      <div class="desc">DSpace handle of the repository, community, or collection to search</div>
      <br/>
      <label for="search">search:</label>
      <input id="search" type="text" name="search" value="" size="80"/>
      <div class="desc">Search term, phrase, or search string to perform. <a href="/static/html/MashupApiHelp.html">Search Help</a></div>
      <br/>
      <label for="sort">sort:</label>
      <input id="sort" type="text" name="sort" value="" size="30"/>
      <div class="desc">Sort criteria: score|title; default=score except for Angelica</div>
      <br/>
      <label for="from">from:</label>
      <input id="from" type="text" name="from" value="0" size="5"/>
      <div class="desc">For pagination purposes, the search index to start from when displaying results</div>
      <br/>
      <label for="rows">rows:</label>
      <input id="rows" type="text" name="rows" value="10" size="5"/>
      <div class="desc">For pagination purposes, the number of items to return per page</div>
      <br/>
      <input type="submit"/>
    </form>
    <textarea READONLY DISABLED cols="120" rows="17">
      <search>
        <api-url>URL to the API documentation<api-url>
        <request>search&search=...</request>
        <status success="true">Status message</status>
        <results numFound="..." from="..." rows="...">
          <item index="n">
            <handle>...</handle>
            <title>...</title>
            <creator>...</creator>
            <date-created>...</date-created>
            <item-url>...</item-url>
            <thumbnail-url>...</thumbnail-url>
          </item>
        </results>
      </search>
    </textarea>
    </div>
  </div>  
</div>

<?php $header->litFooter();?>
</body>
</html>
