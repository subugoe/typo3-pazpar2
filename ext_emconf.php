<?php

########################################################################
# Extension Manager/Repository config file for ext "pazpar2".
#
# Auto generated 31-03-2011 15:50
#
# Manual updates:
# Only the data in the array - everything else is removed by next
# writing. "version" and "dependencies" must not be touched!
########################################################################

$EM_CONF[$_EXTKEY] = array(
	'title' => 'pazpar2',
	'description' => 'Interface to indexdata’s pazpar2 metasearch middleware',
	'category' => 'fe',
	'shy' => '',
	'version' => '0.3.0',
	'dependencies' => 'extbase,fluid',
	'conflicts' => '',
	'suggests' => 't3jquery',
	'priority' => '',
	'loadOrder' => '',
	'module' => '',
	'internal' => '',
	'state' => 'beta',
	'uploadfolder' => 0,
	'createDirs' => '',
	'modify_tables' => '',
	'clearCacheOnLoad' => 0,
	'lockType' => '',
	'author' => 'Sven-S. Porst',
	'author_email' => 'porst@sub.uni-goettingen.de',
	'author_company' => 'Göttingen State and University Library, Germany http://sub.uni-goettingen.de',
	'constraints' => array(
		'depends' => array(
			'php' => '5.2.0-0.0.0',
			'typo3' => '4.4.0-0.0.0',
			'extbase' => '1.2.0-0.0.0',
			'fluid' => '1.2.0-0.0.0',
		),
		'conflicts' => array(
		),
		'suggests' => array(
			't3jquery' => '1.8.0-',
		),
	),
	'_md5_values_when_last_written' => 'a:86:{s:17:"ext_localconf.php";s:4:"abc8";s:14:"ext_tables.php";s:4:"b6c9";s:12:"t3jquery.txt";s:4:"871d";s:40:"Classes/Controller/Pazpar2Controller.php";s:4:"54ba";s:30:"Classes/Domain/Model/Query.php";s:4:"48b4";s:40:"Classes/ViewHelpers/ResultViewHelper.php";s:4:"b858";s:41:"Configuration/FlexForms/flexform_list.xml";s:4:"a963";s:40:"Resources/Private/Language/locallang.xml";s:4:"a493";s:36:"Resources/Private/Partials/form.html";s:4:"8e81";s:46:"Resources/Private/Templates/Pazpar2/index.html";s:4:"07ca";s:30:"Resources/Public/pz2-client.js";s:4:"d4e7";s:24:"Resources/Public/pz2.css";s:4:"9e3a";s:23:"Resources/Public/pz2.js";s:4:"6a0c";s:29:"Resources/Public/flot/API.txt";s:4:"b331";s:29:"Resources/Public/flot/FAQ.txt";s:4:"c288";s:33:"Resources/Public/flot/LICENSE.txt";s:4:"4df0";s:30:"Resources/Public/flot/Makefile";s:4:"ac89";s:30:"Resources/Public/flot/NEWS.txt";s:4:"04e9";s:33:"Resources/Public/flot/PLUGINS.txt";s:4:"3fc8";s:32:"Resources/Public/flot/README.txt";s:4:"bd00";s:33:"Resources/Public/flot/excanvas.js";s:4:"5c01";s:37:"Resources/Public/flot/excanvas.min.js";s:4:"3682";s:44:"Resources/Public/flot/jquery.colorhelpers.js";s:4:"c4cf";s:48:"Resources/Public/flot/jquery.colorhelpers.min.js";s:4:"aee9";s:46:"Resources/Public/flot/jquery.flot.crosshair.js";s:4:"cd99";s:50:"Resources/Public/flot/jquery.flot.crosshair.min.js";s:4:"069d";s:48:"Resources/Public/flot/jquery.flot.fillbetween.js";s:4:"6200";s:52:"Resources/Public/flot/jquery.flot.fillbetween.min.js";s:4:"a825";s:42:"Resources/Public/flot/jquery.flot.image.js";s:4:"a8ac";s:46:"Resources/Public/flot/jquery.flot.image.min.js";s:4:"2804";s:36:"Resources/Public/flot/jquery.flot.js";s:4:"51bb";s:40:"Resources/Public/flot/jquery.flot.min.js";s:4:"bba9";s:45:"Resources/Public/flot/jquery.flot.navigate.js";s:4:"1833";s:49:"Resources/Public/flot/jquery.flot.navigate.min.js";s:4:"7e10";s:40:"Resources/Public/flot/jquery.flot.pie.js";s:4:"4cf6";s:44:"Resources/Public/flot/jquery.flot.pie.min.js";s:4:"a32e";s:43:"Resources/Public/flot/jquery.flot.resize.js";s:4:"38bd";s:47:"Resources/Public/flot/jquery.flot.resize.min.js";s:4:"0ed6";s:46:"Resources/Public/flot/jquery.flot.selection.js";s:4:"df6b";s:50:"Resources/Public/flot/jquery.flot.selection.min.js";s:4:"9ae2";s:42:"Resources/Public/flot/jquery.flot.stack.js";s:4:"e9af";s:46:"Resources/Public/flot/jquery.flot.stack.min.js";s:4:"73bf";s:43:"Resources/Public/flot/jquery.flot.symbol.js";s:4:"df5e";s:47:"Resources/Public/flot/jquery.flot.symbol.min.js";s:4:"2e29";s:46:"Resources/Public/flot/jquery.flot.threshold.js";s:4:"8c5d";s:50:"Resources/Public/flot/jquery.flot.threshold.min.js";s:4:"57c2";s:31:"Resources/Public/flot/jquery.js";s:4:"f240";s:35:"Resources/Public/flot/jquery.min.js";s:4:"ee53";s:40:"Resources/Public/flot/examples/ajax.html";s:4:"1f67";s:46:"Resources/Public/flot/examples/annotating.html";s:4:"4631";s:45:"Resources/Public/flot/examples/arrow-down.gif";s:4:"7e3c";s:45:"Resources/Public/flot/examples/arrow-left.gif";s:4:"2d72";s:46:"Resources/Public/flot/examples/arrow-right.gif";s:4:"4aa8";s:43:"Resources/Public/flot/examples/arrow-up.gif";s:4:"1df1";s:41:"Resources/Public/flot/examples/basic.html";s:4:"fdeb";s:56:"Resources/Public/flot/examples/data-eu-gdp-growth-1.json";s:4:"02c7";s:56:"Resources/Public/flot/examples/data-eu-gdp-growth-2.json";s:4:"0419";s:56:"Resources/Public/flot/examples/data-eu-gdp-growth-3.json";s:4:"aedd";s:56:"Resources/Public/flot/examples/data-eu-gdp-growth-4.json";s:4:"b310";s:56:"Resources/Public/flot/examples/data-eu-gdp-growth-5.json";s:4:"3af6";s:54:"Resources/Public/flot/examples/data-eu-gdp-growth.json";s:4:"3af6";s:57:"Resources/Public/flot/examples/data-japan-gdp-growth.json";s:4:"10cc";s:55:"Resources/Public/flot/examples/data-usa-gdp-growth.json";s:4:"27c9";s:47:"Resources/Public/flot/examples/graph-types.html";s:4:"dcb1";s:57:"Resources/Public/flot/examples/hs-2004-27-a-large_web.jpg";s:4:"f5f0";s:41:"Resources/Public/flot/examples/image.html";s:4:"701f";s:41:"Resources/Public/flot/examples/index.html";s:4:"5a04";s:52:"Resources/Public/flot/examples/interacting-axes.html";s:4:"7194";s:47:"Resources/Public/flot/examples/interacting.html";s:4:"0bd5";s:41:"Resources/Public/flot/examples/layout.css";s:4:"eddb";s:49:"Resources/Public/flot/examples/multiple-axes.html";s:4:"a127";s:44:"Resources/Public/flot/examples/navigate.html";s:4:"7661";s:47:"Resources/Public/flot/examples/percentiles.html";s:4:"61e8";s:39:"Resources/Public/flot/examples/pie.html";s:4:"47a3";s:44:"Resources/Public/flot/examples/realtime.html";s:4:"6f31";s:42:"Resources/Public/flot/examples/resize.html";s:4:"c418";s:45:"Resources/Public/flot/examples/selection.html";s:4:"508f";s:51:"Resources/Public/flot/examples/setting-options.html";s:4:"fc26";s:44:"Resources/Public/flot/examples/stacking.html";s:4:"1d64";s:43:"Resources/Public/flot/examples/symbols.html";s:4:"796b";s:48:"Resources/Public/flot/examples/thresholding.html";s:4:"7278";s:40:"Resources/Public/flot/examples/time.html";s:4:"a476";s:44:"Resources/Public/flot/examples/tracking.html";s:4:"7a60";s:50:"Resources/Public/flot/examples/turning-series.html";s:4:"9832";s:44:"Resources/Public/flot/examples/visitors.html";s:4:"50ca";s:43:"Resources/Public/flot/examples/zooming.html";s:4:"b8b8";}',
);

?>