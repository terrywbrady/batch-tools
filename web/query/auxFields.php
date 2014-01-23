<?php

class auxFields {
	
  public static $AUXQ;	
  public static $AUXT;	
  public static $IMGKEY;
  public static $SHOWARR;
  public static $DC;	
  	
  public static function initAuxFields() {
	$handleContext =  isset($GLOBALS['handleContext']) ? $GLOBALS['handleContext'] : "";
    $thumb = <<< EOF
select array_to_string(array_agg(text('{$handleContext}/bitstream/id/' || bit.bitstream_id || '/' || bit.name)), '<hr/>') 
from bitstream bit
inner join bundle2bitstream b2b
  on b2b.bitstream_id = bit.bitstream_id
inner join bundle b
  on b2b.bundle_id = b.bundle_id
  and b.name = 'THUMBNAIL'
inner join item2bundle i2b
  on i2b.bundle_id = b.bundle_id
  and i2b.item_id=i.item_id
EOF;

    $thumbName = <<< EOF
select array_to_string(array_agg(text(bit.name)), '<hr/>')
from bitstream bit
inner join bundle2bitstream b2b
  on b2b.bitstream_id = bit.bitstream_id
inner join bundle b
  on b2b.bundle_id = b.bundle_id
  and b.name = 'THUMBNAIL'
inner join item2bundle i2b
  on i2b.bundle_id = b.bundle_id
  and i2b.item_id=i.item_id
EOF;

    $size = <<< EOF
select array_to_string(array_agg(text(bit.size_bytes/1000/1000.0)), '<hr/>')
from bitstream bit
inner join bundle2bitstream b2b
  on b2b.bitstream_id = bit.bitstream_id
inner join bundle b
  on b2b.bundle_id = b.bundle_id
  and b.name = 'ORIGINAL'
inner join item2bundle i2b
  on i2b.bundle_id = b.bundle_id
  and i2b.item_id=i.item_id
EOF;

    $origName = <<< EOF
select array_to_string(array_agg(text(bit.name)), '<hr/>')
from bitstream bit
inner join bundle2bitstream b2b
  on b2b.bitstream_id = bit.bitstream_id
inner join bundle b
  on b2b.bundle_id = b.bundle_id
  and b.name = 'ORIGINAL'
inner join item2bundle i2b
  on i2b.bundle_id = b.bundle_id
  and i2b.item_id=i.item_id
EOF;

    $textName = <<< EOF
select array_to_string(array_agg(text(bit.name)), '<hr/>')
from bitstream bit
inner join bundle2bitstream b2b
  on b2b.bitstream_id = bit.bitstream_id
inner join bundle b
  on b2b.bundle_id = b.bundle_id
  and b.name = 'TEXT'
inner join item2bundle i2b
  on i2b.bundle_id = b.bundle_id
  and i2b.item_id=i.item_id
EOF;

    /*Note that this rule contains a filter to ignore @mire zoom tiles, disregard if not applicable*/
    $otherName = <<< EOF
select array_to_string(array_agg(text(bit.name)), '<hr/>')
from bitstream bit
inner join bundle2bitstream b2b
  on b2b.bitstream_id = bit.bitstream_id
inner join bundle b
  on b2b.bundle_id = b.bundle_id
  and b.name not in ('ORIGINAL', 'THUMBNAIL','TEXT')
  and b.name not like ('tiles_%')
inner join item2bundle i2b
  on i2b.bundle_id = b.bundle_id
  and i2b.item_id=i.item_id
where bit.name != 'license.txt'
EOF;

    $origId = <<< EOF
select array_to_string(array_agg(text(bit.internal_id)), '<hr/>')
from bitstream bit
inner join bundle2bitstream b2b
  on b2b.bitstream_id = bit.bitstream_id
inner join bundle b
  on b2b.bundle_id = b.bundle_id
  and b.name = 'ORIGINAL'
inner join item2bundle i2b
  on i2b.bundle_id = b.bundle_id
  and i2b.item_id=i.item_id
EOF;


    $bitRestricted = <<< EOF
select case when count(*) > 0 then 'Unrestricted' else 'Restricted' end
from bitstream bit
inner join bundle2bitstream b2b
  on b2b.bitstream_id = bit.bitstream_id
inner join bundle b
  on b2b.bundle_id = b.bundle_id
  and b.name = 'ORIGINAL'
inner join item2bundle i2b
  on i2b.bundle_id = b.bundle_id
  and i2b.item_id=i.item_id
where exists (
  select 1 
  from resourcepolicy 
  where resource_type_id=0
  and bit.bitstream_id=resource_id
  and epersongroup_id = 0
  and (start_date is null or start_date <= current_date)
  and (end_date is null or start_date >= current_date)
)
EOF;

    $thumbRestricted = <<< EOF
select case when count(*) > 0 then 'Unrestricted' else 'Restricted' end
from bitstream bit
inner join bundle2bitstream b2b
  on b2b.bitstream_id = bit.bitstream_id
inner join bundle b
  on b2b.bundle_id = b.bundle_id
  and b.name = 'THUMBNAIL'
inner join item2bundle i2b
  on i2b.bundle_id = b.bundle_id
  and i2b.item_id=i.item_id
where exists (
  select 1 
  from resourcepolicy 
  where resource_type_id=0
  and bit.bitstream_id=resource_id
  and epersongroup_id = 0
  and (start_date is null or start_date <= current_date)
  and (end_date is null or start_date >= current_date)
)
EOF;

    self::$AUXQ = array(
      "ItemId"     => "i.item_id",  
      "Available" => self::getFieldByName("date","available"),
      "Accession" => self::getFieldByName("date","accessioned"),   
      "Accmo" => self::getMonthByName("date","accessioned"),   
      "Issue" => self::getFieldByName("date","issued"),   
      "Create" => self::getFieldByName("date","created"),   
      "UnqualDate" => self::getFieldByName("date",null),   
      "Creator" => self::getFieldByName("creator",null),   
      "Author" => self::getFieldByName("contributor","author"),   
      "URI" => self::getFieldByName("identifier","uri"),   
      "RelURI" => self::getFieldByName("relation","uri"),   
      "Publisher" => self::getFieldByName("publisher",null),   
      "Subject" => self::getFieldByName("subject",null),   
      "Format" => self::getFieldByName("format",null),   
      "Type" => self::getFieldByName("type",null),   
      "Provenance" => self::getFieldByName("description","provenance"),   
      "GenThumb"  => "({$thumb})",  
      "ThumbName"  => "({$thumbName})",  
      "OrigName"  => "({$origName})",  
      "OtherName"  => "({$otherName})",  
      "Text"  => "({$textName})",  
      "SizeMB"  => "({$size})",  
      "OrigId"  => "({$origId})",  
      "Private"  => "case when i.discoverable = false then 'Private' else 'Discoverable' end",  
      "BitRestricted" => "({$bitRestricted})",  
      "ThumbRestricted" => "({$thumbRestricted})",  
    );

    self::$DC = array(
      "Available" => "dc.date.available",
      "Issue" => "dc.date.issued",  
      "Create" => "dc.date.created[en_US]",   
      "Author" => "dc.contributor[en_US]",   
      "UnqualDate" => "dc.date[en_US]",  
      "Creator" => "dc.creator[en_US]",   
      "RelURI" => "dc.relation.uri[en_US]",   
      "Publisher" => "dc.publisher[en_US]",   
      "Subject" => "dc.subject[en_US]",   
      "Format" => "dc.format[en_US]",   
      "Type" => "dc.type[en_US]",   
    );


    self::$AUXT = array(
      "ItemId"     => "Item id number in DSpace",  
      "Available" => "Date or date range item became available to the public.",  
      "Accession" => "Date DSpace takes possession of item.",  
      "Accmo" => "Month DSpace takes possession of item.",  
      "Issue" => "Date of publication or distribution.",  
      "Create" => "Date of creation or manufacture of intellectual content if different from date.issued.",  
      "RelURI" => "URI to external resource",   
      "UnqualDate" => "Qualified dates are prefered within the system",  
      "GenThumb"  => "Thumbnail Generated by DSpace default generator",  
      "ThumbName"  => "Name of the thumbnail file",  
      "OrigName"  => "Name of the original file",  
      "OrigName"  => "Name of auxilliary files",  
      "SizeMB"  => "Size of the original file MB",  
      "OrigId"  => "Assetstore Id",  
      "BitRestricted"  => "Original Restricted",  
      "ThumbRestricted"  => "Thumbnail Restricted",  
      "Private"  => "Private Item",  
    );

    self::$IMGKEY = array(
	  'GenThumb' => true, 
    );

    self::$SHOWARR = array();
    if (isset($_GET['show'])){
      foreach($_GET['show'] as $s) {
	    if (isset(self::$AUXQ[$s])){
		   self::$SHOWARR[$s] = self::$AUXQ[$s];
	    }
      }
    }
  }
  
