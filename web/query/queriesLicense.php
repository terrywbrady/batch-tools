<?php

function initQueriesLicense() {
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
new query("itemCountLicense","Num Documentation Items",$subq,"license", new testValTrue(),array("Accession","Format","OtherName")); 

}
?>