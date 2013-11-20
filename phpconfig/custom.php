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
class custom {
	
	public static $INSTANCE;
	
	public function getRoot() {return "/var/www";}
	public function getQueueRoot() {return $this->getRoot() . "/queue/";}
	public function getMapRoot() {return $this->getRoot() . "/mapfile/";}
	public function getDspaceBatch() {return "sudo -u dspace " . $this->getRoot() . "/bin/dspaceBatch.sh";}
	public function getBgindicator() {return "&";}
	public function getDefuser() {return "userxx";}
	public function getIngestLoc() {return "/dev/null";}

	private $communityInit;
	
	public function getCommunityInit() {return $this->communityInit;}
	
	public function __construct() {
		$this->communityInit = RestInitializer::instance();
	}

	public static function instance() {
		if (self::$INSTANCE == null) die("Set custom::$INSTANCE");
		return self::$INSTANCE;
	}
	
	//limit user to specific domains
	function getDomainOptions() {
	$opt = <<< HERE
    <option value="@foo.bar">foo.bar</option>
HERE;
		return $opt;
	}
	
	
  //validate the user domain provided	
  function validateDomain($domain) {	
	return "";
  }
  
  //validate the collection handle provided
  function validateCollection($coll) {
	return "";
  }

  //find the appropriate directory to use for locating an ingest folder
  function getHomeDir($user) {
  	return "";
  }
  
  //convert a community or collection's hierarchy into a readable pathname
	public function getPathName($name) {
		return $name;
	}
	
	//return a short hand name for top level collections
	public function getShortName($name, $def) {
		return $name;
	}

}
?>