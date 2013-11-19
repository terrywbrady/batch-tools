<?php
/*
Present a DSpace Tools page header.
Note that the format type ids and metadata field ids in this code are institution specific and have not been abstracted.

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

class LitHeader {
	public $title;
	public function __construct($title) {
		$this->title = $title;
	}
	
	public function litPageHeader() {
		$mode = $GLOBALS['mode'];
		echo <<< HERE
		<script	src="https://ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js"></script>
 		<script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.9.1/jquery-ui.min.js"></script>
		<script	src="../web/qcReport.js"></script>
		<link rel="stylesheet" type="text/css" href="../web/css/ui-lightness/jquery-ui-1.8.21.custom.css"></link>
		<link rel="stylesheet" type="text/css" href="../web/qcReport.css"></link>
		<title>{$this->title} {$mode}</title>
HERE;
	}
	
	public function litHeader($arr) {
		$mode = $GLOBALS['mode'];
		echo <<< HERE
		<div class="breadcrumb">
		  <a href="../web/index.php">DSpace Web Tools {$mode}</a> &gt;
HERE;
	    foreach($arr as $a) {
	    	echo "{$a} &gt;"; 
	    }
		echo <<< HERE
		  {$this->title}
		</div>
		<hr/>
		<h1 align="center">{$this->title}</h1>
HERE;
	}

	public function litFooter() {
		echo <<< HERE
		<hr/>
HERE;
	}
	
}
?>