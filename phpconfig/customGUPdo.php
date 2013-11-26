<?php
/*
This file encapsulates institution-specific business logic used within this set of tools.  It would be necessary to provide meaningful implementations of each of these custom functions.

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
include "customPdo.php";
class customGUPdo extends customPdo {
	private $ro_pass = "";
	
	public function getPdoDb() {
		return new PDO("pgsql:host=localhost;port=5432;dbname=dspace;user=dspace_ro;password=" . $this->ro_pass);
	}
	
	public function __construct($ro_pass) {
		$this->ro_pass = $ro_pass;
		parent::__construct();
	}
	public function getIngestLoc() {return "/var/data/dspace/dspace-ingest/";}

	function getDomainOptions() {
	$opt = <<< HERE
    <option value="@georgetown.edu">georgetown.edu</option>
    <option value="@law.georgetown.edu" <?php echo $law?>law.georgetown.edu</option>    
HERE;
		return $opt;
	}

  //validate the user domain provided	
  function validateDomain($domain) {	
	if (preg_match("|^@(law\.)?georgetown.edu$|", $domain) == 0) {
		$status = "Invalid Domain: " . $domain;
		return $status;
	}
	return "";
  }

  //validate the collection handle provided
  function validateCollection($coll) {
	if (preg_match("|^10822(\.[123])?/[0-9]+$|", $coll) == 0) {
		$status = "Invalid Collection Handle " . $coll;
		return $status;
	}
	return "";
  }

  //convert a community or collection's hierarchy into a readable pathname
	public function getPathName($name) {
		if ($name == "Bioethics Research Library") return "KIE";
		if (preg_match("/Digital +and +Special/", $name)) return "SCRC";
		if (preg_match("/Electronic Theses and Dissertations/", $name)) return "ETD";
		if ($name == "Georgetown University Institutional Repository") return "IR";
		if (preg_match("/Georgetown Law Library/", $name)) return "LAW";
		if (preg_match("/Undergraduate Theses/", $name)) return "UNDERGRAD";
		$name = str_replace("Georgetown University","GU",$name);
		$name = str_replace("Edmund A. Walsh School of Foreign Service","SFS",$name);
		$name = str_replace("Dean Peter Krogh Foreign Affairs Digital Archive","KROGH",$name);
		$name = str_replace("Tocqueville Forum on the Roots of American Democracy","TOCQUEVILLE",$name);
		$name = str_replace("Institute for the Study of International Migration","ISIM",$name);
		$name = str_replace("Mortara Center for International Studies","MORTARA",$name);
		$name = str_replace("Joseph M. Lauinger Library","LAU",$name);
		$name = str_replace("Center for Social Justice, Research and Service","SOC-JUS",$name);
		$name = str_replace("U.S. Practice in International Law","INT-LAW",$name);
		$name = str_replace("School of Foreign Service","SFS",$name);
		return $name;
	}
	
	
}
?>