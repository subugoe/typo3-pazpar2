<?php

class Tx_Pazpar2_Controller_Pazpar2Controller extends Tx_Extbase_MVC_Controller_ActionController {

	public function initializeAction () {
		$this->additionalHeaderData = '<script type="text/javascript" src="/demo/js/pz2.js"></script>
<script type="text/javascript" src="/vlib-test/fileadmin/pazpar2/pz2-client.js"></script>
<link rel="stylesheet" type="text/css" href="/vlib-test/fileadmin/pazpar2/pazpar2.css">';

	}


	public function indexAction () {

	}
}

?>

