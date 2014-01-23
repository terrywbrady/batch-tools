<?php

class query {
	public $name;
	public $header;
	public $subq;
	public $classes;
	public $func;
	public $showarr;
	
	public static $count = 0;

	public static $CATQ = array();
	public static $MULTCAT = array();
	
	public static $QUERIES = array();
	
	function __construct($name, $header, $subq, $classes, $testVal, $showarr) {
    	$CUSTOM = custom::instance();
    	
		self::$count++;
		if (self::$count % 2 == 0) {
			$oddeven = "even";			
		} else {
			$oddeven = "odd";
		}
		
		$this->name = $name;
		$this->header = $header;
		$this->subq = $subq;
		$this->classes = $name . " allcol " . $oddeven . " " . $classes;
		$this->testVal = $testVal;
		$this->showarr = $showarr;
		
		$docols = array();
		if (isset($_GET['col'])) {
			$col = $_GET['col'];
			if ($col != "") {
				$docols = explode(",", $_GET['col']);
			}
		}
		
		if (!$CUSTOM->hasQueryKey($classes)) return;
		
		$addit = (count($docols) == 0) ? true : in_array($name, $docols);

		if ($addit) {
			self::$QUERIES[count(self::$QUERIES)] = $this;
		}
		
		$carr = explode(" ", $classes);
		if (count($carr) > 1) {
			self::$MULTCAT[$name] = $this;
		} else {
		  foreach ($carr as $cat) {
			if (isset(self::$CATQ[$cat])) {
			} else {
				self::$CATQ[$cat] = array();
			}
			self::$CATQ[$cat][] = $this;
		  }
		}
		
	}
	
	function getShowArrArg() {
		$ret = "";
		foreach($this->showarr as $s) {
			$ret .= "&show[]=" . $s;
		}
		return $ret;
	}
	
	public static function getQuery($name) {
		foreach(self::$QUERIES as $q) {
			if ($q->name == $name) 
				return $q;
		}
		return new query("invalid","Invalid Parameters","and 1=2","",new testValZero(),array());
	}
	
	function testVal($vals, $val) {
		return $this->testVal->testVal($vals, $val);
	}
	
	function mainQuery() {
		return "
  (
    select count(*) 
    from item i
    inner join collection2item c2i 
      on c2i.item_id = i.item_id 
      and c2i.collection_id = coll.collection_id
    where (i.in_archive is true or i.discoverable = false)
    {$this->subq}
  ) as {$this->name},
";		
	}

	function commQuery() {
		return "
  (
    select count(*) 
    from item i
    inner join communities2item c2i 
      on c2i.item_id = i.item_id 
      and c2i.community_id = comm.community_id
    /*where (i.in_archive is true or i.discoverable = false)*/
    where i.in_archive is true
    {$this->subq}
  ) as {$this->name},
";		
	}

	function mainItemQuery() {
		return "
  (
    select count(*) 
    from item i
    where i.item_id = i2.item_id
    {$this->subq}
  ) as {$this->name},
";		
	}

	public static function getFilterArgs() {
		$str = "?view=" . util::getArg("view",self::getDefaultView());
		$str .= "&col=" . util::getArg("col","");
		$str .= "&warn=" . util::getArg("warn","");
		$str .= "&type=" . util::getArg("type","");
		$str .= "&comm=" . util::getArg("comm","");
		return $str;
	}
	
	public static function getDefaultView() {
    	$vc = util::getArg("col","");
    	if (($vc == "") || ($vc == "")) return "basic";
    	return "";		
	}

    public static function toolbar() {
    	$v = util::getArg("view",self::getDefaultView());
    	echo '<select id="viewToolbar">';
    	util::makeOpt("allcol","All Attributes",$v);
    	foreach(custom::instance()->getQueryKeys() as $label => $name) {
    		util::makeOpt($label, $name, $v);
    	}
    	echo '</select>';

    	$v = util::getArg("col","");
    	echo '<select id="colToolbar">';
    	util::makeOpt("","All Columns",$v);
    	foreach(self::$QUERIES as $q) {
    		util::makeOpt($q->name,$q->header,$v);
  		};    	
    	echo '</select>';

    	$v = util::getArg("warn","");
    	echo '<select id="warnToolbar">';
    	util::makeOpt("","Any Status",$v);
    	util::makeOpt("warn","Has Warning",$v);
    	util::makeOpt("no-warn","Has No Warning",$v);
    	echo '</select>';

    	$v = util::getArg("type","basic");
    	echo '<select id="typeToolbar">';
    	util::makeOpt("","All Item types",$v);
    	util::makeOpt("docs","Has Documents",$v);
    	util::makeOpt("images","Has Images",$v);
    	util::makeOpt("video","Has Video",$v);
    	util::makeOpt("other","Has Other",$v);
    	util::makeOpt("empty","Has No Media",$v);
    	echo '</select>';

		$v = util::getArg("col","");
		echo "<input type='hidden' name='qcol' value='$v' id='qcol'/>";
    }

    static function getQueryList() {
    	$CUSTOM = custom::instance();
    	$CATEGORIES = $CUSTOM->getQueryKeys();
    	echo <<< HERE
    	<fieldset class='queryCols'><legend>Columns to Query</legend>
    	<fieldset>
    	  <button onclick="$('input.qccol,#warnonly').removeAttr('checked');">Uncheck All</button>
HERE;
    	foreach($CATEGORIES as $label => $name) {
    	  echo <<< HERE
    	  <button label='$label' name='$name' class='checkbutton checkon'>Check $name</button>
HERE;
    	}
    	echo <<< HERE
    	  <button onclick="$('input.qccol').attr('checked','Y');">Check All</button>
    	</fieldset>
    	<div class='checkboxes'>
HERE;
		self::showCheckboxes("General Attributes", self::$MULTCAT);
		$dcount = 2+count(self::$MULTCAT);
		$split = true;
		$total = count(self::$QUERIES) + 2 * count(self::$CATQ);
		foreach(self::$CATQ as $cat => $arr) {
		  if (!isset($CATEGORIES[$cat])) continue;
		  self::showCheckboxes($CATEGORIES[$cat], $arr);
		  $dcount += count($arr) + 2;
		  if ($split && $dcount >= $total/2) {
		  	echo "</div><div class='checkboxes'>";
		  	$split = false;
		  }
		}
		echo <<< HERE
		</div>
    	</fieldset>
HERE;
    }
    
