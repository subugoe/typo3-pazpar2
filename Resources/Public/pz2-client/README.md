# pazpar2-js-client
A JavaScript client to interact with [Index Data’s pazpar2](http://www.indexdata.com/pazpar2) metasearch software and to display its search results.

2010-2013 by [Sven-S. Porst](http://earthlingsoft.net/ssp/), [SUB Göttingen](http://www.sub.uni-goettingen.de) [<porst@sub.uni-goettingen.de](mailto:porst@sub.uni-goettingen.de?subject=pz2-client)



## Examples
An example implementation of the markup for use in the TYPO3 content management system is provided by the »pazpar2« TYPO3 extension, available via the [TYPO3 Extension Repository](http://typo3.org/extensions/repository/view/pazpar2/current/) as well as [github](https://github.com/ssp/typo3-pazpar2).

This extension and the markup it creates can be seen in use on the [Lib AAC](http://aac.sub.uni-goettingen.de/) website.



## Setup
Include »pz2.js«, »pz2-client.js« and »pz2.css« in your HTML file to load the relevant resources. Preserving the order of the JavaScript files is important.

For the scripts to operate successfully, you will need:

* '''for direct pazpar2 use:''' a pazpar2 server (or a reverse proxy forwarding to it) set up at the path `/pazpar2/search.pz2` of your web server [[example apache configuration]](https://raw.github.com/ssp/pazpar2-extras/blob/master/fileadmin/apache/pazpar2.conf). This can be overriden by setting the JavaScript variable `pazpar2Path` before loading pz2-client.js.
* '''for pazpar2 use through Service Proxy:''' Service Proxy (or a reverse proxy forwarding to it) set up at the path `/service-proxy/` and Service Proxy Authentication set up at the path `/service-proxy-auth`. This can be overriden by setting the JavaScript variables `serviceProxyPath` and `serviceProxyAuthPath` before loading pz2-client.js.
* your results will be best if you use the same metadata fields we do. These are based on the ones provided by tmarc.xsl and augmented with additional fields in a few areas. The full list of fields can be found in the Readme of our [TYPO3 Extension](https://github.com/ssp/typo3-pazpar2).
* [jQuery 1.7.1](http://jquery.com/) or higher included in your site
* [flot](http://code.google.com/p/flot/) with its selection module included in your site if you set the useHistogramForYearFacets option to true; the script is included in the repository as a submodule
* for the optional usage of ZDB journal availability information (mostly useful in Germany), you are expected to proxy [ZDB’s Journals Online & Print](http://www.zeitschriftendatenbank.de/services/journals-online-print/) service to the /zdb/ and /zdb-local/ paths of your server [[example apache configuration]](https://raw.github.com/ssp/pazpar2-extras/blob/master/fileadmin/apache/zdb.conf).
* your web page to call pz2ClientDomReady on domready

A number of parameters can be set for the scripts. To be set after including »pz2.js« but before including »pz2-client.js«:

* when querying pazpar2 directly:
** `my_serviceID` (string): the pazpar2 service to use
** `pazpar2Path` (string, default: `/pazpar2/search.pz2): the path of the pazpar2 service on the web server
* when querying pazpar2 through Service Proxy:
** `serviceProxyPath` (string, default: `/service-proxy`): the path of Service Proxy on the web server
** `serviceProxyAuthPath` (string, default: `/service-proxy-auth`): the path of Service Proxy’s authentication URL on the server`

To be set after including »pz2-client.js« if you want to override the default values:

* `useGoogleBooks` (boolean, default: `false`): whether to use Google Books cover art and preview for items with ISBN or OCLC number; if set to true, you also need to include Google’s script for Google Books
* `useMaps` (boolean, default: `false`): whether to use Google Maps to display a map with a highlight for the region covered by the item; if set to true, you also need to load Google loader
* `useZDB` (boolean, default: `false`): whether to look up journal availability at the user’s IP (in German university networks) using ZDB’s Journals Online and Print service
* `ZDBUseClientIP` (boolean, default: `true`): if true, the ZDB-JOP proxy is expected to be at `/zdb/`, if false, the ZDB-JOP proxy is expected to be at `/zdb-local/`
* `useHistogramForYearFacets` (boolean, default: `true`): if true, year facets are displayed as a histogram rather than as a list
* `provideCOinSExport` (boolean, default: `true`): if true, COinS tags are embedded with the results (for Zotero 3 and above)
* `showKVKLink` (boolean, default: `false`): if true, a link to [Karlsruher Virtueller Katalog](http://www.ubka.uni-karlsruhe.de/kvk.html) for searching German union catalogues is included with the export links
* `exportFormats` (array of strings, default: `[]`): format names for export links, allowed values are `ris`, `bibtex`, `ris-inline` and `bibtex-inline`.
* `autocompleteURLs` (object, default: `{}`): keys are search field names (e.g. all, title, person), values are URLs that can be queried for autocomplete terms
* `autocompleteSetupFunction` (function, default: `undefined`): function (URL, fieldName) that is run when setting up the autocomplete feature. Returns an object for configuring [jQuery UI’s autocomplete widget](http://api.jqueryui.com/autocomplete/). Functions `autocompleteSetupArray` for sources that return JSON arrays and `autocompleteSolrSpellcheck` for querying a Solr spellcheck component are predefined.
* `displaySort (array of objects, default: `[]`): sort order

The configuration of the [Lib AAC](http://aac.sub.uni-goettingen.de/) site can be used as an example:

	<head>
		<base href="http://aac.sub.uni-goettingen.de/">
		<script src="jquery-1.7.x.js" type="text/javascript"></script>
		<link rel="stylesheet" type="text/css" href="pz2-client/pz2.css" media="all">
		<script type="text/javascript" src="pz2-client/pz2.js"></script>
		<script type="text/javascript">
			my_serviceID = 'AAC';
		</script>
		<script type="text/javascript" src="pz2-client/pz2-client.js"></script>
		<script type="text/javascript">
			useGoogleBooks = true;
			useMaps = true;
			useZDB = true;
			ZDBUseClientIP = true;
			useHistogramForYearFacets = true;
			provideCOinSExport = true;
			showExportLinksForEachLocation = false;
			showKVKLink = true;
			useKeywords = false;
			exportFormats = ["ris","bibtex"];
			displaySort = [{"fieldName":"date","direction":"descending"},{"fieldName":"author","direction":"ascending"},{"fieldName":"title","direction":"ascending"}];
			termLists = {"xtargets":{"maxFetch":"25","minDisplay":"1"},"medium":{"maxFetch":"12","minDisplay":"1"},"language":{"maxFetch":"5","minDisplay":"1"},"filterDate":{"maxFetch":"10","minDisplay":"5"}};
		</script>
		<script type="text/javascript" src="pz2-client/flot/jquery.flot.js"></script>
		<script type="text/javascript" src="pz2-client/flot/jquery.flot.selection.js"></script>
		<script type="text/javascript" src="https://www.google.com/jsapi"></script>
		<script type="text/javascript">
			jQuery(document).ready(pz2ClientDomReady);
			google.load('books', '0');
		</script>
		<script src="http://books.google.com/books/api.js?key=notsupplied&amp;v=0" type="text/javascript"></script>
	</head>



## DOM Elements
The script expects specific DOM Elements containing its search form and serving as a container for search results. These should have the following structure:

	<div id="pazpar2">
		<div class="pz2-JSNote">No JavaScript Notice</div>
		<div class="pz2-accessNote"></div>
		<form method="get" class="pz2-searchForm pz2-basic">
			<div class="pz2-mainForm">
				<div class="pz2-fieldContainer pz2-field-all">
					<label class="pz2-textFieldLabel" for="pz2-field-all">Alle Felder</label>
					<input placeholder="" class="pz2-searchField" id="pz2-field-all" type="text" value="">
					<span class="pz2-formControls">
						<input class="pz2-submitButton" type="submit" name="submit" value="Search">
						<a class="pz2-extendedLink" href="#">extended Search</a>
					</span>
					<span class="pz2-checkbox pz2-fulltext">
						<input id="pz2-checkbox-fulltext" type="checkbox" name="querySwitchFulltext" value="1">
						<label for="pz2-checkbox-fulltext">include tables of contents</label>
					</span>
				</div>
				<div class="pz2-extraFields">
					<div class="pz2-fieldContainer pz2-field-title">
						<label class="pz2-textFieldLabel" for="pz2-field-title">Title</label>
						<input class="pz2-searchField" id="pz2-field-title" type="text" name="queryStringTitle" value="1">
						<span class="pz2-checkbox pz2-journal-only">
							<input id="pz2-checkbox-journal" type="checkbox" name="querySwitchJournalOnly" value="1">
							<label for="pz2-checkbox-journal">journal titles only</label>
						</span>
					</div>
					<div class="pz2-fieldContainer pz2-field-person">
						<label class="pz2-textFieldLabel" for="pz2-field-person">Person, Author</label>
						<input placeholder="e.g. Lincoln or Wilde, Oscar" class="pz2-searchField" id="pz2-field-person" type="text" name="queryStringPerson" value="">
					</div>
					<div class="pz2-fieldContainer pz2-field-date">
						<label class="pz2-textFieldLabel" for="pz2-field-date">Year</label>
						<input placeholder="e.g. 2004, 2004-, -2004 oder 2004-2008" class="pz2-searchField" id="pz2-field-date" type="text" name="queryStringDate" value="">
					</div>
				</div>
			</div>

			<div class="pz2-ranking">
				<select class="pz2-perPage" name="perpage" onchange="onSelectDidChange">
					<option value="10">10</option>
					<option value="20">20</option>
					<option value="50">50</option>
					<option value="100" selected="selected">100</option>
				</select>
			</div>

			<input type="hidden" name="useJS" value="no">
		</form>

		<div class="pz2-clear"></div>

		<div id="pz2-recordView">
			<div class="pz2-pager pz2-top">
				<div class="pz2-progressIndicator"></div>
				<div class="pz2-pageNumbers"></div>
				<span class="pz2-recordCount" onclick="toggleStatus();"></span>
				<div id="pz2-targetView" style="display: none">No information available yet.</div>
			</div>

			<div id="pz2-termLists"></div>
			<div id="pz2-results"></div>

			<div class="pz2-pager pz2-bottom">
				<div class="pz2-pageNumbers"></div>
			</div>
		</div>
	</div>


The markup consists of the following blocks inside the div#pazpar2:

* `.pz2-JSNote`: contains a note that is hidden by JavaScript on DOM Ready (giving a chance to inform users about JavaScript not being available)
* `.pz2-accessNote`: information about the access privilegs as supplied by the [pazpar2-access](https://github.com/ssp/pazpar2-access) script is displayed here
* `form.pz2-searchForm`: The search form:
	* pz2.css hides the »extended« fields initially and the script will handle expanding/collapsing of the form
	* `.pz2-ranking`: Hidden by default, in principle the number of records could be controlled here
* `.pz2-recordView`: The dynamic results appear in here:
	* `.pz2-pager.pz2-top`: Status information
		* `.pz2-progressIndicator`: An element that expands from nearly zero width to full width to reflect the process of the pazpar2 search
		* `.pz2-pageNumbers`: Links for paging appear in here`
		* `.pz2-recordCount`: The number of results with a hint of status information appear in here
		* `#pz2-targetView`: Extended status infomration that is revealed/hidden by clicking `.pz2-recordCount`
	* `#pz2-termLists`: Facets will appear in here
	* `#pz2-results`: The result list will appear in here
	* `.pz2-pager.pz2-bottom`: The pager is repeated at the bottom of the page
		* `.pz2-pageNumbers`



## Acknowledgements

* The [https://github.com/subugoe/sub-iconfont](media type icon font) and button graphics that included with the scripts were created by [Henrik Cederblad](http://cederbladdesign.com/).
* The included »pz2.js« script for handling the communication with pazpar2 [is a part of](http://git.indexdata.com/?p=pazpar2.git;a=blob_plain;f=js/pz2.js) Index Data’s [pazpar2](http://www.indexdata.com/pazpar2) software with tiny modifications added.