  public static function addAuxField($name, $desc, $query, $dc, $isImg) {
  	  self::$AUXT[$name] = $desc;
  	  self::$AUXQ[$name] = $query;
  	  if ($dc != "") {
  	  	  self::$DC[$name] = $dc;
  	  }
  	  if ($isImg) {
  	  	  self::$IMGKEY[$name] = true;
  	  }  	  
  }
  
  
  public static function getMonthByName($element, $qualifier) {
  	return "(select array_to_string(array_agg(substr(text_value,1,7)),'<hr/>') " .
  			"from metadatavalue mx " .
  			"inner join metadatafieldregistry mfr on mx.metadata_field_id=mfr.metadata_field_id " .
  			"and element='" . $element . "' and " .
  			(($qualifier == null) ?	"qualifier is null" : "qualifier='" . $qualifier . "'") .
  			" and mx.item_id=i.item_id)";
  }

  public static function getFieldByName($element, $qualifier) {
	return "(select array_to_string(array_agg(text_value),'<hr/>') " .
			"from metadatavalue mx " .
			"inner join metadatafieldregistry mfr on mx.metadata_field_id=mfr.metadata_field_id " .
			"and element='" . $element . "' and " .
  			(($qualifier == null) ?	"qualifier is null" : "qualifier='" . $qualifier . "'") .
			" and mx.item_id=i.item_id)";
  }

