<?php
include '../header.php';

$CUSTOM = custom::instance();
$CINIT = $CUSTOM->getCommunityInit();

header('Content-type: application/xml; charset=UTF-8');
error_reporting(0);

$action = util::getArg("action","");

function addNode($parent, $tag, $handle, $name) {
	$doc = $parent->ownerDocument;
	$node = $doc->createElement($tag);
	$parent->appendChild($node);
	$el = $doc->createElement("handle");
	$el->appendChild($doc->createTextNode($handle));
	$node->appendChild($el);
	$el = $doc->createElement("name");
	$el->appendChild($doc->createTextNode(htmlspecialchars($name)));
	$node->appendChild($el);
	return $node;
}

function getResponseNode($xml, $tag) {
    $host = isset($_SERVER['HTTP_HOST']) ? $_SERVER["HTTP_HOST"] : "";
    $request = isset($_SERVER['REQUEST_URI']) ? htmlspecialchars($_SERVER["REQUEST_URI"]) : "";
    $query = isset($_SERVER['QUERY_STRING']) ? htmlspecialchars($_SERVER["QUERY_STRING"]) : "";
    $documentation = preg_replace("/MashupApi\.php.*/","MashupApiDoc.php",$request);

    $RES = <<< HERE
    <${tag}>
    <api-url>https://{$host}{$documentation}</api-url>
    <request>{$query}</request>
    </{$tag}>
HERE;
    $stat = $xml->loadXML($RES);
    return $xml->documentElement;
}

function addStatusNode($parent) {
	$doc = $parent->ownerDocument;
    $statel = $doc->createElement("status");
    $parent->appendChild($statel);
    return $statel;
}

function addHierarchyNode($node, $obj) {
	$n = addNode($node, $obj->type, $obj->handle, $obj->name);
	foreach($obj->children as $cobj) {
		addHierarchyNode($n, $cobj);
	}
}

$xml = new DOMDocument();

if ($action == "getCollections") {
	$root = getResponseNode($xml, "collections");
	$statel = addStatusNode($root);
    $node = addNode($root, "repository", "10822/0", "All of DigitalGeorgetown");

	$CINIT->initCommunities();
    $CINIT->initCollections();
    $CUSTOM->initHierarchy();
    foreach(hierarchy::$TOPS as $obj) {
	    addHierarchyNode($node, $obj);
    }
    statusSuccess($statel);
} else if ($action == "getItemsByHandle") {
    $fq = "";
    $search  = util::getArg("itemHandles","");
	$sort  = util::getArg("sort","");
    $search = urlencode(preg_replace("/,/"," OR ",$search));
    $search = "handle:(" . $search .")";
	search($xml,$fq,$search,0,100,$sort);
} else if ($action == "search") {
	$scopeHandle = util::getArg("scopeHandle","10822/0");
	$defsort = ($scopeHandle == "10822/559388" or $scopeHandle == "10822/559389") ? "title" : "";
	
	$CINIT->initCommunities();
    $CINIT->initCollections();
    $CUSTOM->initHierarchy();
    
    $fq = "";
    foreach(hierarchy::$OBJECTS as $obj) {
    	if ($obj->handle == $scopeHandle) {
    		if ($obj->type == "collection") {
    			$fq = "&fq=location.coll:" . $obj->id;
    		} else {
    			$fq = "&fq=location.comm:" . $obj->id;    			
    		}
    		break;
    	}
    }
    
    if ($fq == "") {
    	$status = "Unknown community/collection handle";
    }
    
	$search  = util::getArg("search","");
	if ($scopeHandle == "10822/559389") {
	  $search .= ' type:"Digital Object"';	
	}
	$sort  = util::getArg("sort","");
	if ($sort == "") $sort = $defsort;
	if ($search == "") $search = "*";
	$search = urlencode($search);
	$from = util::getArg("from","0");
	$rows = util::getArg("rows","10");
	search($xml,$fq,$search,$from,$rows,$sort);
} else {
	$root = getResponseNode($xml, "service");
	$statel = addStatusNode($root);
	statusError($statel,"Action parameter is reqired: getCollections|getItemsByHandle|search");
	$node = $root->ownerDocument->createElement("description");
	$node->appendChild($root->ownerDocument->createTextNode("The purpose of this service is to permit Blackboard to navigate DigitalGeorgetown"));
	$root->appendChild($node);
}

echo $xml->saveXML();

function search($xml,$fq,$search,$from,$rows,$sort) {
	if ($sort == "title") {
		$sort = "bi_sort_1_sort asc";
	} else if ($sort == "score") {
		$sort = "score desc";
	} else {
		$sort = "score desc";
	}
	$sort = urlencode($sort);
    $root = getResponseNode($xml, "search");
    $statel = addStatusNode($root);
    try {
        $sslproto =  isset($GLOBALS['sslproto']) ? $GLOBALS['sslproto'] : "https";
	    $fl = "&fl=score,*&wt=xml&start=".$from."&rows=".$rows;
	    header('Content-type: application/xml; charset=UTF-8');
	
	    $req = "https://localhost/solr/search/select?indent=on".$fl.$fq. "&q=" . $search . "&sort=" . $sort;
        $resp = get_headers($req, 1);
	    if (!preg_match("/.*200 OK/", $resp[0])) {
	    	statusError($statel, "Bad SOLR Request: " . $resp[0] . ": ". $req);
	    	return;
	    }
	    $ret = file_get_contents($req);
	    if ($ret === FALSE) {
	    	statusError($statel, "Bad SOLR Request: " . ": ". $req);
	    	return;
	    }

        $solrxml = new DOMDocument();
        $stat = $solrxml->loadXML($ret);

        $xsl = new DOMDocument();
        $xsl->load("MashupApi.xsl");

        $host = isset($_SERVER['HTTP_HOST']) ? $_SERVER["HTTP_HOST"] : "";
        $proc = new XSLTProcessor();
        $proc->importStylesheet($xsl);
        $proc->setParameter("","from",htmlspecialchars($from));
	    $proc->setParameter("","rows",htmlspecialchars($rows));
	    $proc->setParameter("","search",htmlspecialchars($search));
	    $proc->setParameter("","proto",$sslproto);
	    $proc->setParameter("","host",$host);

        $res = $proc->transformToDoc($solrxml);
	    
	    $newdoc = $res->documentElement;
	    if ($newdoc == null) throw new exception("Search result not produced.");
	    $root->appendChild($root->ownerDocument->importNode($res->documentElement, true));
    	statusSuccess($statel);
	} catch (exception $ex) {
	    $root = getResponseNode($xml, "search");
	    $statel = addStatusNode($root);
	    statusError($statel, $ex->getMessage());
	}
	
}

function statusError($statel, $message) {
	$statel->appendChild($statel->ownerDocument->createTextNode($message));
	$statel->setAttribute("success","false");
}

function statusSuccess($statel) {
	$statel->setAttribute("success","true");
}
