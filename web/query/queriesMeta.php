<?php

function initQueriesMeta() {
$m = <<< EOF
(select max(length(text_value)) from metadatavalue mx where mx.item_id=i.item_id)
EOF;
auxFields::addAuxField("maxmeta", "Longest metadata field", "{$m}", "", false);
$m = <<< EOF
      (select array_to_string(array_agg('...' || substring(text_value,'(.{0,10}[^[:ascii:]].{0,10})') || '...'),'<br/>') " .
  			"from metadatavalue mx " .
  			"inner join metadatafieldregistry mfr on mx.metadata_field_id=mfr.metadata_field_id " .
  			" and mx.item_id=i.item_id)
EOF;
auxFields::addAuxField("NonAscii", "Non-Ascii characters in metadata", "{$m}", "", false);
$m = <<< EOF
(select array_to_string(array_agg('...' || substring(text_value,'(.{0,10}&#.{0,10})') || '...'),'<br/>') " .
  			"from metadatavalue mx " .
  			"inner join metadatafieldregistry mfr on mx.metadata_field_id=mfr.metadata_field_id " .
  			" and mx.item_id=i.item_id)
EOF;
auxFields::addAuxField("AmperPound", "Chacters escaped with Amper Pound", "{$m}", "", false);

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
      and (length(text_value) > 6000)
    ) 
EOF;
new query("itemCountLongMeta","Num Items with long metadata",$subq,"meta", new testValZero(),array("Accession","maxmeta")); 

$subq = <<< EOF
    and exists 
    (
      select 1
      from metadatavalue m 
      where m.item_id = i.item_id
      and text_value like '%&#%'
    ) 
EOF;
new query("hasAmperPound","Has &# in metadata",$subq,"meta", new testValZero(),array("AmperPound")); 

$subq = <<< EOF
    and exists 
    (
      select 1
      from metadatavalue m 
      where m.item_id = i.item_id
      and text_value ~ '^.*[^[:ascii:]].*$'
    ) 
EOF;
new query("hasNonAsciiCurly","Has non-ascii in metadata (includes curly quotes)",$subq,"meta", new testValZero(),array("NonAscii")); 

}

?>