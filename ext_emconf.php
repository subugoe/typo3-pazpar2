<?php

########################################################################
# Extension Manager/Repository config file for ext "pazpar2".
#
# Auto generated 20-09-2012 18:41
#
# Manual updates:
# Only the data in the array - everything else is removed by next
# writing. "version" and "dependencies" must not be touched!
########################################################################

$EM_CONF[$_EXTKEY] = array(
	'title' => 'pazpar2',
	'description' => 'Interface for Index Data’s pazpar2 metasearch middleware',
	'category' => 'plugin',
	'version' => '2.2.0',
	'state' => 'stable',
	'author' => 'Sven-S. Porst',
	'author_email' => 'porst@sub.uni-goettingen.de',
	'author_company' => 'Göttingen State and University Library, Germany http://www.sub.uni-goettingen.de',
	'constraints' => array(
		'depends' => array(
			'php' => '5.3.0-0.0.0',
			'typo3' => '4.5.3-0.0.0',
			'extbase' => '1.3.0-0.0.0',
			'fluid' => '1.3.0-0.0.0',
		),
		'conflicts' => array(
		),
		'suggests' => array(
			'typo3' => '4.7.1-0.0.0',
			't3jquery' => '1.8.0-0.0.0',
			'nkwgok' => '2.0.0-0.0.0',
		),
	),
	'dependencies' => 'extbase,fluid',
	'conflicts' => '',
	'suggests' => 't3jquery,nkwgok',
	'priority' => '',
	'loadOrder' => '',
	'shy' => '',
	'module' => '',
	'internal' => '',
	'uploadfolder' => 0,
	'createDirs' => '',
	'modify_tables' => '',
	'clearCacheOnLoad' => 0,
	'lockType' => '',
		'_md5_values_when_last_written' => 'a:108:{s:16:"ext_autoload.php";s:4:"c517";s:12:"ext_icon.gif";s:4:"4a8e";s:17:"ext_localconf.php";s:4:"0dd3";s:14:"ext_tables.php";s:4:"8f91";s:15:"README.markdown";s:4:"2af1";s:12:"t3jquery.txt";s:4:"652c";s:40:"Classes/Controller/Pazpar2Controller.php";s:4:"dcac";s:54:"Classes/Controller/Pazpar2neuerwerbungenController.php";s:4:"1c52";s:46:"Classes/Domain/Model/Pazpar2neuerwerbungen.php";s:4:"64a8";s:30:"Classes/Domain/Model/Query.php";s:4:"d3b0";s:28:"Classes/Service/Flexform.php";s:4:"a745";s:40:"Classes/ViewHelpers/ResultViewHelper.php";s:4:"5c07";s:35:"Configuration/FlexForms/Pazpar2.xml";s:4:"0117";s:38:"Configuration/TypoScript/constants.txt";s:4:"1712";s:34:"Configuration/TypoScript/setup.txt";s:4:"d1a3";s:49:"Resources/Private/Language/locallang-flexform.xml";s:4:"e01c";s:40:"Resources/Private/Language/locallang.xml";s:4:"3b73";s:37:"Resources/Private/Layouts/Layout.html";s:4:"654c";s:36:"Resources/Private/Partials/form.html";s:4:"3c51";s:60:"Resources/Private/Partials/neuerwerbungen-form-fieldset.html";s:4:"efae";s:51:"Resources/Private/Partials/neuerwerbungen-form.html";s:4:"69a6";s:39:"Resources/Private/Partials/results.html";s:4:"485e";s:46:"Resources/Private/Templates/Pazpar2/Index.html";s:4:"05b0";s:60:"Resources/Private/Templates/Pazpar2neuerwerbungen/Index.html";s:4:"672f";s:39:"Resources/Private/XSL/pz2-to-bibtex.xsl";s:4:"7416";s:36:"Resources/Private/XSL/pz2-to-ris.xsl";s:4:"0a90";s:43:"Resources/Public/convert-pazpar2-record.php";s:4:"9b4f";s:38:"Resources/Public/pz2-neuerwerbungen.js";s:4:"b6c3";s:50:"Resources/Public/pz2-client/button_shade_large.png";s:4:"d68e";s:56:"Resources/Public/pz2-client/button_shade_large_click.png";s:4:"04e0";s:50:"Resources/Public/pz2-client/button_shade_small.png";s:4:"c15c";s:56:"Resources/Public/pz2-client/button_shade_small_click.png";s:4:"ae7a";s:41:"Resources/Public/pz2-client/pz2-client.js";s:4:"f04f";s:47:"Resources/Public/pz2-client/pz2-media-icons.png";s:4:"220c";s:50:"Resources/Public/pz2-client/pz2-neuerwerbungen.css";s:4:"a89f";s:49:"Resources/Public/pz2-client/pz2-neuerwerbungen.js";s:4:"c55c";s:35:"Resources/Public/pz2-client/pz2.css";s:4:"1dc6";s:34:"Resources/Public/pz2-client/pz2.js";s:4:"3117";s:43:"Resources/Public/pz2-client/README.markdown";s:4:"0afd";s:64:"Resources/Public/pz2-client/converter/convert-pazpar2-record.php";s:4:"0eda";s:55:"Resources/Public/pz2-client/converter/pz2-to-bibtex.xsl";s:4:"7416";s:52:"Resources/Public/pz2-client/converter/pz2-to-ris.xsl";s:4:"0a90";s:82:"Resources/Public/pz2-client/converter/test-records/location-article-goescholar.xml";s:4:"f8f7";s:71:"Resources/Public/pz2-client/converter/test-records/location-article.xml";s:4:"5dc7";s:73:"Resources/Public/pz2-client/converter/test-records/location-book-mega.xml";s:4:"9553";s:68:"Resources/Public/pz2-client/converter/test-records/location-book.xml";s:4:"28fa";s:40:"Resources/Public/pz2-client/flot/API.txt";s:4:"2818";s:44:"Resources/Public/pz2-client/flot/excanvas.js";s:4:"e5b3";s:48:"Resources/Public/pz2-client/flot/excanvas.min.js";s:4:"ee9e";s:40:"Resources/Public/pz2-client/flot/FAQ.txt";s:4:"a80e";s:55:"Resources/Public/pz2-client/flot/jquery.colorhelpers.js";s:4:"c4cf";s:58:"Resources/Public/pz2-client/flot/jquery.flot.categories.js";s:4:"11fd";s:57:"Resources/Public/pz2-client/flot/jquery.flot.crosshair.js";s:4:"cd99";s:59:"Resources/Public/pz2-client/flot/jquery.flot.fillbetween.js";s:4:"6200";s:53:"Resources/Public/pz2-client/flot/jquery.flot.image.js";s:4:"a8ac";s:47:"Resources/Public/pz2-client/flot/jquery.flot.js";s:4:"e18b";s:56:"Resources/Public/pz2-client/flot/jquery.flot.navigate.js";s:4:"1833";s:51:"Resources/Public/pz2-client/flot/jquery.flot.pie.js";s:4:"0790";s:54:"Resources/Public/pz2-client/flot/jquery.flot.resize.js";s:4:"38bd";s:57:"Resources/Public/pz2-client/flot/jquery.flot.selection.js";s:4:"ea17";s:53:"Resources/Public/pz2-client/flot/jquery.flot.stack.js";s:4:"e9af";s:54:"Resources/Public/pz2-client/flot/jquery.flot.symbol.js";s:4:"df5e";s:57:"Resources/Public/pz2-client/flot/jquery.flot.threshold.js";s:4:"7146";s:42:"Resources/Public/pz2-client/flot/jquery.js";s:4:"f240";s:44:"Resources/Public/pz2-client/flot/LICENSE.txt";s:4:"f493";s:41:"Resources/Public/pz2-client/flot/Makefile";s:4:"ac89";s:41:"Resources/Public/pz2-client/flot/NEWS.txt";s:4:"fefe";s:44:"Resources/Public/pz2-client/flot/PLUGINS.txt";s:4:"3fc8";s:43:"Resources/Public/pz2-client/flot/README.txt";s:4:"bd00";s:51:"Resources/Public/pz2-client/flot/examples/ajax.html";s:4:"e049";s:57:"Resources/Public/pz2-client/flot/examples/annotating.html";s:4:"4631";s:56:"Resources/Public/pz2-client/flot/examples/arrow-down.gif";s:4:"7e3c";s:56:"Resources/Public/pz2-client/flot/examples/arrow-left.gif";s:4:"2d72";s:57:"Resources/Public/pz2-client/flot/examples/arrow-right.gif";s:4:"4aa8";s:54:"Resources/Public/pz2-client/flot/examples/arrow-up.gif";s:4:"1df1";s:52:"Resources/Public/pz2-client/flot/examples/basic.html";s:4:"fdeb";s:57:"Resources/Public/pz2-client/flot/examples/categories.html";s:4:"202e";s:67:"Resources/Public/pz2-client/flot/examples/data-eu-gdp-growth-1.json";s:4:"02c7";s:67:"Resources/Public/pz2-client/flot/examples/data-eu-gdp-growth-2.json";s:4:"0419";s:67:"Resources/Public/pz2-client/flot/examples/data-eu-gdp-growth-3.json";s:4:"aedd";s:67:"Resources/Public/pz2-client/flot/examples/data-eu-gdp-growth-4.json";s:4:"b310";s:67:"Resources/Public/pz2-client/flot/examples/data-eu-gdp-growth-5.json";s:4:"3af6";s:65:"Resources/Public/pz2-client/flot/examples/data-eu-gdp-growth.json";s:4:"3af6";s:68:"Resources/Public/pz2-client/flot/examples/data-japan-gdp-growth.json";s:4:"10cc";s:66:"Resources/Public/pz2-client/flot/examples/data-usa-gdp-growth.json";s:4:"27c9";s:58:"Resources/Public/pz2-client/flot/examples/graph-types.html";s:4:"dcb1";s:68:"Resources/Public/pz2-client/flot/examples/hs-2004-27-a-large_web.jpg";s:4:"f5f0";s:52:"Resources/Public/pz2-client/flot/examples/image.html";s:4:"701f";s:52:"Resources/Public/pz2-client/flot/examples/index.html";s:4:"bb2b";s:63:"Resources/Public/pz2-client/flot/examples/interacting-axes.html";s:4:"de0c";s:58:"Resources/Public/pz2-client/flot/examples/interacting.html";s:4:"344a";s:52:"Resources/Public/pz2-client/flot/examples/layout.css";s:4:"eddb";s:60:"Resources/Public/pz2-client/flot/examples/multiple-axes.html";s:4:"a127";s:55:"Resources/Public/pz2-client/flot/examples/navigate.html";s:4:"7661";s:58:"Resources/Public/pz2-client/flot/examples/percentiles.html";s:4:"61e8";s:50:"Resources/Public/pz2-client/flot/examples/pie.html";s:4:"47a3";s:55:"Resources/Public/pz2-client/flot/examples/realtime.html";s:4:"6f31";s:53:"Resources/Public/pz2-client/flot/examples/resize.html";s:4:"c418";s:56:"Resources/Public/pz2-client/flot/examples/selection.html";s:4:"508f";s:62:"Resources/Public/pz2-client/flot/examples/setting-options.html";s:4:"fc26";s:55:"Resources/Public/pz2-client/flot/examples/stacking.html";s:4:"1d64";s:54:"Resources/Public/pz2-client/flot/examples/symbols.html";s:4:"796b";s:59:"Resources/Public/pz2-client/flot/examples/thresholding.html";s:4:"7278";s:51:"Resources/Public/pz2-client/flot/examples/time.html";s:4:"a476";s:55:"Resources/Public/pz2-client/flot/examples/tracking.html";s:4:"7a60";s:61:"Resources/Public/pz2-client/flot/examples/turning-series.html";s:4:"9832";s:55:"Resources/Public/pz2-client/flot/examples/visitors.html";s:4:"50ca";s:54:"Resources/Public/pz2-client/flot/examples/zooming.html";s:4:"b8b8";}',
);

?>