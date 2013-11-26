<?php

class auxFields {
	
  public static $AUXQ;	
  public static $AUXT;	
  public static $IMGKEY;
  public static $SHOWARR;
  	
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

    $stream = <<< EOF
select 
  array_to_string(
    array_agg(
      'DocId: ' ||
      (
        select text_value 
        from resourcemetadatavalue rmv
        inner join resourcemetadatafieldregistry rmfr on rmv.metadata_field_id=rmfr.metadata_field_id 
        where rmv.resource_id=bit.bitstream_id and rmfr.element='scribd' and rmfr.qualifier='docid'
      ) || ', ' ||
      (
        select text_value 
        from resourcemetadatavalue rmv
        inner join resourcemetadatafieldregistry rmfr on rmv.metadata_field_id=rmfr.metadata_field_id 
        where rmv.resource_id=bit.bitstream_id and rmfr.element='scribd' and rmfr.qualifier='accesskey'
      ) 
    ),  
    '<hr/>'
  )
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

    $streamflag = <<< EOF
select 
  array_to_string(
    array_agg(
      (
        select text_value 
        from resourcemetadatavalue rmv
        inner join resourcemetadatafieldregistry rmfr on rmv.metadata_field_id=rmfr.metadata_field_id 
        where rmv.resource_id=bit.bitstream_id and rmfr.element='preview' and rmfr.qualifier='scribd'
      )
    ),  
    '<hr/>'
  )
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

    $streamperm = <<< EOF
select 
  array_to_string(
    array_agg(
      (
        select text_value 
        from resourcemetadatavalue rmv
        inner join resourcemetadatafieldregistry rmfr on rmv.metadata_field_id=rmfr.metadata_field_id 
        where rmv.resource_id=bit.bitstream_id and rmfr.element='permission' and rmfr.qualifier='scribd'
      )
    ),  
    '<hr/>'
  )
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
      "Available" => self::getFieldById(12),  
      "Accession" => self::getFieldById(11),   
      "Accmo" => self::getMonthById(11),   
      "Issue" => self::getFieldById(15),   
      "Create" => self::getFieldById(14),   
      "UnqualDate" => self::getFieldById(10),   
      "Creator" => self::getFieldById(9),   
      "Identifier" => self::getFieldById(17),   
      "URI" => self::getFieldById(25),   
      "RelURI" => self::getFieldById(52),   
      "Publisher" => self::getFieldById(39),   
      "Subject" => self::getFieldById(57),   
      "Format" => self::getFieldById(33),   
      "Type" => self::getFieldById(66),   
      "Provenance" => self::getFieldByName("description","provenance"),   
      "EmbargoTerms" => self::getFieldByName("embargo","terms"),   
      "EmbargoLift" => self::getFieldByName("embargo","lift-date"),   
      "EmbargoCustom" => self::getFieldByName("embargo","custom-date"),   
      "GenThumb"  => "({$thumb} where bit.description = 'Generated Thumbnail')",  
      "LitThumb"  => "({$thumb} where bit.description = 'LIT Thumbnail')",  
      "CustomThumb"  => "({$thumb} where (bit.description is null or bit.description not in ('Generated Thumbnail','LIT Thumbnail')))",  
      "ThumbName"  => "({$thumbName})",  
      "OrigName"  => "({$origName})",  
      "OtherName"  => "({$otherName})",  
      "Text"  => "({$textName})",  
      "SizeMB"  => "({$size})",  
      "OrigId"  => "({$origId})",  
      "DocStream"  => "({$stream})",  
      "Private"  => "case when i.discoverable = false then 'Private' else 'Discoverable' end",  
      //"DocStreamFlag"  => "({$streamflag})",  
      //"DocStreamPerm"  => "({$streamperm})",  
      "BitRestricted" => "({$bitRestricted})",  
      "ThumbRestricted" => "({$thumbRestricted})",  
      //"IndexSort1" => "(select sort_1 from bi_item bi where bi.item_id=i.item_id)",  
      //"IndexSort2" => "(select sort_2 from bi_item bi where bi.item_id=i.item_id)",  
      //"IndexSort3" => "(select sort_3 from bi_item bi where bi.item_id=i.item_id)",  
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
      "GenThumb"  => "Thumbnail Generated by DSpace default generator (discontinued in DigitalGeorgetown), may be re-generated",  
      "LitThumb"  => "Thumbnail Generated by DigitalGeorgetown custom generator, may be re-generated",  
      "CustomThumb"  => "Custom thumbnail added manually, will not be re-generated by DigitalGeorgetown generator",  
      "BkThumb"  => "Backup of thumbnails while modifying the thumbnail generator",  
      "ThumbName"  => "Name of the thumbnail file",  
      "OrigName"  => "Name of the original file",  
      "OrigName"  => "Name of auxilliary files",  
      "SizeMB"  => "Size of the original file MB",  
      "OrigId"  => "Assetstore Id",  
      "DocStream"  => "Document Streaming Attributes",  
      //"DocStreamFlag"  => "Document Streaming Flag",  
      //"DocStreamPerm"  => "Document Streaming Permission",  
      "BitRestricted"  => "Original Restricted",  
      "ThumbRestricted"  => "Thumbnail Restricted",  
      "Private"  => "Private Item",  
      //"IndexSort1" => "DSpace default sort fields built by the index process",  
      //"IndexSort2" => "DSpace default sort fields built by the index process",  
      //"IndexSort3" => "DSpace default sort fields built by the index process",  
      "EmbargoTerms" => "Embargo Terms",   
      "EmbargoLift" => "Embargo Lift Date",   
      "EmbargoCustom" => "Embargo Custom Date",   
    );

    self::$IMGKEY = array(
	  'BkThumb' => true, 
	  'LitThumb' => true, 
	  'GenThumb' => true, 
	  'CustomThumb' => true
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
  
  public static function getFieldById($id) {
	return "(select array_to_string(array_agg(text_value),'<hr/>') from metadatavalue mx where mx.metadata_field_id=" . $id . " and mx.item_id=i.item_id)";
  }

  public static function getMonthById($id) {
	return "(select array_to_string(array_agg(substr(text_value,1,7)),'<hr/>') from metadatavalue mx where mx.metadata_field_id=" . $id . " and mx.item_id=i.item_id)";
  }

  public static function getFieldByName($element, $qualifier) {
	return "(select array_to_string(array_agg(text_value),'<hr/>') from metadatavalue mx inner join metadatafieldregistry mfr on mx.metadata_field_id=mfr.metadata_field_id and element='" . $element . "' and qualifier='" . $qualifier . "' and mx.item_id=i.item_id)";
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