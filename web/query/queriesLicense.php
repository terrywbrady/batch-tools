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

}
?>