  public static function getShowOptCb() {
  	$showopt = "";
	foreach(self::$AUXQ as $k => $v) {
	  $sel = isset(self::$SHOWARR[$k]) ? "checked" : "";
	  $title = self::getTitleAttr($k); 
	  $showopt .= "<li><input type='checkbox' {$title} value='{$k}' $sel name='show[]' id='show_{$k}'/>" .
	  		"<label {$title} for='show_{$k}'>{$k}</label></li>";
	}
  	return <<< HERE
<span>Designate the fields to display for each item</span>
<ul>
{$showopt}
</ul>
HERE;
  }

  public static function  getShowOptToggle()  {
  	$showOpt = self::getShowOptCb();
  	return <<< HERE
  	<input id="soc" type="checkbox" onclick="$('#showopt').dialog('open')"/><label for="soc">Item Display Options</label>
  	<div id="showopt">{$showOpt}</div>
HERE;
  }

  public static function  getShowToolsToggle()  {
  	$showOpt = self::getShowOptCb();
  	return <<< HERE
  	<input id="soc" type="checkbox" onclick="$('#showopt').dialog('open')"/><label for="soc">Item Display Options</label>
  	<div id="showopt">{$showOpt}</div>
HERE;
  }
  
  public static function getTitleAttr($k) {
  	$title = isset(self::$AUXT[$k]) ? self::$AUXT[$k] : $k;
  	return  "title='{$title}'"; 
  }
}

?>