    static function showCheckboxes($title, $arr) {
		  echo "<h4>$title</h4>";
		  echo "<ul>";
		  foreach($arr as $q) {
     		$name = $q->name;
     		$header = $q->header;
     		$classes = $q->classes;
     		$checked = ($name == "itemCount") ? "checked" : "";
     		echo "<li><input class='qccol $classes' name='qcCol' type='checkbox' $checked id='$name' value='$name'><label for='$name'>$header</label></li>";
		  }	
		  echo "</ul>";    	
    }

}

class testValTrue {
	public static function testVal($vals, $val) {
		return true;
	}
}

class testValZero {
	public static function testVal($vals, $val) {
		return ($val == 0);
	}
}

class testValPos {
	public static function testVal($vals, $val) {
		return ($val > 0);
	}
}

class testValNumItem {
	public static function testVal($vals, $val) {
		return ($val == $vals['itemCount']);
	}
}
class testValNumImage {
	public static function testVal($vals, $val) {
		return ($val == $vals['itemCountImage']);
	}
}
class testValNumDoc {
	public static function testVal($vals, $val) {
		return ($val == $vals['itemCountDoc']);
	}
}

function initQueries() {
$subq = "";
new query("itemCount","Num Items",$subq,"head basic text license type image meta mod date embargo", new testValPos(),array("Accession")); 

$subq = <<< EOF
    and exists 
    (
      select 1
      from item2bundle i2b
      inner join bundle b 
        on i2b.bundle_id = b.bundle_id
        and b.name = 'ORIGINAL'
        and i.item_id = i2b.item_id
      inner join bundle2bitstream b2b on b.bundle_id = b2b.bundle_id
      inner join bitstream bit on bit.bitstream_id = b2b.bitstream_id
      inner join bitstreamformatregistry bfr on bit.bitstream_format_id = bfr.bitstream_format_id
        and bfr.mimetype in (
             'text/plain',
             'application/pdf',
             'text/html',
             'application/msword',
             'text/xml',
	         'application/msword',
             'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
             'application/vnd.ms-powerpoint',
	         'application/vnd.openxmlformats-officedocument.presentationml.presentation',
             'application/vnd.ms-excel',
             'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
       )
    ) 
EOF;
new query("itemCountDoc","Num Document Items",$subq,"head basic text image", new testValTrue(),array("Accession","Creator")); 


$subq = <<< EOF
    and exists 
    (
      select 1
      from item2bundle i2b
      inner join bundle b 
        on i2b.bundle_id = b.bundle_id
        and b.name = 'ORIGINAL'
        and i.item_id = i2b.item_id
      inner join bundle2bitstream b2b on b.bundle_id = b2b.bundle_id
      inner join bitstream bit on bit.bitstream_id = b2b.bitstream_id
      inner join bitstreamformatregistry bfr on bit.bitstream_format_id = bfr.bitstream_format_id
        and bfr.mimetype in (
             'image/jp2',
             'image/jpeg'
      )
    ) 
EOF;
new query("itemCountSuppImage","Num Supported Image Items",$subq,"basic image", new testValTrue(),array("Accession","GenThumb")); 

$subq = <<< EOF
    and exists 
    (
      select 1
      from item2bundle i2b
      inner join bundle b 
        on i2b.bundle_id = b.bundle_id
        and b.name = 'ORIGINAL'
        and i.item_id = i2b.item_id
      inner join bundle2bitstream b2b on b.bundle_id = b2b.bundle_id
      inner join bitstream bit on bit.bitstream_id = b2b.bitstream_id
      inner join bitstreamformatregistry bfr on bit.bitstream_format_id = bfr.bitstream_format_id
        and bfr.mimetype like 'image/%' and bfr.mimetype not in (
             'image/jp2',
             'image/jpeg'
      )
    ) 
EOF;
new query("itemCountUnsuppImage","Num Unsupported Image Items",$subq,"basic type image", new testValZero(),array("Accession","GenThumb")); 

$subq = <<< EOF
    and exists 
    (
      select 1
      from item2bundle i2b
      inner join bundle b 
        on i2b.bundle_id = b.bundle_id
        and b.name = 'ORIGINAL'
        and i.item_id = i2b.item_id
      inner join bundle2bitstream b2b on b.bundle_id = b2b.bundle_id
      inner join bitstream bit on bit.bitstream_id = b2b.bitstream_id
      inner join bitstreamformatregistry bfr on bit.bitstream_format_id = bfr.bitstream_format_id
        and (
          bfr.mimetype like 'video/%' or 
          bfr.mimetype like 'image/%' or 
          bfr.mimetype in (
             'text/plain',
             'application/pdf',
             'text/html',
             'application/msword',
             'text/xml',
	         'application/msword',
             'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
             'application/vnd.ms-powerpoint',
	         'application/vnd.openxmlformats-officedocument.presentationml.presentation',
             'application/vnd.ms-excel',
             'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
         )
       )
    ) 
EOF;
new query("itemCountOther","Num Other Items",$subq,"basic type", new testValZero(),array("Accession","Format","OrigName")); 

$subq = <<< EOF
    and exists 
    (
      select 1
      from item2bundle i2b
      inner join bundle b 
        on i2b.bundle_id = b.bundle_id
        and b.name not in ('ORIGINAL', 'THUMBNAIL','TEXT')
        and b.name not like ('tiles_%')
        and i.item_id = i2b.item_id
      inner join bundle2bitstream b2b on b.bundle_id = b2b.bundle_id
      inner join bitstream bit on bit.bitstream_id = b2b.bitstream_id
        and bit.name != 'license.txt'
    ) 
EOF;
new query("itemCountLicense","Num Documentation Items",$subq,"basic type", new testValTrue(),array("Accession","Format","OtherName")); 

$subq = <<< EOF
    and exists 
    (
      select 1
      from item2bundle i2b
      inner join bundle b 
        on i2b.bundle_id = b.bundle_id
        and b.name = 'ORIGINAL'
        and i.item_id = i2b.item_id
      inner join bundle2bitstream b2b on b.bundle_id = b2b.bundle_id
      inner join bitstream bit on bit.bitstream_id = b2b.bitstream_id
        and bit.size_bytes > 10000000
    ) 
EOF;
new query("largeOrig","Large Original",$subq,"basic", new testValZero(),array("SizeMB","Format")); 


$subq = <<< EOF
    and not exists 
    (
      select 1
      from item2bundle i2b
      inner join bundle b 
        on i2b.bundle_id = b.bundle_id
        and b.name = 'ORIGINAL'
        and i.item_id = i2b.item_id
    ) 
    and not exists 
    (
      select 1
      from metadatavalue m 
      where m.item_id = i.item_id
      and m.metadata_field_id = (
        select metadata_field_id from metadatafieldregistry mfr
        where mfr.element = 'relation' and mfr.qualifier = 'uri'
      )
    ) 
EOF;
new query("itemCountWithoutOriginal","Num Items without Original or Relation URI",$subq,"basic", new testValZero(),array("Accession")); 

$subq = <<< EOF
    and exists 
    (
      select 1
      from metadatavalue m 
      where m.item_id = i.item_id
      and m.metadata_field_id = (
        select metadata_field_id from metadatafieldregistry mfr
        where mfr.element = 'relation' and mfr.qualifier = 'uri'
      )
    ) 
EOF;
new query("itemCountWithRelationURI","Num Items with Relation URI",$subq,"basic", new testValTrue(),array("Accession")); 

$subq = <<< EOF
    and exists 
    (
      select 1
      from item2bundle i2b
      inner join bundle b 
        on i2b.bundle_id = b.bundle_id
        and b.name = 'ORIGINAL'
        and i.item_id = i2b.item_id
      where (
        select count(*)
        from bundle2bitstream b2b
        inner join bitstream bit on b2b.bitstream_id = bit.bitstream_id
        where b2b.bundle_id = b.bundle_id 
      ) > 1
    ) 
EOF;
new query("itemCountWithMultOriginal","Num Items with Multiple Original",$subq,"basic", new testValZero(),array("Accession")); 

$subq = <<< EOF
    and exists 
    (
      select 1
      from item2bundle i2b
      inner join bundle b 
        on i2b.bundle_id = b.bundle_id
        and b.name = 'TEXT'
        and i.item_id = i2b.item_id
      where (
        select count(*)
        from bundle2bitstream b2b
        inner join bitstream bit on b2b.bitstream_id = bit.bitstream_id
        where b2b.bundle_id = b.bundle_id 
      ) > 1
    ) 
EOF;
new query("itemCountWithMultText","Num Items with Multiple Text Streams",$subq,"basic", new testValZero(),array("Accession","Text")); 

$subq = <<< EOF
    and exists 
    (
      select 1
      from item2bundle i2b
      inner join bundle b 
        on i2b.bundle_id = b.bundle_id
        and b.name = 'ORIGINAL'
        and i.item_id = i2b.item_id
      inner join bundle2bitstream b2b on b.bundle_id = b2b.bundle_id
      inner join bitstream bit on bit.bitstream_id = b2b.bitstream_id
      inner join bitstreamformatregistry bfr on bit.bitstream_format_id = bfr.bitstream_format_id
        and bfr.mimetype in (
             'text/plain',
             'text/html',
             'application/msword',
             'text/xml',
	         'application/msword',
             'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
             'application/vnd.ms-powerpoint',
	         'application/vnd.openxmlformats-officedocument.presentationml.presentation',
             'application/vnd.ms-excel',
             'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
       )
    ) 
EOF;
new query("itemCountDocNonPDF","Num Non-PDF doc Items",$subq,"text", new testValZero(),array("OrigName","Creator")); 

$subq = <<< EOF
    and exists 
    (
      select 1
      from item2bundle i2b
      inner join bundle b 
        on i2b.bundle_id = b.bundle_id
        and b.name = 'ORIGINAL'
        and i.item_id = i2b.item_id
      inner join bundle2bitstream b2b on b.bundle_id = b2b.bundle_id
      inner join bitstream bit on bit.bitstream_id = b2b.bitstream_id
      inner join bitstreamformatregistry bfr on bit.bitstream_format_id = bfr.bitstream_format_id
        and bfr.mimetype in ('application/pdf')
        and bit.size_bytes < 20000
    ) 
EOF;
new query("itemCountBadPdf","Possible Bad PDF (Too small)",$subq,"text", new testValZero(),array("Accession","OrigName")); 

$subq = <<< EOF
     and exists 
    (
      select 1
      from item2bundle i2b
      inner join bundle b 
        on i2b.bundle_id = b.bundle_id
        and b.name = 'ORIGINAL'
        and i.item_id = i2b.item_id
      inner join bundle2bitstream b2b on b.bundle_id = b2b.bundle_id
      inner join bitstream bit on bit.bitstream_id = b2b.bitstream_id
      inner join bitstreamformatregistry bfr on bit.bitstream_format_id = bfr.bitstream_format_id
        and bfr.mimetype in (
             'text/plain',
             'text/html',
             'application/msword',
             'text/xml',
	         'application/msword',
             'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
             'application/vnd.ms-powerpoint',
	         'application/vnd.openxmlformats-officedocument.presentationml.presentation',
             'application/vnd.ms-excel',
             'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
       )
    ) 
    and not exists 
    (
      select 1
      from item2bundle i2b
      inner join bundle b 
        on i2b.bundle_id = b.bundle_id
        and b.name = 'TEXT'
        and i.item_id = i2b.item_id
    ) 
EOF;
new query("itemCountWithoutTEXT","Num Doc Items without Text Extract",$subq,"text", new testValZero(),array("Accession","Format"));

$subq = <<< EOF
    and exists 
    (
      select 1
      from item2bundle i2b
      inner join bundle b 
        on i2b.bundle_id = b.bundle_id
        and b.name = 'ORIGINAL'
        and i.item_id = i2b.item_id
      inner join bundle2bitstream b2b on b.bundle_id = b2b.bundle_id
      inner join bitstream bit on bit.bitstream_id = b2b.bitstream_id
      inner join bitstreamformatregistry bfr on bit.bitstream_format_id = bfr.bitstream_format_id
        and bfr.mimetype like 'video/%'
    ) 
EOF;
new query("itemCountVideo","Num Video Items",$subq,"type", new testValZero(),array("Accession","Format")); 

$subq = <<< EOF
    and exists 
    (
      select 1
      from item2bundle i2b
      inner join bundle b 
        on i2b.bundle_id = b.bundle_id
        and b.name = 'ORIGINAL'
        and i.item_id = i2b.item_id
      inner join bundle2bitstream b2b on b.bundle_id = b2b.bundle_id
      inner join bitstream bit on bit.bitstream_id = b2b.bitstream_id
      inner join bitstreamformatregistry bfr on bit.bitstream_format_id = bfr.bitstream_format_id
        and bfr.mimetype like 'text/html%'
    ) 
EOF;
new query("itemCountHtml","Num HTML Items",$subq,"type", new testValZero(),array("Accession","Format")); 

$subq = <<< EOF
    and exists 
    (
      select 1
      from item2bundle i2b
      inner join bundle b 
        on i2b.bundle_id = b.bundle_id
        and b.name = 'ORIGINAL'
        and i.item_id = i2b.item_id
      inner join bundle2bitstream b2b
        on b.bundle_id = b2b.bundle_id
      inner join bitstream bit 
        on b2b.bitstream_id = bit.bitstream_id
        and bit.name ~ '.*\.zip$'
    ) 
EOF;
new query("itemCountZip","Num Zip files",$subq,"type", new testValTrue(),array("Accession","Format","OrigName")); 

$subq = <<< EOF
    and not exists 
    (
      select 1
      from item2bundle i2b
      where i.item_id = i2b.item_id
    )
EOF;
new query("itemCountWithoutBundle","Num Items without Bundle",$subq,"type", new testValZero(),array("Accession")); 

$subq = <<< EOF
    and exists 
    (
      select 1
      from item2bundle i2b
      inner join bundle b 
        on i2b.bundle_id = b.bundle_id
        and b.name = 'ORIGINAL'
        and i.item_id = i2b.item_id
      inner join bundle2bitstream b2b
        on b.bundle_id = b2b.bundle_id
      inner join bitstream bit 
        on b2b.bitstream_id = bit.bitstream_id
        and char_length(bit.name) > 30
    ) 
EOF;
new query("itemCountLongFileName","Num Items with Long File Name",$subq,"type", new testValTrue(),array("Accession","Format","OrigName")); 


$subq = <<< EOF
    and exists 
    (
      select 1
      from metadatavalue m 
      where m.item_id = i.item_id
      and m.metadata_field_id = (
        select metadata_field_id from metadatafieldregistry mfr
        where mfr.element = 'date' and mfr.qualifier = 'created'
      )
       and text_value !~ '^((No [dD]ate)|(([0-9][0-9][0-9][0-9]|[0-9][0-9][0-9][0-9]-(0[1-9]|1[012])|[0-9][0-9][0-9][0-9]-(0[1-9]|1[012])-(31|30|[12][0-9]|0[1-9]))))$'
    ) 
EOF;

new query("itemCountWithCreate","Num Items with Invalid Creation Date",$subq,"date", new testValZero(),array("Accession","Create")); 

$subq = <<< EOF
    and exists 
    (
      select 1
      from metadatavalue m 
      where m.item_id = i.item_id
      and m.metadata_field_id = (
        select metadata_field_id from metadatafieldregistry mfr
        where mfr.element = 'date' and mfr.qualifier = 'created'
      )
      and text_value in ('No date','No Date')
    ) 
EOF;

new query("itemCountIsNoDate","Num Items with Creation Date is 'No Date'",$subq,"date", new testValTrue(),array("Accession","Create")); 

$subq = <<< EOF
    and not exists 
    (
      select 1
      from metadatavalue m 
      where m.item_id = i.item_id
      and m.metadata_field_id = (
        select metadata_field_id from metadatafieldregistry mfr
        where mfr.element = 'date' and mfr.qualifier = 'created'
      )
     ) 
EOF;
new query("itemCountWithNoCreate","Num Items with No Creation Date",$subq,"date", new testValZero(),array("Accession")); 

$subq = <<< EOF
    and exists 
    (
      select 1
      from metadatavalue m 
      where m.item_id = i.item_id
      and m.metadata_field_id = (
        select metadata_field_id from metadatafieldregistry mfr
        where mfr.element = 'date' and mfr.qualifier = 'issued'
      )
       and text_value !~ '^([0-9][0-9][0-9][0-9]|[0-9][0-9][0-9][0-9]-(0[1-9]|1[012])||[0-9][0-9][0-9][0-9]-(0[1-9]|1[012])-(31|30|[12][0-9]|0[1-9]))$'
    ) 
EOF;

new query("itemCountWithInvIssue","Num Items with Invalid Issue Date",$subq,"date", new testValZero(),array("Accession","Issue")); 

$subq = <<< EOF
    and not exists 
    (
      select 1
      from metadatavalue m 
      where m.item_id = i.item_id
      and m.metadata_field_id = (
        select metadata_field_id from metadatafieldregistry mfr
        where mfr.element = 'date' and mfr.qualifier = 'issued'
      )
    ) 
EOF;
new query("itemCountWithNoIssue","Num Items with No Issue Date",$subq,"date", new testValZero(),array("Accession")); 

$subq = <<< EOF
    and not exists 
    (
      select 1
      from metadatavalue m 
      where m.item_id = i.item_id
      and m.metadata_field_id = (
        select metadata_field_id from metadatafieldregistry mfr
        where mfr.element = 'date' and mfr.qualifier = 'accessioned'
      )
    ) 
EOF;
new query("itemCountWithNoAcc","Num Items with No Accession Date",$subq,"date", new testValZero(),array("Accession")); 

$subq = <<< EOF
    and not exists 
    (
      select 1
      from metadatavalue m 
      where m.item_id = i.item_id
      and m.metadata_field_id = (
        select metadata_field_id from metadatafieldregistry mfr
        where mfr.element = 'date' and mfr.qualifier = 'available'
      )
    ) 
EOF;
new query("itemCountWithNoAvail","Num Items with No Available Date",$subq,"date", new testValZero(),array("Accession")); 

$subq = <<< EOF
    and 
    (
      (
        select count(*)
        from metadatavalue m 
      where m.item_id = i.item_id
      and m.metadata_field_id = (
        select metadata_field_id from metadatafieldregistry mfr
        where mfr.element = 'date' and mfr.qualifier = 'created'
      )
      ) > 1
      or
      (
        select count(*)
        from metadatavalue m 
      where m.item_id = i.item_id
      and m.metadata_field_id = (
        select metadata_field_id from metadatafieldregistry mfr
        where mfr.element = 'date' and mfr.qualifier = 'issued'
      )
      ) > 1
      or
      (
        select count(*)
        from metadatavalue m 
      where m.item_id = i.item_id
      and m.metadata_field_id = (
        select metadata_field_id from metadatafieldregistry mfr
        where mfr.element = 'date' and mfr.qualifier = 'available'
      )
      ) > 1
      or
      (
        select count(*)
        from metadatavalue m 
      where m.item_id = i.item_id
      and m.metadata_field_id = (
        select metadata_field_id from metadatafieldregistry mfr
        where mfr.element = 'date' and mfr.qualifier = 'accessioned'
      )
      ) > 1
    ) 
EOF;
new query("itemCountWithDupDate","Num Items with Duplicate Date",$subq,"date", new testValZero(),array("Accession","Create","Issue","Available")); 

$subq = <<< EOF
    and exists 
    (
      select 1
      from metadatavalue m 
      where m.item_id = i.item_id
      and m.metadata_field_id = (
        select metadata_field_id from metadatafieldregistry mfr
        where mfr.element = 'date' and mfr.qualifier is null
      )
    ) 
EOF;
new query("itemCountWithUnqualDate","Num Items with Unqualified Date",$subq,"date", new testValZero(),array("UnqualDate")); 

$subq = <<< EOF
    and exists 
    (
      select 1
      from item2bundle i2b
      inner join bundle b 
        on i2b.bundle_id = b.bundle_id
        and b.name = 'ORIGINAL'
        and i.item_id = i2b.item_id
    ) 
    and not exists 
    (
      select 1
      from item2bundle i2b
      inner join bundle b 
        on i2b.bundle_id = b.bundle_id
        and b.name = 'THUMBNAIL'
        and i.item_id = i2b.item_id
    ) 
EOF;
new query("itemCountWithoutThumbnail","Num Items with Original without Thumbnail",$subq,"image", new testValZero(),array("Accession")); 

$subq = <<< EOF
    and exists 
    (
      select 1
      from item2bundle i2b
      inner join bundle b 
        on i2b.bundle_id = b.bundle_id
        and b.name = 'ORIGINAL'
        and i.item_id = i2b.item_id
    ) 
EOF;
new query("itemCountWithOriginal","Num Items with Original",$subq,"image", new testValTrue(),array("Accession")); 

$subq = <<< EOF
    and exists 
    (
      select 1
      from item2bundle i2b
      inner join bundle b 
        on i2b.bundle_id = b.bundle_id
        and b.name = 'THUMBNAIL'
        and i.item_id = i2b.item_id
    ) 
EOF;
new query("itemCountWithThumbnail","Num Items with Thumbnail",$subq,"image", new testValTrue(),array("Accession")); 


$subq = <<< EOF
    and exists 
    (
      select 1
      from item2bundle i2b
      inner join bundle b 
        on i2b.bundle_id = b.bundle_id
        and b.name = 'THUMBNAIL'
        and i.item_id = i2b.item_id
      where (
        select count(*)
        from bundle2bitstream b2b
        inner join bitstream bit on b2b.bitstream_id = bit.bitstream_id
        where b2b.bundle_id = b.bundle_id 
      ) > 1
    ) 
EOF;
new query("itemCountWithMultThumbnail","Num Items with Multiple Thumbnail",$subq,"image", new testValZero(),array("Accession","GenThumb")); 

$subq = <<< EOF
    and exists 
    (
      select 1
      from item2bundle i2b
      inner join bundle b 
        on i2b.bundle_id = b.bundle_id
        and b.name = 'THUMBNAIL'
        and i.item_id = i2b.item_id
      inner join bundle2bitstream b2b on b2b.bundle_id = b.bundle_id
      inner join bitstream bit on b2b.bitstream_id = bit.bitstream_id
        and bit.size_bytes < 400
    ) 
EOF;
new query("itemCountWithTinyThumbnail","Num Items with Invalid Thumbnail (Too Small)",$subq,"image", new testValZero(),array("Accession","GenThumb")); 

$subq = <<< EOF
    and exists 
    (
      select 1
      from item2bundle i2b
      inner join bundle b 
        on i2b.bundle_id = b.bundle_id
        and b.name = 'THUMBNAIL'
        and i.item_id = i2b.item_id
      inner join bundle2bitstream b2b on b2b.bundle_id = b.bundle_id
      inner join bitstream bit on b2b.bitstream_id = bit.bitstream_id
        and bit.description = 'Generated Thumbnail'
    ) 
EOF;
new query("itemCountWithGenThumbnail","Num Items with DSpace Default Generated Thumbnail",$subq,"image", new testValZero(),array("Accession","GenThumb")); 

$subq = <<< EOF
    and exists 
    (
      select 1
      from item2bundle i2b
      inner join bundle b 
        on i2b.bundle_id = b.bundle_id
        and b.name = 'THUMBNAIL'
        and i.item_id = i2b.item_id
      inner join bundle2bitstream b2b on b2b.bundle_id = b.bundle_id
      inner join bitstream bit on b2b.bitstream_id = bit.bitstream_id
        and bit.name != (
          select bit2.name || '.jpg'
          from bitstream bit2
          inner join bundle2bitstream b2b2 on bit2.bitstream_id = b2b2.bitstream_id
          inner join bundle b2 on b2b2.bundle_id=b2.bundle_id and b2.name = 'ORIGINAL'
          inner join item2bundle i2b2 on i2b2.bundle_id=b2.bundle_id and i2b2.item_id = i.item_id
          limit 1
        ) 
    ) 
EOF;
new query("itemCountWithInvalidThumbnailName","Num Items with Invalid Thumbnail Name",$subq,"image", new testValZero(),array("Accession","OrigName","ThumbName","GenThumb")); 

$subq = <<< EOF
    and not exists 
    (
      select 1
      from item2bundle i2b
      inner join bundle b 
        on i2b.bundle_id = b.bundle_id
        and b.name = 'LICENSE'
        and i.item_id = i2b.item_id
    ) 
EOF;
new query("itemCountWithoutLicense","Num Items without License",$subq,"license", new testValTrue(),array("Accession")); 

$subq = <<< EOF
    and not exists 
    (
      select 1
      from metadatavalue m 
      where m.item_id = i.item_id
      and m.metadata_field_id = (
        select metadata_field_id from metadatafieldregistry mfr
        where mfr.element = 'creator'
      )
    ) 
EOF;
new query("itemCountWithNoCreator","Num Items with No Creator",$subq,"meta", new testValZero(),array("Accession")); 

$subq = <<< EOF
    and not exists 
    (
      select 1
      from metadatavalue m 
      where m.item_id = i.item_id
      and m.metadata_field_id = (
        select metadata_field_id from metadatafieldregistry mfr
        where mfr.element = 'title' and mfr.qualifier is null
      )
    ) 
EOF;
new query("itemCountWithNoTitle","Num Items with No Title",$subq,"meta", new testValZero(),array("Accession")); 

$subq = <<< EOF
    and not exists 
    (
      select 1
      from metadatavalue m 
      where m.item_id = i.item_id
      and m.metadata_field_id = (
        select metadata_field_id from metadatafieldregistry mfr
        where mfr.element = 'identifier' and mfr.qualifier = 'uri'
      )
    ) 
EOF;
new query("itemCountWithNoIdent","Num Items with No URI",$subq,"meta", new testValZero(),array("Accession")); 

$subq = <<< EOF
    and not exists 
    (
      select 1
      from metadatavalue m 
      where m.item_id = i.item_id
      and m.metadata_field_id = (
        select metadata_field_id from metadatafieldregistry mfr
        where mfr.element = 'publisher'
      )
    ) 
EOF;
new query("itemCountWithNoPub","Num Items with No Publisher",$subq,"meta", new testValZero(),array("Accession")); 

$subq = <<< EOF
    and not exists 
    (
      select 1
      from metadatavalue m 
      where m.item_id = i.item_id
      and m.metadata_field_id = (
        select metadata_field_id from metadatafieldregistry mfr
        where mfr.element = 'subject' and mfr.qualifier is null
      )
    ) 
EOF;
new query("itemCountWithNoSubject","Num Items with No Subject",$subq,"meta", new testValZero(),array("Accession")); 

$subq = <<< EOF
    and exists 
    (
      select 1
      from metadatavalue m 
      where m.item_id = i.item_id
      and m.metadata_field_id = (
        select metadata_field_id from metadatafieldregistry mfr
        where mfr.element = 'subject' and mfr.qualifier is null
      )
      and text_value like '%;%'
    ) 
EOF;
new query("itemCountWithCompoundSubject","Num Items with Compound Subject",$subq,"meta", new testValZero(),array("Accession","Subject")); 

$subq = <<< EOF
    and not exists 
    (
      select 1
      from metadatavalue m 
      where m.item_id = i.item_id
      and m.metadata_field_id = (
        select metadata_field_id from metadatafieldregistry mfr
        where mfr.element = 'format' and mfr.qualifier is null
      )
    ) 
EOF;
new query("itemCountWithNoFormat","Num Items with No Format",$subq,"meta", new testValZero(),array("Accession")); 

$subq = <<< EOF
    and exists 
    (
      select 1
      from metadatavalue m 
      where m.item_id = i.item_id
      and (text_value is null or text_value = '')
    ) 
EOF;
new query("itemCountWithEmptyMeta","Num Items with Empty Metadata",$subq,"meta", new testValZero(),array("Accession")); 

$subq = <<< EOF
    and exists 
    (
      select 1
      from metadatavalue m 
      where m.item_id = i.item_id
      and (text_value ~ '^.*[^ ]{50,50}.*$')
    ) 
EOF;
new query("itemCountWithLongMeta","Num Items with Long Unbreaking Metadata",$subq,"meta", new testValZero(),array("Accession","URI")); 

$subq = <<< EOF
    and exists 
    (
      select 1
      from metadatavalue m 
      where m.item_id = i.item_id
      and m.metadata_field_id = (
        select metadata_field_id from metadatafieldregistry mfr
        where mfr.element = 'description' and mfr.qualifier in ('','abstract')
      )
      and (text_value ~ '^.*(http://|https://|mailto:).*$')
    ) 
EOF;
new query("itemCountDescUrl","Num Items with URL in description or abstract",$subq,"meta", new testValZero(),array("Accession","URI")); 

$subq = <<< EOF
    and exists 
    (
      select 1
      from metadatavalue m 
      where m.item_id = i.item_id
      and m.metadata_field_id = (
        select metadata_field_id from metadatafieldregistry mfr
        where mfr.element = 'description' and mfr.qualifier = 'provenance'
      )
      and (text_value ~ '^.*No\. of bitstreams.*\.(PDF|pdf|DOC|doc|PPT|ppt|DOCX|docx|PPTX|pptx).*$')
    ) 
EOF;
new query("hasFullText","Has full text per provenance",$subq,"meta", new testValZero(),array("Provenance")); 

$subq = <<< EOF
    and exists 
    (
      select 1
      from metadatavalue m 
      where m.item_id = i.item_id
      and m.metadata_field_id = (
        select metadata_field_id from metadatafieldregistry mfr
        where mfr.element = 'description' and mfr.qualifier = 'provenance'
      )
      and (text_value !~ '^.*No\. of bitstreams.*\.(PDF|pdf|DOC|doc|PPT|ppt|DOCX|docx|PPTX|pptx).*$')
    ) 
EOF;
new query("hasNoFullText","Has no full text per provenance",$subq,"meta", new testValZero(),array("Provenance")); 

$subq = <<< EOF
    and exists 
    (
      select 1
      from metadatavalue m 
      where m.item_id = i.item_id
      and m.metadata_field_id = (
        select metadata_field_id from metadatafieldregistry mfr
        where mfr.element = 'date' and mfr.qualifier = 'accessioned'
      )
     and to_date(text_value, 'YYYY-MM-DD') > current_date - interval '1 day'
    ) 
EOF;
new query("itemLast1day","Mod last 1 day",$subq,"mod", new testValTrue(),array("Accession")); 

$subq = <<< EOF
    and exists 
    (
      select 1
      from metadatavalue m 
      where m.item_id = i.item_id
      and m.metadata_field_id = (
        select metadata_field_id from metadatafieldregistry mfr
        where mfr.element = 'date' and mfr.qualifier = 'accessioned'
      )
      and to_date(text_value, 'YYYY-MM-DD') > current_date - interval '7 day'
    ) 
EOF;
new query("itemLast7day","Mod last 7 days",$subq,"mod", new testValTrue(),array("Accession")); 

$subq = <<< EOF
    and exists 
    (
      select 1
      from metadatavalue m 
      where m.item_id = i.item_id
      and m.metadata_field_id = (
        select metadata_field_id from metadatafieldregistry mfr
        where mfr.element = 'date' and mfr.qualifier = 'accessioned'
      )
      and to_date(text_value, 'YYYY-MM-DD') > current_date - interval '30 day'
    ) 
EOF;
new query("itemLast30day","Mod last 30 days",$subq,"mod", new testValTrue(),array("Accession","Accmo")); 

$subq = <<< EOF
    and exists 
    (
      select 1
      from metadatavalue m 
      where m.item_id = i.item_id
      and m.metadata_field_id = (
        select metadata_field_id from metadatafieldregistry mfr
        where mfr.element = 'date' and mfr.qualifier = 'accessioned'
      )
      and to_date(text_value, 'YYYY-MM-DD') > current_date - interval '60 day'
    ) 
EOF;
new query("itemLast60day","Mod last 60 days",$subq,"mod", new testValTrue(),array("Accession","Accmo")); 

$subq = <<< EOF
    and exists 
    (
      select 1
      from metadatavalue m 
      where m.item_id = i.item_id
      and m.metadata_field_id = (
        select metadata_field_id from metadatafieldregistry mfr
        where mfr.element = 'date' and mfr.qualifier = 'accessioned'
      )
      and to_date(text_value, 'YYYY-MM-DD') > current_date - interval '60 day'
    ) 
EOF;
new query("itemLast90day","Mod last 90 days",$subq,"mod", new testValTrue(),array("Accession","Accmo")); 

$subq = <<< EOF
    and exists 
    (
      select 1
      from metadatavalue m 
      where m.item_id = i.item_id
      and m.metadata_field_id = (
        select metadata_field_id from metadatafieldregistry mfr
        where mfr.element = 'date' and mfr.qualifier = 'accessioned'
      )
      and to_date(text_value, 'YYYY-MM-DD') > current_date - interval '60 day'
    ) 
EOF;
new query("itemLast180day","Mod last 180 days",$subq,"mod", new testValTrue(),array("Accession","Accmo")); 

$subq = <<< EOF
    and exists 
    (
      select 1
      from metadatavalue m
      where m.item_id = i.item_id
      and m.metadata_field_id = (
        select metadata_field_id from metadatafieldregistry mfr
        where mfr.element = 'embargo' and mfr.qualifier = 'terms'
      )
    ) 
EOF;
new query("embargoTerms","Has embargo terms (metadata)",$subq,"embargo", new testValTrue(),array("EmbargoTerms","EmbargoLift","EmbargoCustom","BitRestricted", "ThumbRestricted","Private")); 

$subq = <<< EOF
    and exists 
    (
      select 1
      from metadatavalue m
      where m.item_id = i.item_id
      and m.metadata_field_id = (
        select metadata_field_id from metadatafieldregistry mfr
        where mfr.element = 'embargo' and mfr.qualifier = 'lift-date'
      )
    ) 
EOF;
new query("embargoLift","Has embargo lift date (metadata)",$subq,"embargo", new testValTrue(),array("EmbargoTerms","EmbargoLift","EmbargoCustom","BitRestricted", "ThumbRestricted","Private")); 

$subq = <<< EOF
  and i.discoverable = false
EOF;
new query("private","Private Item - Not Searchable",$subq,"embargo", new testValZero(),array("Accession","DocStream","EmbargoLift","BitRestricted", "ThumbRestricted","Private")); 

$subq = <<< EOF
    and not exists 
    (
	  select 1 
  	  from resourcepolicy 
  	  where resource_type_id=2
  	    and i.item_id=resource_id
  		and epersongroup_id = 0
  		and (start_date is null or start_date <= current_date)
  		and (end_date is null or start_date >= current_date)
    ) 
EOF;
new query("restrictedItem","Restricted Item Metadata - No Anonymous Access",$subq,"embargo", new testValZero(),array("Accession","DocStream","EmbargoLift","BitRestricted", "ThumbRestricted","Private")); 

$subq = <<< EOF
    and exists 
    (
      select 1
      from item2bundle i2b
      inner join bundle b 
        on i2b.bundle_id = b.bundle_id
        and b.name = 'ORIGINAL'
        and i.item_id = i2b.item_id
      inner join bundle2bitstream b2b on b.bundle_id = b2b.bundle_id
      inner join bitstream bit on bit.bitstream_id = b2b.bitstream_id
      where not exists (
		select 1 
  		from resourcepolicy 
  		where resource_type_id=0
  		and bit.bitstream_id=resource_id
  		and epersongroup_id = 0
  		and (start_date is null or start_date <= current_date)
  		and (end_date is null or start_date >= current_date)
      )
    ) 
EOF;
new query("restrictedOriginal","Restricted Original Bitstream - No Anonymous Access",$subq,"embargo", new testValZero(),array("Accession","DocStream","EmbargoLift","BitRestricted", "ThumbRestricted","Private")); 

$subq = <<< EOF
    and exists 
    (
      select 1
      from item2bundle i2b
      inner join bundle b 
        on i2b.bundle_id = b.bundle_id
        and b.name = 'ORIGINAL'
        and i.item_id = i2b.item_id
      inner join bundle2bitstream b2b on b.bundle_id = b2b.bundle_id
      inner join bitstream bit on bit.bitstream_id = b2b.bitstream_id
      where exists (
		select 1 
  		from resourcepolicy 
  		where resource_type_id=0
  		and bit.bitstream_id=resource_id
  		and epersongroup_id != 0
  		and (start_date is null or start_date <= current_date)
  		and (end_date is null or start_date >= current_date)
      )
    ) 
EOF;
new query("specialAccess","Special Access Rule - Original Bitstream Accessible to a Specific Group",$subq,"embargo", new testValZero(),array("Accession","DocStream","EmbargoLift","BitRestricted", "ThumbRestricted","Private")); 

$subq = <<< EOF
    and exists 
    (
      select 1
      from item2bundle i2b
      inner join bundle b 
        on i2b.bundle_id = b.bundle_id
        and b.name = 'THUMBNAIL'
        and i.item_id = i2b.item_id
      inner join bundle2bitstream b2b on b.bundle_id = b2b.bundle_id
      inner join bitstream bit on bit.bitstream_id = b2b.bitstream_id
      where not exists (
		select 1 
  		from resourcepolicy 
  		where resource_type_id=0
  		and bit.bitstream_id=resource_id
  		and epersongroup_id = 0
  		and (start_date is null or start_date <= current_date)
  		and (end_date is null or start_date >= current_date)
      )
    ) 
EOF;
new query("restrictedThumbnail","Restricted Thumbnail - No Anonymous Access",$subq,"embargo", new testValZero(),array("Accession","DocStream","EmbargoLift","BitRestricted", "ThumbRestricted","Private")); 


$subq = <<< EOF
    and exists 
    (
      select 1
      from metadatavalue m
      where m.item_id = i.item_id
      and m.metadata_field_id = (
        select metadata_field_id from metadatafieldregistry mfr
        where mfr.element = 'embargo' and mfr.qualifier = 'custom-date'
      )
    ) 
EOF;
new query("embargoCustom","Has embargo custom date (metadata)",$subq,"embargo", new testValTrue(),array("EmbargoTerms","EmbargoLift","EmbargoCustom","BitRestricted", "ThumbRestricted","Private")); 

$subq = <<< EOF
    and exists 
    (
      select 1
      from metadatavalue m
      where m.item_id = i.item_id
      and m.metadata_field_id = (
        select metadata_field_id from metadatafieldregistry mfr
        where mfr.element = 'embargo' and mfr.qualifier = 'terms'
      )
    ) 
    and exists 
    (
      select 1
      from item2bundle i2b
      inner join bundle b 
        on i2b.bundle_id = b.bundle_id
        and b.name = 'ORIGINAL'
        and i.item_id = i2b.item_id
      inner join bundle2bitstream b2b on b.bundle_id = b2b.bundle_id
      inner join bitstream bit on bit.bitstream_id = b2b.bitstream_id
      where exists (
	    select 1 
  	    from resourcepolicy 
  	    where resource_type_id=0
  	    and bit.bitstream_id=resource_id
  	    and epersongroup_id = 0
  		and (start_date is null or start_date <= current_date)
  		and (end_date is null or start_date >= current_date)
  	  )
    )
EOF;
new query("embargoLifted","Embargo has been lifted: has embargo terms, but original bitstream is accessible to anonymous users",$subq,"embargo", new testValTrue(),array("EmbargoTerms","EmbargoLift","EmbargoCustom","BitRestricted", "ThumbRestricted","Private")); 
}
?>