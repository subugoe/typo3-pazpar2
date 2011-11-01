/* 
 * pz2-client.js
 * 
 * Adapted from Indexdata’s js-client.js by Sven-S. Porst
 * for SUB Göttingen <porst@sub.uni-goettingen.de>
 */

var usesessions = true;
var pazpar2path = '/pazpar2/search.pz2';
var showResponseType = '';

/*	Maintain a list of all facet types so we can loop over it.
	Don't forget to also set termlist attributes in the corresponding
	metadata tags for the service.

	It is crucial for the date histogram that 'filterDate' is the last item in this list.
*/
var termLists = {
	'xtargets': {'maxFetch': 25, 'minDisplay': 1},
	'medium': {'maxFetch': 12, 'minDisplay': 1},
	'language': {'maxFetch': 6, 'minDisplay': 1},
	// 'author': {'maxFetch': 10, 'minDisplay': 1},
	'filterDate': {'maxFetch': 10, 'minDisplay': 5}
};


if (document.location.hash == '#useproxy') {
	usesessions = false;
	pazpar2path = '/service-proxy/';
	showResponseType = 'json';
}


/*	Simple-minded localisation:
	Create a hash for each language, then use the appropriate one on the page.
*/
var germanTerms = {
	// Facets
	'gefiltert': 'gefiltert',
	'Filter aufheben': 'Filter aufheben',
	'Filter # aufheben': 'Filter # aufheben',
	'Facetten': 'Facetten',
	'facet-title-xtargets': 'Kataloge',
	'facet-title-medium': 'Art',
	'facet-title-author': 'Autoren',
	'facet-title-language': 'Sprache',
	'facet-title-subject': 'Themengebiete',
	'facet-title-filterDate': 'Jahre',
	// Detail display
	'Im Katalog ansehen': 'Im Katalog ansehen.',
	'detail-label-title': 'Titel',
	'detail-label-author': 'Autor',
	'detail-label-author-plural': 'Autoren',
	'detail-label-author-clean': 'Autor',
	'detail-label-author-clean-plural': 'Autoren',
	'detail-label-other-person': 'Person',
	'detail-label-other-person-plural': 'Personen',
	'detail-label-other-person-clean': 'Person',
	'detail-label-other-person-clean-plural': 'Personen',
	'detail-label-medium': 'Art',
	'detail-label-description': 'Information',
	'detail-label-description-plural': 'Informationen',
	'detail-label-abstract': 'Abstract',
	'detail-label-series-title': 'Reihe',
	'detail-label-issn': 'ISSN',
	'detail-label-acronym-issn': 'Internationale Standardseriennummer',
	'detail-label-acronym-isbn': 'Internationale Standardbuchnummer',
	'detail-label-doi': 'DOI',
	'detail-label-acronym-doi': 'Document Object Identifier: Mit dem Link zu dieser Nummer kann das Dokument im Netz gefunden werden.',
	'detail-label-doi-plural': 'DOIs',
	'detail-label-creator': 'erfasst von',
	'detail-label-verfügbarkeit': 'Verfügbarkeit',
	'elektronisch': 'digital',
	'gedruckt': 'gedruckt',
	'Ausgabe': 'Ausgabe',
	/* Google Books status Strings from
		http://code.google.com/intl/de-DE/apis/books/examples/translated-branding-elements.html	*/
	'Google Books: Vollständige Ansicht': 'Google Books: Vollständige Ansicht',
	'Google Books: Eingeschränkte Vorschau': 'Google Books: Eingeschränkte Vorschau',
	'Vorschau schließen': 'Vorschau schließen',
	'Umschlagbild': 'Umschlagbild',
	// Download/Extra Links
	'mehr Links': 'mehr Links',
	'download-label-format-simple': 'Bibliographische Daten für diesen Treffer als * laden',
	'download-label-format-all': 'Alle Ausgaben als * laden',
	'download-label-submenu-format': 'Einzelne als * laden',
	'download-label-submenu-index-format': 'Ausgabe *',
	'download-label-ris': 'RIS',
	'download-label-bibtex': 'BibTeX',
	'KVK': 'KVK',
	'deutschlandweit im KVK suchen': 'deutschlandweit im KVK suchen',
	'&lang=de': '&lang=de',
	// Short Display
	'von': 'von',
	'In': 'In',
	// General Information
	'Suche...': 'Suche\u2026',
	'keine Suchabfrage': 'keine Suchabfrage',
	'keine Treffer gefunden': 'keine Treffer',
	'+': '+',
	'Es können nicht alle # Treffer geladen werden.': '+: Es können nicht alle # Treffer geladen werden. Bitte verwenden Sie einen spezifischeren Suchbegriff, um die Trefferzahl zu reduzieren.',
	'...': '\u2026',
	'Error indicator': '\u2022',
	'Bei der Übertragung von Daten aus # der abgefragten Kataloge ist ein Fehler aufgetreten.': '\u2022: Bei der Übertragung von Daten aus # der abgefragten Kataloge ist ein Fehler aufgetreten.',
	'In diesem Katalog gibt es noch # weitere Treffer.': 'In diesem Katalog gibt es noch # weitere Treffer, die wir nicht herunterladen und hier anzeigen können. Bitte verwenden Sie einen spezifischeren Suchbegriff, um die Trefferzahl zu reduzieren. Oder suchen Sie direkt im Katalog.',
	// Pager
	'Vorige Trefferseite anzeigen': 'Vorige Trefferseite anzeigen',
	'Nächste Trefferseite anzeigen': 'Nächste Trefferseite anzeigen',
	// Histogram Tooltip
	'Treffer': 'Treffer',
	// ZDB-JOP status labels
	'frei verfügbar': 'frei verfügbar',
	'teilweise frei verfügbar': 'teilweise frei verfügbar',
	'verfügbar': 'verfügbar',
	'teilweise verfügbar': 'teilweise verfügbar',
	'nicht verfügbar': 'nicht verfügbar',
	'diese Ausgabe nicht verfügbar': 'diese Ausgabe nicht verfügbar',
	'Informationen bei der Zeitschriftendatenbank': 'Verfügbarkeitsinformationen bei der Zeitschriftendatenbank ansehen',
	'[neuere Bände im Lesesaal 2]': '[neuere Bände im Lesesaal 2]',
	// Link tooltip
	'Erscheint in separatem Fenster.': 'Erscheint in separatem Fenster.',
	// Search Form
	'erweiterte Suche': 'erweiterte Suche',
	'einfache Suche': 'einfache Suche',
	// Status display
	'Übertragungsstatus': 'Übertragungsstatus',
	'Status:': 'Status:',
	'Aktive Abfragen:': 'Aktive Abfragen:',
	'Geladene Datensätze:': 'Geladene Datensätze:',
	'Datenbank': 'Datenbank',
	'Code': 'Statuscode',
	'Status': 'Status',
	'Geladen': 'Geladen',
	'Client_Working': 'arbeitet',
	'Client_Idle': 'fertig',
	'Client_Error': 'Fehler',
	'Client_Disconnected': 'Verbindungsabbruch'
};


var englishTerms = {
	// Facets
	'gefiltert': 'filtered',
	'Filter aufheben': 'Remove filter',
	'Filter # aufheben': 'Remove filter #',
	'Facetten': 'Facets',
	'facet-title-xtargets': 'Catalogues',
	'facet-title-medium': 'Type',
	'facet-title-author': 'Authors',
	'facet-title-language': 'Languages',
	'facet-title-subject': 'Subjects',
	'facet-title-filterDate': 'Years',
	// Detail display
	'Im Katalog ansehen': 'View in catalogue.',
	'detail-label-title': 'Title',
	'detail-label-author': 'Author',
	'detail-label-author-plural': 'Authors',
	'detail-label-author-clean': 'Author',
	'detail-label-author-clean-plural': 'Authors',
	'detail-label-other-person': 'Person',
	'detail-label-other-person-plural': 'People',
	'detail-label-other-person-clean': 'Person',
	'detail-label-other-person-clean-plural': 'People',
	'detail-label-medium': 'Type',
	'detail-label-description': 'Information',
	'detail-label-description-plural': 'Information',
	'detail-label-abstract': 'Abstract',
	'detail-label-series-title': 'Series',
	'detail-label-issn': 'ISSN',
	'detail-label-acronym-issn': 'International Standard Series Number',
	'detail-label-acronym-isbn': 'International Standard Book Number',
	'detail-label-doi': 'DOI',
	'detail-label-acronym-doi': 'Document Object Identifier: Use the link to load the document.',
	'detail-label-doi-plural': 'DOIs',
	'detail-label-creator': 'catalogued by',
	'detail-label-verfügbarkeit': 'Availability',
	'elektronisch': 'electronic',
	'gedruckt': 'printed',
	'Ausgabe': 'Edition',
	/* Google Books status Strings from
		http://code.google.com/intl/de-DE/apis/books/examples/translated-branding-elements.html	*/
	'Google Books: Vollständige Ansicht': 'Google Books: Full view',
	'Google Books: Eingeschränkte Vorschau': 'Google Books: Limited Preview',
	'Vorschau schließen': 'Close Preview',
	'Umschlagbild': 'Book Cover',
	// Short Display
	'von': 'of',
	'In': 'In',
	// Download/Extra Links
	'mehr Links': 'additional Links',
	'download-label-format-simple': 'Load bibliographic data for this result as *',
	'download-label-format-all': 'Load all Editions as *',
	'download-label-submenu-format': 'Load as *',
	'download-label-submenu-index-format': 'Record *',
	'download-label-ris': 'RIS',
	'download-label-bibtex': 'BibTeX',
	'KVK': 'KVK',
	'deutschlandweit im KVK suchen': 'search for this title in union catalogues throughout Germany (KVK)',
	'&lang=de': '&lang=en',
	// General Information
	'Suche...': 'Searching\u2026',
	'keine Treffer gefunden': 'no matching records',
	'keine Suchabfrage': 'no search query',
	'+': '+',
	'Es können nicht alle # Treffer geladen werden.': '+: There are # results, not all of which can be loaded. Please use a more specific search query to reduce the number of results.',
	'...': '\u2026',
	'Error indicator': '\u2022',
	'In diesem Katalog gibt es noch # weitere Treffer.': 'There are # additional results available in this catalogue which we cannot download and display. Please use a more specific search query.',
	// Pager
	'Vorige Trefferseite anzeigen': 'Show next page of results',
	'Nächste Trefferseite anzeigen': 'Show previous page of results',
	// Histogram Tooltip
	'Treffer': 'Treffer',
	// ZDB-JOP status labels
	'frei verfügbar': 'accessible for all',
	'teilweise frei verfügbar': 'partially accessible for all',
	'verfügbar': 'accessible',
	'teilweise verfügbar': 'partially accessible',
	'nicht verfügbar': 'not accessible',
	'diese Ausgabe nicht verfügbar': 'this issue not accessible',
	'Informationen bei der Zeitschriftendatenbank': 'View availability information at Zeitschriftendatenbank',
	'[neuere Bände im Lesesaal 2]': '[current volumes in Lesesaal 2]',
	// Link tooltip
	'Erscheint in separatem Fenster.': 'Link opens in a new window.',
	// Search Form
	'erweiterte Suche': 'Extended Search',
	'einfache Suche': 'Basic Search',
	// Status display
	'Übertragungsstatus': 'Status Information',
	'Status:': 'Status:',
	'Aktive Abfragen:': 'Active Queries:',
	'Geladene Datensätze:': 'Loaded Records:',
	'Datenbank': 'Database',
	'Code': 'Status Code',
	'Status': 'Status',
	'Gesamt': 'Loaded',
	'Client_Working': 'working',
	'Client_Idle': 'done',
	'Client_Error': 'Error',
	'Client_Disconnected': 'disconnected'
};



var localisations = {
	'de': germanTerms,
	'en': englishTerms
};



/*	localise
	Return localised term using the passed dictionary
		or the one stored in localisations variable.
	The localisation dictionary has ISO 639-1 language codes as keys.
	For each of them there can be a dictionary with terms for that language.
	In case the language dictionary is not present, the default ('de') is used.
	input:	term - string to localise
			externalDictionary (optional) - localisation dictionary
	output:	localised string
*/
function localise (term, externalDictionary) {
	var dictionary = localisations;
	if (externalDictionary) {
		dictionary = externalDictionary;
	}

	if (!pageLanguage) {
		pageLanguage = jQuery('html')[0].getAttribute('lang');
		if (!pageLanguage) {
			pageLanguage = 'de';
		}
	}

	var languageCode = pageLanguage;
	if (dictionary[pageLanguage] == null) {
		languageCode = 'de';
	}

	var localised = dictionary[languageCode][term];
	if (localised == undefined) {
		localised = term;
		// console.log('No localisation for: "' + term + '"');
	}

	return localised;
}



function my_errorHandler (error) {
	if (error.code == 1 && this.request.status == 417) {
		// session has expired, create a new one
		my_paz.init(undefined, my_paz.serviceId);
	}
}



/*	Create a parameters array and pass it to the pz2's constructor.
	Then register the form submit event with the pz2.search function.
	autoInit is set to true on default.
*/
my_paz = new pz2( {"onshow": my_onshow,
					"showtime": 1000,//each timer (show, stat, term, bytarget) can be specified this way
					"pazpar2path": pazpar2path,
					"oninit": my_oninit,
					"onstat": my_onstat,
/* We are not using pazpar2’s termlists but create our own.
					"onterm": my_onterm,
					"termlist": termListNames.join(","),
*/
					"onbytarget": my_onbytarget,
	 				"usesessions" : usesessions,
					"showResponseType": showResponseType,
					"serviceId": my_serviceID,
					"errorhandler": my_errorHandler
} );



// some state vars
var domReadyFired = false;
var pz2Initialised = false;
var pageLanguage = undefined;
var curPage = 1;
var recPerPage = 100;
var fetchRecords = 1500;
var curDetRecId = '';
var curDetRecData = null;
var curSort = [];
var curFilter = null;
var curSearchTerm = null;
var curAdditionalQueryTerms = [];
var facetData = {}; // stores faceting information as sent by pazpar2
var filterArray = {};

var hitList = {}; // local storage for the records sent from pazpar2
var displayHitList = []; // filtered and sorted list used for display
var displayHitListUpToDate = []; // list filtered for all conditions but the date used for drawing the date histogram
var targetStatus = {};


/* Default settings that can be overwritten. */

// Default sort order.
var displaySort =  [];
// Use Google Books for cover art when an ISBN or OCLC number is known?
var useGoogleBooks = false;
// Query ZDB-JOP for availability information based for items with ISSN?
// ZDB-JOP needs to be reverse-proxied to /zdb/ (passing on the client IP)
// or /zdb-local/ (passing on the server’s IP) depedning on ZDBUseClientIP.
var useZDB = false;
var ZDBUseClientIP = true;
// Display year facets using a histogram graphic?
var useHistogramForYearFacets = true;
// Name of the site that can be used, e.g. for file names of downloaded files.
var siteName = undefined;
// Add COinS elements to our results list for the benefit of zotero >= 3?
var provideCOinSExport = true;
// List of export formats we provide links for. An empty list suppresses the
// creation of export links.
var exportFormats = ['ris', 'bibtex'];
// Offer submenus with items for each location in the export links?
var showExportLinksForEachLocation = false;
// Always map SUB Göttingen links to the Opac, rather than to GVK?
var preferSUBOpac = false;



/*	my_oninit
	Callback for pz2.js called when initialisation is complete.
*/
function my_oninit() {
	my_paz.stat();
	my_paz.bytarget();
	pz2Initialised = true;
	triggerSearchForForm(null);
}



/*	appendInfoToContainer
	Convenince method to append an item to another one, even if undefineds and arrays are involved.
	input:	* info - the DOM element to insert
			* container - the DOM element to insert info to
*/
var appendInfoToContainer = function (info, container) {
	if (info != undefined && container != undefined ) {
		if (info.constructor !== Array) {
			// info is a single item
			container.appendChild(info);
		}
		else {
			for (var infoNumber in info) {
				container.appendChild(info[infoNumber]);
			}
		}
	}
}



/*	turnIntoNewWindowLink
	Add a target attribute to open in our target window and add a note
	to the title about this fact.
	The link’s title element should be set before calling this function.
	input:	link - DOM a element
	output:	DOM element of the link passed in
*/
function turnIntoNewWindowLink (link) {
	if (link) {
		link.setAttribute('target', 'pz2-linkTarget');
		jQuery(link).addClass('pz2-newWindowLink');

		var newTitle = localise('Erscheint in separatem Fenster.');
		if (link.title) {
			var oldTitle = link.title;
			newTitle = oldTitle + ' (' + newTitle + ')';
		}
		link.title = newTitle;
	}
}



/*	fieldContentsInRecord
	Returns array of data from a record's md-fieldName field.
		* special case for xtargets which is mapped to location/@name
		* special case for date which uses the date from each location rather than the merged range
	input:	fieldName - name of the field to use
			record - pazpar2 record
	output:	array with content of the field in the record
*/
function fieldContentsInRecord (fieldName, record) {
	var result = [];
	
	if ( fieldName === 'xtargets' ) {
		// special case xtargets: gather server names from location info for this
		for (var locationNumber in record.location) {
			result.push(record.location[locationNumber]['@name']);
		}
	}
	else if ( fieldName === 'date' ) {
		// special case for dates: go through locations and collect date for each edition
		for (var locationNumber in record.location) {
			var date = record.location[locationNumber]['md-date'];
			if (date) {
				if (typeof(date) === 'string') {
					result.push(date);
				}
				else if (typeof(date) === 'object') {
					for (var datenumber in date) {
						result.push(date[datenumber]);
					}
				}
			}
		}		
	}
	else {
		result = record['md-' + fieldName];
	}

	return result;
}





/*	displayLists
	Converts a given list of data to thes list used for display by:
		1. applying filters
		2. sorting
	according to the setup in the displaySort and filterArray variables.
*/
function displayLists (list) {
	
	/*	filter
		Returns filtered lists of pazpar2 records according to the current 
		filterArray. The first list are the results to display. The second list
		are the results satisfying all filters except the date ones. It is used
		for drawing the date histogram.
	
		input:	list - list of pazpar2 records
		output:	list of 2 items:
					* list of pazpar2 records matching all filters
					* list of pazpar2 records matching all non-date filters
	*/
	var filteredLists = function (listToFilter) {
		
		/*	matchesFilters
			Returns how the passed record passes the filters.
			input:	record - pazpar2 record
			output: integer - 0 if no match, 1 if matching, 2 if matching everything but date
		*/
		var matchesFilters = function (record) {
			var matches = true;
			var matchesEverythingNotTheDate = true;
			for (var facetType in termLists) {
				for (var filterIndex in filterArray[facetType]) {
					matches = false;
					matchesEverythingNotTheDate = false;
					var filterValue = filterArray[facetType][filterIndex];
					if (facetType === 'xtargets') {
						for (var locationIndex in record.location) {
							matches = (record.location[locationIndex]['@name'] == filterValue);
							if (matches) {break;}
						}
					}
					else if (facetType === 'filterDate' && filterValue.constructor == Object) {
						matchesEverythingNotTheDate = true;
						for (var dateIndex in record['md-filterDate']) {
							var recordDate = parseInt(record['md-filterDate'][dateIndex].substr(0,4));
							matches = (filterValue.from <= recordDate) && (recordDate <= filterValue.to);
							if (matches) {break;}
						}
					}
					else {
						var contents = fieldContentsInRecord(facetType, record);
						for (var index in contents) {
							matches = (String(contents[index]).toLowerCase() == filterValue.toLowerCase());
							if (matches) {break;}
						}
					}

					if (!matches) {break;}
				}

				if (!matches) {break;}
			}

			var result = (matches) ? 1 : 0;
			if (!matches && matchesEverythingNotTheDate) result = 2;
			return result;
		}


		var filteredList = [];
		var filteredUpToDateList = [];
		for (var index in listToFilter) {
			var item = listToFilter[index];
			var matchState = matchesFilters(item);
			if (matchState == 1) {
				filteredList.push(item);
			}
			if (matchState >= 1) {
				filteredUpToDateList.push(item);
			}
		}
		
		return [filteredList, filteredUpToDateList];
	}



	/*	sortFunction
		Sort function for pazpar2 records.
		Sorts by date or author according to the current setup in the displaySort variable.
		input:	record1, record2 - pazpar2 records
		output: negative/0/positive number
	*/
	var sortFunction = function(record1, record2) {
		/*	dateForRecord
			Returns the year / last year of a date range of the given pazpar2 record.
			If no year is present, the year 1000 is used to make records look old.
			input:	record - pazpar2 record
			output: Date object with year found in the pazpar2 record
		*/
		function dateForRecord (record) {
			var year;
			var dateArray = record['md-date'];
			if (dateArray) {
				var dateString = dateArray[0];
				if (dateString) {
					var yearsArray = dateString.split('-');
					var lastYear = yearsArray[yearsArray.length - 1];
					year = parseInt(lastYear);
				}
			}

			// Records without a date are treated as very old.
			if (!year) {
				year = 1000;
			}

			return year;
		}



		/*	fieldContentForSorting
			Returns a record's md-fieldName field, suitable for sorting.
				* Concatenated when several instances of the field are present.
				* All lowercase.
			input:	fieldName - name of the field to use
					record - pazpar2 record
			output: string with content of the field in the record
		*/
		function fieldContentForSorting (fieldName, record) {
			var result = String(fieldContentsInRecord(fieldName, record));
			result = result.replace(/^\W*/,'');
			result = result.toLowerCase();

			return result;
		}


		var result = 0;

		for (var sortCriterionIndex in displaySort) {
			var fieldName = displaySort[sortCriterionIndex].fieldName;
			var direction = (displaySort[sortCriterionIndex].direction === 'ascending') ? 1 : -1;

			if (fieldName == 'date') {
				var date1 = dateForRecord(record1);
				var date2 = dateForRecord(record2);
				
				result = (date1 - date2) * direction;
			}
			else {
				var string1 = fieldContentForSorting(fieldName, record1);
				var string2 = fieldContentForSorting(fieldName, record2);
				
				if (string1 == string2) {
					result = 0;
				}
				else if (string1 == undefined) {
					result = 1;
				}
				else if (string2 == undefined) {
					result = -1;
				}
				else {
					result = ((string1 < string2) ? -1 : 1) * direction;
				}
			}

			if (result != 0) {
				break;
			}
		}

		return result;
	}
	

	var result = filteredLists(list);
	result[0] = result[0].sort(sortFunction)
	return result
}



/*	updateAndDisplay
	Updates displayHitList and displayHitListUpToDate, then redraws.
*/
function updateAndDisplay () {
	var filterResults = displayLists(hitList);
	displayHitList = filterResults[0];
	displayHitListUpToDate = filterResults[1];
	display();
	updateFacetLists();
}



/*	my_onshow
	Callback for pazpar2 when data become available.
	Goes through the records and adds them to hitList.
	Regenerates displayHitList(UpToDate) and triggers redisplay.
	input:	data - result data passed from pazpar2
*/
function my_onshow (data) {
	for (var hitNumber in data.hits) {
		var hit = data.hits[hitNumber];
		var hitID = hit.recid[0];
		if (hitID) {
			var oldHit = hitList[hitID];
			if (oldHit) {
				hit.detailsDivVisible = oldHit.detailsDivVisible;
				if (oldHit.location.length == hit.location.length) {
					// preserve old details Div, if the location info hasn't changed
					hit.detailsDiv = hitList[hitID].detailsDiv;
				}
			}
			
			// Make sure the 'medium' field exists by setting it to 'other' if necessary.
			if (!hit['md-medium']) {
				hit['md-medium'] = ['other'];
			}
			
			// Create a 'filterDate' field which uses the last consecutive four digits
			// of the date and is used for faceting.
			if (hit['md-date']) {
				hit['md-filterDate'] = [];
				for (var dateIndex in hit['md-date']) {
                    var dateParts = hit['md-date'][dateIndex].match(/[0-9]{4}/g);
					if (dateParts && dateParts.length > 0) {
						hit['md-filterDate'].push(dateParts[dateParts.length - 1]);
					}
				}
			}
			// If there is no title information but series information, use the
			// first series field for the title.
			if (!(hit['md-title'] || hit['md-multivolume-title']) && hit['md-series-title']) {
				hit['md-multivolume-title'] = [hit['md-series-title'][0]];
			}
			
			hitList[hitID] = hit;
		}
	}

	updateAndDisplay();
}



/*	display
	Displays the records stored in displayHitList as short records.
*/
function display () {

	/*	markupForField
		Creates span DOM element and content for a field name; Appends it to the given container.
		input:	fieldName - string with key for a field stored in hit
				container (optional)- the DOM element we created is appended here
				prepend (optional) - string inserted before the DOM element with the field data
				append (optional) - string appended after the DOM element with the field data
		output: the DOM SPAN element that was appended
	*/
	var markupForField = function (fieldName, container, prepend, append) {
		var fieldContent = hit['md-' + fieldName];

		if (fieldContent !== undefined && container) {
			deduplicate(fieldContent);
			var span = document.createElement('span');
			jQuery(span).addClass('pz2-' + fieldName);
			span.appendChild(document.createTextNode(fieldContent.join('; ')));
		
			if (prepend) {
				container.appendChild(document.createTextNode(prepend));
			}

			container.appendChild(span);

			if (append) {
				container.appendChild(document.createTextNode(append));
			}
		}

		return span;
	}



	/*	titleInfo
		Returns DOM SPAN element with markup for the current hit's title.
		output:	DOM SPAN element
	*/
	var titleInfo = function() {
		var titleCompleteElement = document.createElement('span');
		jQuery(titleCompleteElement).addClass('pz2-title-complete');

		var titleMainElement = document.createElement('span');
		titleCompleteElement.appendChild(titleMainElement);
		jQuery(titleMainElement).addClass('pz2-title-main');
		markupForField('title', titleMainElement);
		markupForField('multivolume-title', titleMainElement, ' ');

		markupForField('title-remainder', titleCompleteElement, ' ');
		markupForField('title-number-section', titleCompleteElement, ' ');

		titleCompleteElement.appendChild(document.createTextNode('. '));

		return titleCompleteElement;
	}



	/*	authorInfo
		Returns DOM SPAN element with markup for the current hit's author information.
		The pre-formatted title-responsibility field is preferred and a list of author
			names is used as a fallback.
		output:	DOM SPAN element
	*/
	var authorInfo = function() {
		var outputText;

		if (hit['md-title-responsibility'] !== undefined) {
			// use responsibility field if available
		 	outputText = hit['md-title-responsibility'];
		}
		else if (hit['md-author'] !== undefined) {
			// otherwise try to fall back to author fields
			var authors = [];
			for (var index = 0; index < hit['md-author'].length; index++) {
				var authorname = hit['md-author'][index];
				authors.push(authorname);
			}

			outputText = authors.join('; ');
		}

		if (outputText) {
			// ensure the author designation ends with a single full stop
			var extraFullStop = '';
			if (outputText.length > 1 && outputText[outputText.length - 1] != '.') {
				extraFullStop = '.';
			}
		
			var output = document.createElement('span');
			jQuery(output).addClass('pz2-item-responsibility');
			output.appendChild(document.createTextNode(outputText + extraFullStop))
		}
		
		return output;
	}



	/*	appendJournalInfo
		Appends DOM SPAN element with the current hit's journal information to linkElement.
	*/
	var appendJournalInfo = function () {
		var output = document.createElement('span');
		jQuery(output).addClass('pz2-journal');

		var journalTitle = markupForField('journal-title', linkElement, ' ' + localise('In') + ': ');
		if (journalTitle) {
			markupForField('journal-subpart', journalTitle, ', ')
			journalTitle.appendChild(document.createTextNode('.'));
		}
	}



	/*	COinSInfo
		Creates an array of COinS spans, to be used by Zotero.
		ouput:	array of SPAN DOM elements with COinS data.
	*/
	var COinSInfo = function () {

		/*	COinSStringForObject
			Turns an Object containing arrays of strings for its keys into a
				string suitable for the title attribute of a COinS style element.
			input: data - object
			output: string
		*/
		var COinSStringForObject = function (data) {
			var infoList = [];
			for (var key in data) {
				var info = data[key];
				if (info !== undefined) {
					for (var infoIndex in info) {
						infoList.push(key + '=' + encodeURIComponent(info[infoIndex]));				
					}
				}
			}
			return infoList.join('&');
		}
		

		var coinsSpans = [];

		for (var locationIndex in hit.location) {
			var location = hit.location[locationIndex];
			var coinsData = {'ctx_ver': ['Z39.88-2004']};

			// title
			var title = '';
			if (location['md-title']) {
				title += location['md-title'][0];
			}
			if (location['md-multivolume-title']) {
				title += ' ' + location['md-multivolume-title'][0];
			}
			if (location['md-title-remainder']) {
				title += ' ' + location['md-title-remainder'][0];
			}
			
			// format info
			if (location['md-medium'] && location['md-medium'][0] == 'article') {
				coinsData['rft_val_fmt'] = ['info:ofi/fmt:kev:mtx:journal'];
				coinsData['rft.genre'] = ['article'];
				coinsData['rft.atitle'] = [title];
				coinsData['rft.jtitle'] = location['md-journal-title'];
				if (location['md-volume-number'] || location['md-pages-number']) {
					// We have structured volume or pages information: use that instead of journal-subpart.
					coinsData['rft.volume'] = location['md-volume-number'];
					coinsData['rft.issue'] = location['md-issue-number'];
					if (location['md-pages-number']) {
						var pageInfo = (location['md-pages-number'][0]).split('-');
						coinsData['rft.spage'] = [pageInfo[0]];
						if (pageInfo.length >= 2) {
							coinsData['rft.epage'] = [pageInfo[1]];
						}
					}
				}
				else {
					// We lack structured volume information: use the journal-subpart field.
					coinsData['rft.volume'] = location['md-journal-subpart'];
				}
			}
			else {
				coinsData['rft_val_fmt'] = ['info:ofi/fmt:kev:mtx:book'];
				coinsData['rft.btitle'] = [title];
				if (location['md-medium'] && location['md-medium'][0] == 'book')  {
					coinsData['rft.genre'] = ['book'];
				}
				else {
					coinsData['rft.genre'] = ['document'];
				}
			}
			
			// authors
			var authors = [];
			if (location['md-author']) {
				authors = authors.concat(location['md-author']);
			}
			if (location['md-other-person']) {
				authors = authors.concat(location['md-other-person']);
			}
			coinsData['rft.au'] = authors;
			
			// further fields
			coinsData['rft.date'] = location['md-date'];
			coinsData['rft.isbn'] = location['md-isbn'];
			coinsData['rft.issn'] = location['md-issn'];
			coinsData['rft.source'] = location['md-catalogue-url'];
			coinsData['rft.pub'] = location['md-publication-name'];
			coinsData['rft.place'] = location['md-publication-place'];
			coinsData['rft.series'] = location['md-series-title'];
			coinsData['rft.description'] = location['md-description'];
			coinsData['rft_id'] = [];
			if (location['md-doi']) {
				coinsData['rft_id'].push('info:doi/' + location['md-doi'][0]);
			}
			for (var URLID in location['md-electronic-url']) {
				coinsData['rft_id'].push(location['md-electronic-url'][URLID]);
			}

			var span = document.createElement('span');
			jQuery(span).addClass('Z3988');
			var coinsString = COinSStringForObject(coinsData);
			span.setAttribute('title', coinsString);
			coinsSpans.push(span);
		}

		return coinsSpans;
	}



	// Create results list.
	var OL = document.createElement('ol');
	var firstIndex = recPerPage * (curPage - 1);
	var numberOfRecordsOnPage = Math.min(displayHitList.length - firstIndex, recPerPage);
	OL.setAttribute('start', firstIndex + 1);
	jQuery(OL).addClass('pz2-resultList');

	for (var i = 0; i < numberOfRecordsOnPage; i++) {
		var hit = displayHitList[firstIndex + i];
		var LI = hit.li;
		
		if (!LI) {
			// The LI element does not exist: create it and store it with the data.
			LI = document.createElement('li');
			LI.setAttribute('id', 'recdiv_' + HTMLIDForRecordData(hit));

			var linkElement = document.createElement('a');
			LI.appendChild(linkElement);
			linkElement.setAttribute('href', '#');
			jQuery(linkElement).addClass('pz2-recordLink');
			linkElement.onclick = new Function('toggleDetails(this.id);return false;');
			linkElement.setAttribute('id', 'rec_' + HTMLIDForRecordData(hit));

			var iconElement = document.createElement('span');
			linkElement.appendChild(iconElement);
			var mediaClass = 'unknown';
			if (hit['md-medium'].length == 1) {
				mediaClass = hit['md-medium'][0];
			}
			else if (hit['md-medium'].length > 1) {
				mediaClass = 'multiple';
			}

			jQuery(iconElement).addClass('pz2-mediaIcon ' + mediaClass);
			iconElement.title = localise(mediaClass, mediaNames);

			appendInfoToContainer(titleInfo(), linkElement);
			var authors = authorInfo();
			appendInfoToContainer(authors, linkElement);

			if (hit['md-medium'] == 'article') {
				appendJournalInfo();
			}
			else {
				var spaceBefore = ' ';
				if (authors) {
					spaceBefore = ', ';
				}
				markupForField('date', linkElement, spaceBefore, '.');
			}

			if (provideCOinSExport) {
				appendInfoToContainer(COinSInfo(), LI);
			}
			hit.li = LI;
		}
		
		OL.appendChild(LI);

		if (hit.detailsDivVisible) {
			var detailsDiv = hit.detailsDiv;
			if (!detailsDiv) {
				detailsDiv = renderDetails(hit.recid[0]);
				hit.detailsDiv = detailsDiv;
			}
			appendInfoToContainer(detailsDiv, LI);
			jQuery(LI).addClass('pz2-detailsVisible');
		}
		
		

	}

	// Replace old results list
	var results = document.getElementById("pz2-results");
	jQuery(results).empty();
	results.appendChild(OL);

	updatePagers();
	
	// Let Zotero know about updated content
	if (!jQuery.browser.msie) {
		var zoteroNotification = document.createEvent('HTMLEvents');
		zoteroNotification.initEvent('ZoteroItemUpdated', true, true);
		document.dispatchEvent(zoteroNotification);
	}
}



/* 	updatePagers
	Updates pagers and record counts shown in .pz2-pager elements.
*/
function updatePagers () {
	jQuery('div.pz2-pager').each( function(index) {
			var pages = Math.ceil(displayHitList.length / recPerPage);

			// Update pager
			var jPageNumbersContainer = jQuery('.pz2-pageNumbers', this);
			jPageNumbersContainer.removeClass().addClass('pz2-pageNumbers pz2-pageCount-' + pages);
			jPageNumbersContainer.empty();
			var pageNumbersContainer = jPageNumbersContainer[0];

			var previous = document.createElement('span');
			if (curPage > 1) {
				previous = document.createElement('a');
				previous.setAttribute('href', '#');
				previous.onclick = new Function('pagerPrev();return false;');
				previous.title = localise('Vorige Trefferseite anzeigen');
			}
			jQuery(previous).addClass('pz2-prev');
			previous.appendChild(document.createTextNode('«'));
			pageNumbersContainer.appendChild(previous);

			var pageList = document.createElement('ol');
			jQuery(pageList).addClass('pz2-pages');
			pageNumbersContainer.appendChild(pageList);

			var dotsItem = document.createElement('li');
			dotsItem.appendChild(document.createTextNode('…'));

			for(var pageNumber = 1; pageNumber <= pages; pageNumber++) {
				var pageItem = document.createElement('li');
				pageList.appendChild(pageItem);
				if(pageNumber != curPage) {
					var linkElement = document.createElement('a');
					linkElement.setAttribute('href', '#');
					linkElement.onclick = new Function('showPage(' + pageNumber + ');return false;');
					linkElement.appendChild(document.createTextNode(pageNumber));
					pageItem.appendChild(linkElement);
				}
				else {
					jQuery(pageItem).addClass('pz2-currentPage');
					pageItem.appendChild(document.createTextNode(pageNumber));
				}
			}

			var nextLink = document.createElement('span');
			if (pages - curPage > 0) {
				nextLink = document.createElement('a');
				nextLink.setAttribute('href', '#');
				nextLink.onclick = new Function('pagerNext();return false;');
				nextLink.title = localise('Nächste Trefferseite anzeigen');
			}
			jQuery(nextLink).addClass('pz2-next');
			nextLink.appendChild(document.createTextNode('»'));
			pageNumbersContainer.appendChild(nextLink);

			var jRecordCount = jQuery('.pz2-recordCount');
			// Add record count information
			var infoString;
			if (displayHitList.length > 0) {
				var firstIndex = recPerPage * (curPage - 1);
				var numberOfRecordsOnPage = Math.min(displayHitList.length - firstIndex, recPerPage);
				infoString = String(firstIndex + 1) + '-'
								+ String(firstIndex + numberOfRecordsOnPage)
								+ ' ' + localise('von') + ' '
								+ String(displayHitList.length);

				// Determine transfer status and append indicators about it to
				// the result count: + for overflow, … while we are busy and
				// · for errors.
				var transfersBusy = [];
				var resultOverflow = [];
				var hasError = [];
				var totalResultCount = 0;
				var statusIndicator = '';

				for (var targetIndex in targetStatus) {
					var target = targetStatus[targetIndex];

					if (!isNaN(target.hits)) {
						totalResultCount += parseInt(target.hits);
					}

					if (target.state === 'Client_Working') {
						transfersBusy.push(target);
					}
					else if (target.state === 'Client_Idle') {
						if (target.hits > target.records) {
							resultOverflow.push(target);
						}
					}
					else if (target.state === 'Client_Error' || target.state === 'Client_Disconnected') {
						hasError.push(target);
					}
				}

				var titleText = [];
				if (resultOverflow.length > 0) {
					infoString += localise('+');
					var overflowMessage = localise('Es können nicht alle # Treffer geladen werden.');
					titleText.push(overflowMessage.replace('#', totalResultCount));
				}
				if (transfersBusy.length > 0) {
					infoString += localise('...');
				}
				if (hasError.length > 0) {
					infoString += localise('Error indicator');
					var errorMessage = localise('Bei der Übertragung von Daten aus # der abgefragten Kataloge ist ein Fehler aufgetreten.');
					titleText.push(errorMessage.replace('#', hasError.length));
				}
				jRecordCount.attr('title', titleText.join('\n'));

				// Mark results as filtered if the filterArray has a
				// non-trivial property.
				for  (var filterIndex  in filterArray) {
					if (filterArray[filterIndex] !== undefined) {
						infoString += ' [' + localise('gefiltert') + ']';
						break;
					}
				}
			}
			else {
				if (my_paz.currQuery == undefined) {
					infoString = localise('keine Suchabfrage');
				}
				else if (my_paz.activeClients == 0) {
					infoString = localise('keine Treffer gefunden');
				}
				else {
					infoString = localise('Suche...');
				}
			}

			jRecordCount.empty()
			jRecordCount.append(infoString);
		}
	);
}



/*	my_onstat
	Callback for pazpar2 status information. Updates status display.
	input:	data - object with status information from pazpar2
*/
function my_onstat(data) {
	// Display progress bar, with a minimum of 5% progress.
	var progress = (data.clients[0] - data.activeclients[0]) / data.clients[0] * 100;
	progress = Math.max(progress, 5);
	var opacityValue = (progress === 100) ? 0 : 1;
	jQuery('.pz2-pager .pz2-progressIndicator').animate({'width': progress + '%', 'opacity': opacityValue}, 'slow');

	// Update result count
	updatePagers();

	// Write out status information.
	var statDiv = document.getElementById('pz2-stat');
	if (statDiv) {
		jQuery(statDiv).empty();

		var heading = document.createElement('h4');
		statDiv.appendChild(heading);
		heading.appendChild(document.createTextNode(localise('Status:')));

		var statusText = localise('Aktive Abfragen:') + ' '
				+ data.activeclients + '/' + data.clients + ' – '
				+ localise('Geladene Datensätze:') + ' '
				+ data.records + '/' + data.hits;
		statDiv.appendChild(document.createTextNode(statusText));
	}
}



/*	facetListForType
	Creates DOM elements for the facet list of the requested type.
		Uses facet information stored in facetData.
	input:	type - string giving the facet type
			preferOriginalFacets (optional) - boolean that triggers using
				the facet information sent by pazpar2
	output:	DOM elements for displaying the list of faces
*/
function facetListForType (type, preferOriginalFacets) {
	/*	isFilteredForType
		Returns whether there is a filter for the given type.
		input:	type - string with the type's name
		output:	boolean indicating whether there is a filter or not
	*/
	var isFilteredForType = function (type) {
		var result = false;
		if (filterArray[type]) {
			result = (filterArray[type].length > 0);
		}
		return result;
	}


	/*	facetInformationForType
		Creates list with facet information.
			* information is collected from the filtered hit list.
			* list is sorted by term frequency.
		output:	list of Objects with properties 'name' and 'freq'(ency)
					(these are analogous to the Objects passed to the callback by pz2.js)
	*/
	var facetInformationForType = function (type) {
		/*	isFiltered
			Returns whether there is any filter active.
				(One may want to use pazpar2's original term lists if not.)
			output:	boolean indicating whether a filter is active
		*/
		var isFiltered = function () {
			var isFiltered = false;
			for (var filterType in filterArray) {
				isFiltered = isFilteredForType(filterType);
				if (isFiltered) {break;}
			}
			return isFiltered;
		}


		var termList = [];
		if (!isFiltered() && preferOriginalFacets) {
			termList = facetData[type]
		}
		else {
			// Loop through data ourselves to gather facet information.
			var termArray = {};
			var recordList = displayHitList;
			if (type == 'filterDate') {
				recordList = displayHitListUpToDate;
			}
			for (var recordIndex in recordList) {
				var record = recordList[recordIndex];
				var dataArray = fieldContentsInRecord(type, record);
				var countsToIncrement = {}
				for (var index in dataArray) {
					var data = dataArray[index];
					countsToIncrement[data] = true;
				}

				for (var term in countsToIncrement) {
					if (!termArray[term]) {
						termArray[term] = 0;
					}
					termArray[term]++;
				}
			}
			
			// Sort by term frequency.
			for (var term in termArray) {
				termList.push({'name': term, 'freq': termArray[term]});
			}

			if (termList.length > 0) {
				termList.sort( function(term1, term2) {
						if (term1.freq < term2.freq) {return 1;}
						else if (term1.freq == term2.freq) {
							if (term1.name < term2.name) {return -1;}
							else {return 1;}
						}
						else {return -1;}
					}
				);
	
				// Note the maximum number
				termList['maximumNumber'] = termList[0].freq;

				// Special case for dates when displaying them as a list:
				// take the most frequent items and sort by date if we are not using the histogram.
				if (type === 'filterDate' && !useHistogramForYearFacets) {
					var maximumDateFacetCount = termLists['filterDate'].maxFetch;
					if (termList.length > maximumDateFacetCount) {
						termList.splice(maximumDateFacetCount, termList.length - maximumDateFacetCount);
					}
					termList.sort( function(term1, term2) {
							return (term1.name < term2.name) ? 1 : -1;
						}
					);
				}
			}
		}

		return termList;
	}



	var facetDisplayTermsForType = function (terms, type) {
		var list = document.createElement('ol');
		
		// Loop through list of terms for the type and create an item with link for each one.
		for (var i = 0; i < terms.length && i < termLists[type].maxFetch; i++) {
			var facetName = terms[i].name;
			var item = document.createElement('li');
			list.appendChild(item);

			// Link
			var link = document.createElement('a');
			item.appendChild(link);
			link.setAttribute('href', '#');
			link.onclick = new Function('limitResults("' + type + '","' + facetName.replace(/"/g, '\\"') + '");return false;');

			// 'Progress bar'
			var progressBar = document.createElement('div');
			link.appendChild(progressBar);
			var progress = terms[i].freq / terms['maximumNumber'] * 100;
			progressBar.setAttribute('style', 'width:' + progress + '%;');
			jQuery(progressBar).addClass('pz2-progressIndicator');

			// Facet Display Name
			var facetDisplayName = facetName;
			if (type === 'xtargets') {
				facetDisplayName = localise(facetName, catalogueNames);
			}
			else if (type === 'language') {
				facetDisplayName = localise(facetName, languageNames);
			}
			else if (type === 'medium') {
				facetDisplayName = localise(facetName, mediaNames);
			}
			var textSpan = document.createElement('span');
			link.appendChild(textSpan);
			jQuery(textSpan).addClass('pz2-facetName');
			textSpan.appendChild(document.createTextNode(facetDisplayName));

			// Hit Count
			var count = document.createElement('span');
			link.appendChild(count);
			jQuery(count).addClass('pz2-facetCount');
			count.appendChild(document.createTextNode(terms[i].freq));
			var target = targetStatus[facetName];
			if (type === 'xtargets' && target) {
				if (target.state === 'Client_Idle') {
					// When the client is finished with data transfers, check whether
					// we need to add the overflow indicator.
					var hitOverflow = target.hits - target.records;
					if (hitOverflow > 0) {
						count.appendChild(document.createTextNode(localise('+')));
						var titleString = localise('In diesem Katalog gibt es noch # weitere Treffer.');
						titleString = titleString.replace('#', hitOverflow);
						item.title = titleString;
					}
				}
				else if (target.state === 'Client_Working') {
					// While transfers from the target are still running, append an
					// ellipsis to indicate that we are busy.
					count.appendChild(document.createTextNode(localise('...')));
				}
				else if (target.state === 'Client_Error' || target.state === 'Client_Disconnected') {
					// If an error occurred for the target, indicate that.
					count.appendChild(document.createTextNode(localise('Error indicator')));
				}
			}

			// Media icons
			if (type === 'medium') {
				var mediaIcon = document.createElement('span');
				link.appendChild(mediaIcon);
				jQuery(mediaIcon).addClass('pz2-mediaIcon ' + facetName);
			}

			// Mark facets which are currently active and add button to remove faceting.
			if (isFilteredForType(type)) {
				for (var filterIndex in filterArray[type]) {
					if (facetName === filterArray[type][filterIndex]) {
						jQuery(item).addClass('pz2-activeFacet');
						var cancelLink = document.createElement('a');
						item.appendChild(cancelLink);
						cancelLink.setAttribute('href', '#');
						jQuery(cancelLink).addClass('pz2-facetCancel');
						cancelLink.onclick = new Function('delimitResults("' + type + '","' + facetName.replace(/"/g, '\\"') + '"); return false;');
						cancelLink.appendChild(document.createTextNode(localise('Filter aufheben')));
						break;
					}
				}
			}
		}
		
		return list;
	}


	var appendFacetHistogramForDatesTo = function (terms, container) {
		if (isFilteredForType('filterDate')) {
			var cancelLink = document.createElement('a');
			container.appendChild(cancelLink);
			cancelLink.setAttribute('href', '#');
			jQuery(cancelLink).addClass('pz2-facetCancel pz2-activeFacet');
			cancelLink.onclick = new Function('delimitResults("filterDate"); return false;');
			var yearRange = filterArray['filterDate'][0].from + '-' + filterArray['filterDate'][0].to;
			var cancelLinkText = localise('Filter # aufheben').replace('#', yearRange);
			cancelLink.appendChild(document.createTextNode(cancelLinkText));
		}

		var graphDiv = document.createElement('div');
		container.appendChild(graphDiv);
		jQuery(graphDiv).addClass('pz2-histogramContainer');
		var graphWidth = jQuery('#pz2-termLists').width();
		var jGraphDiv = jQuery(graphDiv);
		var canvasHeight = 150;
		jGraphDiv.css({'width': graphWidth + 'px', 'height': canvasHeight + 'px', 'position': 'relative'});

		var graphData = [];
		for (var termIndex in terms) {
			var year = parseInt(terms[termIndex].name);
			if (year) {
				graphData.push([year, terms[termIndex].freq]);
			}
		}
		
		/*	Set up xaxis with two labelled ticks, one at each end.
			Dodgy: Use whitespace to approximately position the labels in a way that they don’t
			extend beyond the end of the graph (by default they are centered at the point of
			their axis, thus extending beyond the width of the graph on one site.
		*/
		var xaxisTicks = function (axis) {
			return [[axis.datamin, '      ' + axis.datamin], [axis.datamax, axis.datamax + '      ']];
		}

		// Use the colour of term list titles for the histogram.
		var graphColour = jQuery('.pz2-termList-xtargets a').css('color');

		var graphOptions = {
			'series': {
				'bars': {
					'show': true,
					'fill': true,
					'lineWidth': 0,
					'fillColor': graphColour
				}
			},
			'xaxis':  {
				'tickDecimals': 0,
				'ticks': xaxisTicks,
				'autoscaleMargin': null
			},
			'yaxis': {
				'position': 'right',
				'tickDecimals': 0,
				'tickFormatter': function(val, axis) {return (val != 0) ? (val) : ('');},
				'labelWidth': 30
			},
			'grid': {
				'borderWidth': 0,
				'clickable': true,
				'hoverable': true
			},
			'selection': {
				'mode': 'x',
				'color': '#009'
			}
		};
		
		var plot = jQuery.plot(jGraphDiv , [{'data': graphData, 'color': graphColour}], graphOptions);

		jGraphDiv.bind('plotselected', function(event, ranges) {
			var firstYear = Math.floor(ranges.xaxis.from);
			var lastYear = Math.ceil(ranges.xaxis.to);
			ranges.xaxis.from = firstYear;
			ranges.xaxis.to = lastYear;
			plot.setSelection(ranges, true);
			filterArray['filterDate'] = undefined;
			limitResults('filterDate', ranges.xaxis);
		});

		jGraphDiv.bind('plotunselected', function() {
			delimitResults('filterDate');
		});
		
		jGraphDiv.mouseleave(function() {
			jQuery("#pz2-histogram-tooltip").remove();
		});
		
		jGraphDiv.bind('plothover', function(event, ranges, item) {
			var showTooltip = function(x, y, contents) {
				jQuery('<div id="pz2-histogram-tooltip">' + contents + '</div>').css( {
					'position': 'absolute',
					'display': 'none',
					'top': y + 5,
					'left': x + 5
				}).appendTo('body').fadeIn(200);
			}
		
			jQuery("#pz2-histogram-tooltip").remove();
			var year = Math.floor(ranges.x);
			for (termIndex in terms) {
				var term = terms[termIndex].name;
				if (term == year) {
					var hitCount = terms[termIndex].freq;
					var displayString = year + ': ' + hitCount + ' ' + localise('Treffer');
					tooltipY = jGraphDiv.offset().top + canvasHeight - 20;
                    showTooltip(ranges.pageX, tooltipY, displayString);
				}
			}
		});

		for (filterIndex in filterArray['filterDate']) {
			plot.setSelection({'xaxis': filterArray['filterDate'][filterIndex]}, true);
		}					
	}


	// Create container and heading.
	var container = document.createElement('div');
	jQuery(container).addClass('pz2-termList pz2-termList-' + type);

	var terms = facetInformationForType(type);
	if (terms.length >= termLists[type].minDisplay || filterArray[type]) {
		// Always display facet list if it is filtered. Otherwise require
		// at least .minDisplay facet elements.
		var heading = document.createElement('h5')
		container.appendChild(heading);
		var headingText = localise('facet-title-'+type);
		if (isFilteredForType(type)) {
			headingText += ' [' + localise('gefiltert') + ']';
		}
		heading.appendChild(document.createTextNode(headingText));

		// Display histogram if set up and able to do so.
		if (useHistogramForYearFacets && type == 'filterDate'
			&& (!jQuery.browser.msie || jQuery.browser.version >= 9)) {
			appendFacetHistogramForDatesTo(terms, container);
		}
		else {
			container.appendChild(facetDisplayTermsForType(terms, type));
		}
	}
		
	return container;		
}



/*	updateFacetLists
	Updates all facet lists for the facet types stored in termLists.
*/
function updateFacetLists () {
	var container = document.getElementById('pz2-termLists');
	if (container) {
		jQuery(container).empty();

		var mainHeading = document.createElement('h4');
		container.appendChild(mainHeading);
		mainHeading.appendChild(document.createTextNode(localise('Facetten')));

		for (var facetType in termLists ) {
			container.appendChild(facetListForType(facetType));
		}
	}
}



/*	my_onterm
	pazpar2 callback for receiving facet data.
		Stores facet data and recreates facets on page.
	input:	data - Array with facet information.
*/
function my_onterm (data) {
	facetData = data;
}


function my_onrecord(data) {
}



/*	my_onbytarget
	Callback for target status information. Updates the status display.
	input:	data - list coming from pazpar2
*/
function my_onbytarget(data) {
	var targetDiv = document.getElementById('pz2-targetView');
	jQuery(targetDiv).empty();

	var table = document.createElement('table');
	targetDiv.appendChild(table);

	var caption = document.createElement('caption');
	table.appendChild(caption);
	caption.appendChild(document.createTextNode(localise('Übertragungsstatus')))

	var thead = document.createElement('thead');
	table.appendChild(thead);
	var tr = document.createElement('tr');
	thead.appendChild(tr);
	var td = document.createElement('th');
	tr.appendChild(td);
	td.appendChild(document.createTextNode(localise('Datenbank')));
	td.id = 'pz2-target-name';
	td = document.createElement('th');
	tr.appendChild(td);
	td.appendChild(document.createTextNode(localise('Geladen')));
	td.id = 'pz2-target-loaded';
	td = document.createElement('th');
	tr.appendChild(td);
	td.appendChild(document.createTextNode(localise('Treffer')));
	td.id = 'pz2-target-hits';
	td = document.createElement('th');
	tr.appendChild(td);
	jQuery(td).addClass('pz2-target-status');
	td.appendChild(document.createTextNode(localise('Status')));
	td.id = 'pz2-target-status';
	
	var tbody = document.createElement('tbody');
	table.appendChild(tbody);

	for (var i = 0; i < data.length; i++ ) {
		tr = document.createElement('tr');
		tbody.appendChild(tr);
		td = document.createElement('th');
		tr.appendChild(td);
		td.appendChild(document.createTextNode(localise(data[i].name, catalogueNames)));
		td.title = data[i].id;
		td.setAttribute('headers', 'pz2-target-name');
		td = document.createElement('td');
		tr.appendChild(td);
		td.appendChild(document.createTextNode(data[i].records));
		td.setAttribute('headers', 'pz2-target-loaded');
		td = document.createElement('td');
		tr.appendChild(td);
		var hitCount = data[i].hits;
		if (hitCount == -1) {
			hitCount = '?';
		}
		td.appendChild(document.createTextNode(hitCount));
		td.setAttribute('headers', 'pz2-target-hits');
		td = document.createElement('td');
		tr.appendChild(td);
		td.appendChild(document.createTextNode(localise(data[i].state)));
		if (data[i].diagnostic != 0) {
			td.setAttribute('title', localise('Code') + ': ' + data[i].diagnostic);
		}
		td.setAttribute('headers', 'pz2-target-status');

		targetStatus[data[i].name] = data[i];
	}

	if (my_paz.activeClients === 0) {
		// Update the facet when no more clients are active, to ensure result
		// counts and overflow indicators are up to date.
		updateFacetLists();
		// Update result count
		updatePagers();
	}

}

////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////



/*	domReady
	Called when the page is loaded. Sets up JavaScript-based search mechanism.
*/
function domReady ()  {
	domReadyFired = true;

	jQuery('.pz2-searchForm').each( function(index, form) {
			form.onsubmit = onFormSubmitEventHandler;
			jQuery('.pz2-extendedLink', form).click(addExtendedSearchForLink);
		}
	);

	jQuery('.pz2-sort, .pz2-perPage').attr('onchange', 'onSelectDidChange');
	jQuery('#pazpar2').removeClass('pz2-noJS');

	triggerSearchForForm(null);
}



/*	onFormSubmitEventHandler
	Called when the search button is pressed.
*/
function onFormSubmitEventHandler () {
	triggerSearchForForm(this);
	return false;
}



/*	onSelectDidChange
	Called when sort-order popup menu is changed.
		Gathers new sort-order information and redisplays.
*/
function onSelectDidChange () {
	loadSelectsInForm(this.form);
	updateAndDisplay();
	return false;
}



/*	resetPage
	Empties result lists and filters, switches to first page and redisplays.
*/
function resetPage() {
	curPage = 1;
	hitList = {};
	displayHitList = [];
	filterArray = {};
	jQuery('.pz2-pager .pz2-progressIndicator').css({'width': 0});
	updateAndDisplay();
}



/*	triggerSearchForForm
	Trigger pazpar2 search.
	Called when my_paz is initialised and when the search button is clicked.
	input:	form - DOM element of the form used to trigger the search
			additionalQueryTerms - array of query terms not entered in the form [optional]
*/
function triggerSearchForForm (form, additionalQueryTerms) {

	/*	addSearchStringForFieldToArray
		Creates the appropriate search string for the passed field name and
			adds it to the passed array.
		pazpar2-style search strings are 'fieldname=searchTerm'.

		inputs:	fieldName - string
				array - array containing the search strings
	*/
	var addSearchStringForFieldToArray = function (fieldName, array) {
		var searchString = jQuery('#pz2-field-' + fieldName, myForm).val()
		if (searchString && searchString != '') {
			searchString = jQuery.trim(searchString);
			if (fieldName == 'all') {
				if (jQuery('#pz2-checkbox-fulltext:checked', myForm).length > 0) {
					searchString = 'fulltext=' + searchString;
				}
			}
			else if (fieldName == 'title' && jQuery('#pz2-checkbox-journal:checked', myForm).length > 0) {
				// Special case for title restricted to journals only.
				searchString = 'journal=' + searchString;
			}
			else if (fieldName == 'person') {
				// Special case for person search: do a phrase search.
				searchString = fieldName + '="' + searchString + '"';
			}
			else {
				searchString = fieldName + '=' + searchString;
			}
			
			array.push(searchString);
		}
	}

	var myForm = form;
	// If no form is passed use the first .pz2-mainForm.
	if (myForm === undefined) {
		var mainForms = jQuery('.pz2-mainForm form');
		if (mainForms.length > 0) {
			myForm = mainForms[0];
		}
	}

	// Deal with additional query terms if there are any.
	if (additionalQueryTerms !== undefined) {
		curAdditionalQueryTerms = additionalQueryTerms;
	}

	if (domReadyFired && pz2Initialised) {
		var searchChunks = [];
		addSearchStringForFieldToArray('all', searchChunks);
		addSearchStringForFieldToArray('title', searchChunks);
		addSearchStringForFieldToArray('person', searchChunks);
		addSearchStringForFieldToArray('date', searchChunks);
		searchChunks = searchChunks.concat(curAdditionalQueryTerms)
		var searchTerm = searchChunks.join(' and ');
		searchTerm = searchTerm.replace('*', '?')
		if ( searchTerm != '' && searchTerm != curSearchTerm ) {
			loadSelectsFromForm(myForm);
			my_paz.search(searchTerm, fetchRecords, curSort, curFilter);
			curSearchTerm = searchTerm;
			resetPage();
		}
	}
}



/*	addExtendedSearchForLink
	Handler for link switching to extended search.
	Adds the extended search fields, moves the search button and updates
		the link to show basic search.

	input:	event - jQuery event
	output:	false
*/
function addExtendedSearchForLink (event) {
	// switch form type
	var formContainer = jQuery('.pz2-mainForm');
	jQuery(formContainer).parent('form').removeClass('pz2-basic').addClass('pz2-extended');

	// move the controls
	var controls = jQuery('.pz2-formControls', formContainer);
	jQuery('.pz2-fieldContainer:last').append(controls);

	// switch the link to a simple search link
	jQuery(this).unbind().click(removeExtendedSearchForLink).empty().text(localise('einfache Suche'));
	jQuery('.pz2-extraFields').show();

	return false;
}



/*	removeExtendedSearchForLink
	Handler for link switching to basic search.
	Removes the extended search fields, moves the search button and updates
		the link to reflect the state.

	input:	event - jQuery event
	output:	false
*/
function removeExtendedSearchForLink (event) {
	// switch form type
	var formContainer = jQuery('.pz2-mainForm');
	formContainer.parent('form').removeClass('pz2-extended').addClass('pz2-basic');

	// move the controls
	var controls = jQuery('.pz2-formControls', formContainer);
	jQuery('#pz2-field-all').after(controls);

	// switch the link to an extended search link
	jQuery(this).unbind().click(addExtendedSearchForLink).empty().text(localise('erweiterte Suche'));

	// remove extended search fields
	jQuery('.pz2-extraFields', formContainer).hide();

	return false;
}



/*	setSortCriteriaFromString
	Takes the passed sort value string with sort criteria separated by --
		and labels and value inside the criteria separated by -,
			[this strange format is owed to escaping problems when creating a Flow3 template for the form]
		parses them and sets the displaySort and curSort variables accordingly.
	If the sort form is not present, the sort order stored in displaySort is used.

	input:	sortString - string giving the sort format
*/
function setSortCriteriaFromString (sortString) {
	var curSortArray = [];

	if (sortString) {
		// The sort string exists: we get our settings from the menu.
		displaySort = [];
		var sortCriteria = sortString.split('--');

		for (var criterionIndex in sortCriteria) {
			var criterionParts = sortCriteria[criterionIndex].split('-');
			if (criterionParts.length == 2) {
				var fieldName = criterionParts[0];
				var direction = criterionParts[1];
				displaySort.push({'fieldName': fieldName,
									'direction': ((direction == 'd') ? 'descending' : 'ascending')});
				curSortArray.push(fieldName + ':' + ((direction == 'd') ? '0' : '1') );
			}
		}
	}
	else {
		// Use the default sort order set in displaySort.
		for (var displaySortIndex in displaySort) {
			var sortCriterion = displaySort[displaySortIndex];
			curSortArray.push(sortCriterion.fieldName + ':' + ((sortCriterion.direction == 'descending') ? '0' : '1'));
		}
	}

	curSort = curSortArray.join(',');
}



/*	loadSelectsFromForm
	Retrieves current settings for sort order and items per page from the form that is passed.
	input:	form - DOM element of the form to get the data from
*/
function loadSelectsFromForm (form) {
	var sortOrderString = jQuery('.pz2-sort option:selected', form).val();
	setSortCriteriaFromString(sortOrderString);

	var jPerPageSelect = jQuery('.pz2-perPage option:selected', form);
	if (jPerPageSelect.length > 0) {
		recPerPage = jPerPageSelect.val();
	}
}



/*	limitResults
	Adds a filter for term for the data of type kind. Then redisplays.
	input:	* kind - string with name of facet type
			* term - string that needs to match the facet
*/
function limitResults(kind, term) {
	if (filterArray[kind]) {
		// add alternate condition to an existing filter
		filterArray[kind].push(term);
	}
	else {
		// the first filter of its kind: create array
		filterArray[kind] = [term];
	}

	curPage = 1;
	updateAndDisplay();
	updateFacetLists();
}



/*	delimitResults
	Removes a filter for term from the data of type kind. Then redisplays.
	input:	* kind - string with name of facet type
			* term (optional) - string that shouldn't be filtered for
					all terms are removed if term is omitted.
*/
function delimitResults(kind, term) {
	if (filterArray[kind]) {
		if (term) {
			// if a term is given only delete occurrences of 'term' from the filter
			for (var index = filterArray[kind].length -1; index >= 0; index--) {
				if (filterArray[kind][index] == term) {
					filterArray[kind].splice(index,1);
				}
			}
			if (filterArray[kind].length == 0) {
				filterArray[kind] = undefined;
			}
		}
		else {
			// if no term is given, delete the complete filter
			filterArray[kind] = undefined;
		}

		updateAndDisplay();
		updateFacetLists();
	}
}



/*	showPage
	Display the results page with the given number.
		If the number is out of range, go to the first or last page instead.
	input:	pageNum - number of the page to be shown
*/
function showPage (pageNum) {
	curPage = Math.min( Math.max(0, pageNum), Math.ceil(displayHitList.length / recPerPage) );
	display();
}


/*	pagerNext
	Display the next page (if available).
*/
function pagerNext() {
	showPage (curPage + 1);
}


/*	pagerPrev
	Display the previous page (if available).
*/
function pagerPrev() {
	showPage(curPage - 1);
}





/*	toggleStatus
	Shows and hides the status display with information on all targets.
	Invoked by clicking the number of results.
	output:	false
*/
function toggleStatus() {
	var jTargetView = jQuery('#pz2-targetView');
	jQuery('#pazpar2 .pz2-recordCount').after(jTargetView);
	jTargetView.slideToggle('fast');
	return false;
}



/*	toggleDetails
	Called when a list item is clicked.
		Reveals/Hides the detail information for the record.
		Detail information is created when it is first needed and then stored with the record.
	input:	prefixRecId - string of the form rec_RECORDID coming from the DOM ID	
*/
function toggleDetails (prefixRecId) {
	var recordIDHTML = prefixRecId.replace('rec_', '');
	var recordID = recordIDForHTMLID(recordIDHTML);
	var record = hitList[recordID];
	var detailsElement = document.getElementById('det_' + recordIDHTML);
	var extraLinks = jQuery('.pz2-extraLinks', record.detailsDiv);
	var parent = document.getElementById('recdiv_'+ recordIDHTML);

	if (record.detailsDivVisible) {
		// Detailed record information is present: remove it
		extraLinks.fadeOut('fast');
		jQuery(detailsElement).slideUp('fast');
		record.detailsDivVisible = false;
		jQuery(parent).removeClass('pz2-detailsVisible');
	}
	else {
		// Create detail view if it doesn’t exist yet.
		if (!record.detailsDiv) {
			record.detailsDiv = renderDetails(recordID);
		}

		// Append the detail view if it is not in the DOM.
		if (!detailsElement) {
			jQuery(record.detailsDiv).hide();
			parent.appendChild(record.detailsDiv);
		}

		extraLinks.hide();
		if (!jQuery.browser.msie || jQuery.browser.version > 7) {
			jQuery(record.detailsDiv).slideDown('fast');
			extraLinks.fadeIn('fast');
		}
		else {
			jQuery(record.detailsDiv).show();
			extraLinks.show();
		}

		record.detailsDivVisible = true;
		jQuery(parent).addClass('pz2-detailsVisible');
	}
}



/*	deduplicate
	Removes duplicate entries from an array.
	The first occurrence of any item is kept, later ones are removed.
	This function works in place and alters the original array.
	input:	information - array to remove duplicate entries from.
*/
var deduplicate = function (information) {
	if (information) {
		for (var i = 0; i < information.length; i++) {
			var item = information[i].toLowerCase();
			var isDuplicate = false;
			for (var j = 0; j < i; j++) {
				var jtem = information[j].toLowerCase();
				if (item == jtem) {
					isDuplicate = true;
					information.splice(i, 1);
					i--;
					break;
				}
			}
		}
	}
}



/*	renderDetails
	Create DIV with details information about the record passed.
		Inserts details information and handles retrieval of external data
			such as ZDB info and Google Books button.
	input:	recordID - string containing the key of the record to display details for
	output:	DIV DOM element containing the details to be displayed
*/
function renderDetails(recordID) {
	/*	markupInfoItems
		Returns marked up version of the DOM items passed, putting them into a list if necessary:
		input:	infoItems - array of DOM elements to insert
		output: * 1-element array => just the element
				* multi-element array => UL with an LI containing each of the elements
				* empty array => undefined
	*/
	var markupInfoItems = function (infoItems) {
		var result;

		if (infoItems.length == 1) {
			result = infoItems[0];
		}
		else if (infoItems.length > 1) {
			result = document.createElement('ul');
			jQuery(infoItems).each( function(index) {
					var LI = document.createElement('li');
					result.appendChild(LI);
					LI.appendChild(this);
				}
			);
		}

		return result;
	}



	/*	detailLineBasic
		input:	titleElement - DOM element containing the title
				dataElement - DOMElement with the information to be displayed
				attributes - associative array if attributes added to the resulting elements (optional)
		output: Array of DOM elements containing
				0:	DT element with the titleElement
				1:	DD element with the informationElement
	*/
	var detailLineBasic = function (titleElement, dataElement, attributes) {
		var line;
		if (titleElement && dataElement) {
			var rowTitleElement = document.createElement('dt');
			for (attributeName in attributes) {
				rowTitleElement.setAttribute(attributeName, attributes[attributeName]);
			}
			rowTitleElement.appendChild(titleElement);

			var rowDataElement = document.createElement('dd');
			for (attributeName in attributes) {
				rowDataElement.setAttribute(attributeName, attributes[attributeName]);
			}
			rowDataElement.appendChild(dataElement);
			
			line = [rowTitleElement, rowDataElement];
		}
		
		return line;
	}



	/*	detailLine
		input:	title - string with element's name
				informationElements - array of DOM elements with the information to be displayed
		output: Array of DOM elements containing
				0:	DT element with the row's title
				1:	DD element with the row's data
						If there is more than one data item, they are wrapped in a list.
	*/
	var detailLine = function (title, informationElements) {
		var line;
		if (title && informationElements) {
			var headingText;	

			if (informationElements.length == 1) {
				headingText = localise('detail-label-'+title);
			}
			else {
				var labelKey = 'detail-label-' + title + '-plural';
				var labelLocalisation = localise(labelKey);
				if (labelKey === labelLocalisation) { // no plural form, fall back to singular
					labelKey = 'detail-label-' + title;
					labelLocalisation = localise(labelKey);
				}
				headingText = labelLocalisation;
			}

			var infoItems = markupInfoItems(informationElements);

			if (infoItems) { // we have information, so insert it
				var labelNode = document.createTextNode(headingText + ':');
				var acronymKey = 'detail-label-acronym-' + title;
				if (localise(acronymKey) !== acronymKey) {
					// acronym: add acronym element
					var acronymElement = document.createElement('abbr');
					acronymElement.title = localise(acronymKey);
					acronymElement.appendChild(labelNode);
					labelNode = acronymElement;
				}

				line = detailLineBasic(labelNode, infoItems);
			}
		}

		return line;
	}



	/*	detailLineAuto
		input:	title - string with the element's name
		output:	Array of DOM elements for title and the data coming from data[md-title]
				as created by detailLine.
	*/
	var detailLineAuto = function (title) {
		var result = undefined;
		var element = DOMElementForTitle(title);

		if (element.length !== 0) {
			result = detailLine( title, element );
		}

		return result;
	}


	
	/*	linkForDOI
		input:	DOI - string with DOI
		output: DOM anchor element with link to the DOI at dx.doi.org
	*/
	var linkForDOI = function (DOI) {
		var linkElement = document.createElement('a');
		linkElement.setAttribute('href', 'http://dx.doi.org/' + DOI);
		turnIntoNewWindowLink(linkElement);
		linkElement.appendChild(document.createTextNode(DOI));

		var DOISpan = document.createElement('span');
		DOISpan.appendChild(linkElement);		

		return DOISpan;
	}



	/*	DOMElementForTitle
		input:	title - title string
		output:	nil, if the field md-title does not exist in data. Otherwise:
				array of DOM elements created from the fields of data[md-title]
	*/
	var DOMElementForTitle = function (title) {
		var result = [];
		if ( data['md-' + title] !== undefined ) {
			var theData = data['md-' + title];
			deduplicate(theData);

			for (var dataIndex in theData) {
				var rawDatum = theData[dataIndex];
				var wrappedDatum;
				switch	(title) {
					case 'doi':
						wrappedDatum = linkForDOI(rawDatum);
						break;
					default:
						wrappedDatum = document.createTextNode(rawDatum);
				}
				result.push(wrappedDatum);
			}
		}

		return result;
	}



	/*	ISSNsDetailLine
		Returns DOMElements with markup for the record’s ISSN information,
			taking into account the issn, eissn and pissn fields.

		output: Array of DOM elements containing
				0:	DT element with the row's title ISSN or ISSNs
				1:	DD element with a list of ISSNs
	*/
	var ISSNsDetailLine = function () {
		var ISSNTypes = {'issn': '', 'pissn': 'gedruckt', 'eissn': 'elektronisch'};
		var ISSNList = [];
		for (var ISSNTypeIndex in ISSNTypes) {
			var fieldName = 'md-' + ISSNTypeIndex;
			for (var ISSNIndex in data[fieldName]) {
				var ISSN = data[fieldName][ISSNIndex].substr(0,9);
				if (jQuery.inArray(ISSN, ISSNList) == -1) {
					if (ISSNTypes[ISSNTypeIndex] !== '') {
						ISSN += ' (' + localise(ISSNTypes[ISSNTypeIndex]) + ')';
					}
					ISSNList.push(ISSN);
				}
			}
		}
		
		var infoElements;
		if (ISSNList.length > 0) {
			infoElements = [document.createTextNode(ISSNList.join(', '))];
		}

		return detailLine('issn', infoElements);
	}



	/*	ZDBQuery
		Loads XML journal info from ZDB via a proxy on our own server
			(to avoid cross-domain load problems).
		Inserts the information into the DOM.

		input:	element - DOM element that the resulting information is inserted into.
	*/
	var addZDBInfoIntoElement = function (element) {
		// Do nothing if there are no ISSNs.
		var ISSN;
		if (data['md-issn'] && data['md-issn'].length > 0) {
			ISSN = data['md-issn'][0];
		}
		else if (data['md-pissn'] && data['md-pissn'].length > 0) {
			ISSN = data['md-pissn'][0];
		}
		var eISSN;
		if (data['md-eissn'] && data['md-eissn'].length > 0) {
			eISSN = data['md-eissn'][0];
		}
		
		if ( !(ISSN || eISSN) ) {return;}

		var serviceID = 'sub:vlib';
		var parameters = 'sid=' + serviceID;

		if ( ISSN ) {
			parameters += '&issn=' + ISSN;
		}
		
		if ( eISSN ) {
			parameters += '&eissn=' + eISSN;
		}

		if (data['md-medium'] == 'article') {
			parameters += '&genre=article';

			// Add additional information to request to get more precise result and better display.
			var year = data['md-date'];
			if (year) {
				var yearNumber = parseInt(year[0], 10);
				parameters += '&date=' + yearNumber;
			}

			var volume = data['md-volume-number'];
			if (volume) {
				var volumeNumber = parseInt(volume, 10);
				parameters += '&volume=' + volumeNumber;
			}

			var issue = data['md-issue-number'];
			if (issue) {
				var issueNumber = parseInt(issue, 10);
				parameters += '&issue=' + issueNumber;
			}

			var pages = data['md-pages-number'];
			if (pages) {
				parameters += '&pages=' + pages;
			}

			var title = data['md-title'];
			if (title) {
				parameters += '&atitle=' + encodeURI(title);
			}
		}
		else { // it’s a journal
			parameters += '&genre=journal';

			var journalTitle = data['md-title'];
			if (journalTitle) {
				parameters += '&title=' + encodeURI(journalTitle);
			}
		}

		// Run the ZDB query.
		var ZDBPath = '/zdb/';
		if (!ZDBUseClientIP) {
			ZDBPath = '/zdb-local/';
		}
		var ZDBURL = ZDBPath + 'full.xml?' + parameters;

		jQuery.get(ZDBURL,
			/*	AJAX callback
				Creates DOM elements with information coming from ZDB.
				input:	resultData - XML from ZDB server
				uses:	element - DOM element for inserting the information into
			*/
			function (resultData) {

				/*	ZDBInfoItemForResult
					Turns XML of a single ZDB Result a DOM element displaying the relevant information.
					input:	ZDBResult - XML Element with a Full/(Print|Electronic)Data/ResultList/Result element
					output:	DOM Element for displaying the information in ZDBResult that's relevant for us
				*/
				var ZDBInfoItemForResult = function (ZDBResult) {
					var status = ZDBResult.getAttribute('state');
					var statusText;

					// Determine the access status of the result.
					if (status == 0) {
						statusText = localise('frei verfügbar');
					}
					else if (status == 1) {
						statusText = localise('teilweise frei verfügbar');
					}
					else if (status == 2) {
						statusText = localise('verfügbar');
					}
					else if (status == 3) {
						statusText = localise('teilweise verfügbar');
					}
					else if (status == 4) {
						statusText = localise('nicht verfügbar');
					}
					else if (status == 5) {
						statusText = localise('diese Ausgabe nicht verfügbar');
					}
					else {
						/*	Remaining cases are:
								status == -1: non-unique ISSN
								status == 10: unknown
						*/
					}
					
					// Only display detail information if we do have access.
					if (statusText) {
						var statusElement = document.createElement('span');
						jQuery(statusElement).addClass('pz2-ZDBStatusInfo');

						var accessLinkURL = jQuery('AccessURL', ZDBResult);
						if (accessLinkURL.length > 0) {
							// Having an AccessURL implies this is inside ElectronicData.
							statusElement.appendChild(document.createTextNode(statusText));
							var accessLink = document.createElement('a');
							statusElement.appendChild(document.createTextNode(' – '));
							statusElement.appendChild(accessLink);
							accessLink.setAttribute('href', accessLinkURL[0].textContent);
							var linkTitle = jQuery('Title', ZDBResult);
							if (linkTitle && linkTitle.length > 0) {
								linkTitle = linkTitle[0].textContent;
							}
							else {
								linkTitle = localise('Zugriff');
							}
							turnIntoNewWindowLink(accessLink);

							var additionals = [];
							var ZDBAdditionals = jQuery('Additional', ZDBResult);
							ZDBAdditionals.each( function (index) {
									additionals.push(this.textContent);
								}
							);
							if (additionals.length > 0) {
								accessLink.appendChild(document.createTextNode(additionals.join('; ') + '. '))
							}
							else {
								accessLink.appendChild(document.createTextNode(linkTitle));
							}
						}
						else if (status < 4) {
							// Absence of an AccessURL implies this is inside PrintData.
							// status > 3 means the volume is not available. Don't print info then.
							var locationInfo = document.createElement('span');
							var infoText = '';

							var period = jQuery('Period', ZDBResult)[0];
							if (period) {
								infoText += period.textContent + ': ';

							}
							var location = jQuery('Location', ZDBResult)[0];
							var locationText = '';
							if (location) {
								locationText = location.textContent;
								infoText += locationText;
							}

							var signature = jQuery('Signature', ZDBResult)[0];
							if (signature) {
								infoText += ' ' + signature.textContent;
							}
							
							if (locationText.search('Göttingen SUB') != -1 && locationText.search('LS2') != -1) {
								infoText += ' ' + localise('[neuere Bände im Lesesaal 2]');
							}

							locationInfo.appendChild(document.createTextNode(infoText));
							statusElement.appendChild(locationInfo);
						}
						else {
							statusElement = undefined;
						}
					}	
					return statusElement;
				}



				/*	appendLibraryNameFromResultDataTo
					If we there is a Library name, insert it into the target container.
					input:	* data: ElectronicData or PrintData element from ZDB XML
							* target: DOM container to which the marked up library name is appended
				*/
				var appendLibraryNameFromResultDataTo = function (data, target) {
					var libraryName = jQuery('Library', data)[0];
					if (libraryName) {
						var libraryNameSpan = document.createElement('span');
						jQuery(libraryNameSpan).addClass('pz2-ZDBLibraryName');
						libraryNameSpan.appendChild(document.createTextNode(libraryName.textContent));
						target.appendChild(libraryNameSpan);
					}
				}



				/*	ZDBInfoElement
					Coverts ZDB XML data for electronic or print journals
						to DOM elements displaying their information.
					input:	data - ElectronicData or PrintData element from ZDB XML
					output:	DOM element containing the information from data
				*/				
				var ZDBInfoElement = function (data) {
					var results = jQuery('Result', data);

					if (results.length > 0) {
						var infoItems = [];
						results.each( function(index) {
								var ZDBInfoItem = ZDBInfoItemForResult(this);
								if (ZDBInfoItem) {
									infoItems.push(ZDBInfoItem);
								}
							}
						);

						if (infoItems.length > 0) {
							var infos = document.createElement('span');
							infos.appendChild(markupInfoItems(infoItems));
						}
					}

					return infos;
				}



				/*	ZDBInformation
					Converts complete ZDB XML data to DOM element containing information about them.
					input:	data - result from ZDB XML request
					output: DOM element displaying information about journal availability.
								If ZDB figures out the local library and the journal
									is accessible there, we display:
									* its name
									* electronic journal information with access link
									* print journal information
				*/
				var ZDBInformation = function (data) {
					var container;

					var electronicInfos = ZDBInfoElement( jQuery('ElectronicData', data) );
					var printInfos = ZDBInfoElement( jQuery('PrintData', data) );
					
					if (electronicInfos || printInfos) {
						container = document.createElement('div');
						if (ZDBUseClientIP) {
							appendLibraryNameFromResultDataTo(data, container);
						}
					}

					if (electronicInfos) {
						var electronicHeading = document.createElement('h5');
						container.appendChild(electronicHeading);
						electronicHeading.appendChild(document.createTextNode(localise('elektronisch')));
						container.appendChild(electronicInfos);
					}

					if (printInfos) {
						var printHeading = document.createElement('h5');
						container.appendChild(printHeading);
						printHeading.appendChild(document.createTextNode(localise('gedruckt')));
						container.appendChild(printInfos);
					}

					return container;
				}



				var availabilityLabel = document.createElement('a');
				var ZDBLinkURL = 'http://services.d-nb.de/fize-service/gvr/html-service.htm?' + parameters;
				availabilityLabel.setAttribute('href', ZDBLinkURL);
				availabilityLabel.title = localise('Informationen bei der Zeitschriftendatenbank');
				turnIntoNewWindowLink(availabilityLabel);
				availabilityLabel.appendChild(document.createTextNode(localise('detail-label-verfügbarkeit') + ':'));

				var infoBlock = ZDBInformation(resultData);

				var infoLineElements = detailLineBasic(availabilityLabel, infoBlock, {'class':'pz2-ZDBInfo'});
				var jInfoLineElements = jQuery(infoLineElements);
				jInfoLineElements.hide();
				appendInfoToContainer(infoLineElements, element);
				if (!jQuery.browser.msie || jQuery.browser.version > 7) {
					jInfoLineElements.slideDown('fast');
				}
				else {
					jInfoLineElements.show();
				}
			}
		);
	}
	



	/*	appendGoogleBooksElementTo
		Figure out whether there is a Google Books Preview for the current data.
		input:	DL DOM element that an additional item can be appended to
	*/
	var appendGoogleBooksElementTo = function (container) {
		// Create list of search terms from ISBN and OCLC numbers.
		var searchTerms = [];
		for (locationNumber in data.location) {
			var numberField = String(data.location[locationNumber]['md-isbn']);
			var matches = numberField.replace(/-/g,'').match(/[0-9]{9,12}[0-9xX]/g);
			if (matches) {
				for (var ISBNMatchNumber = 0; ISBNMatchNumber < matches.length; ISBNMatchNumber++) {
					searchTerms.push('ISBN:' + matches[ISBNMatchNumber]);
				}
			}
			numberField = String(data.location[locationNumber]['md-oclc-number']);
			matches = numberField.match(/[0-9]{4,}/g);
			if (matches) {
				for (var OCLCMatchNumber = 0; OCLCMatchNumber < matches.length; OCLCMatchNumber++) {
					searchTerms.push('OCLC:' + matches[OCLCMatchNumber]);
				}
			}
		}

		if (searchTerms.length > 0) {
			// Query Google Books for the ISBN/OCLC numbers in question.
			var googleBooksURL = 'http://books.google.com/books?bibkeys=' + searchTerms
						+ '&jscmd=viewapi&callback=?';
			jQuery.getJSON(googleBooksURL,
				function(data) {
					/*	bookScore
						Returns a score for given book to help determine which book
						to use on the page if several results exist.
					
						Preference is given existing previews and books that are
						embeddable are preferred if there is a tie.
					
						input: book - Google Books object
						output: integer
					*/
					function bookScore (book) {
						var score = 0;

						if (book.preview === 'full') {
							score += 10;
						}
						else if (book.preview === 'partial') {
							score += 5;
						}
						if (book.embeddable === true) {
							score += 1;
						}

						return score;
					}


					/*
						If there are multiple results choose the first one with
						the maximal score. Ignore books without a preview.
					*/
					var selectedBook;
					jQuery.each(data,
						function(bookNumber, book) {
							var score = bookScore(book);
							book.score = score;

							if (selectedBook === undefined || book.score > selectedBook.score) {
								if (book.preview !== 'noview') {
									selectedBook = book;
								}
							}
						}
					);

					// Add link to Google Books if there is a selected book.
					if (selectedBook !== undefined) {
						var dt = document.createElement('dt');
						var dd = document.createElement('dd');

						var bookLink = document.createElement('a');
						dd.appendChild(bookLink);
						bookLink.setAttribute('href', selectedBook.preview_url);
						if (selectedBook.embeddable === true) {
							bookLink.onclick = openPreview;
						}

						var language = jQuery('html').attr('lang');
						if (language === undefined) {
							language = 'en';
						}
						var buttonImageURL = 'http://www.google.com/intl/' + language + '/googlebooks/images/gbs_preview_button1.gif';

						var buttonImage = document.createElement('img');
						buttonImage.setAttribute('src', buttonImageURL);
						var buttonAltText = 'Google Books';
						if (selectedBook.preview === 'full') {
							buttonAltText = localise('Google Books: Vollständige Ansicht');
						}
						else if (selectedBook.preview === 'partial') {
							buttonAltText = localise('Google Books: Eingeschränkte Vorschau');
						}
						buttonImage.setAttribute('alt', buttonAltText);
						bookLink.appendChild(buttonImage);

						if (selectedBook.thumbnail_url !== undefined) {
							bookLink = bookLink.cloneNode(false);
							dt.appendChild(bookLink);
							var coverArtImage = document.createElement('img');
							bookLink.appendChild(coverArtImage);
							coverArtImage.setAttribute('src', selectedBook.thumbnail_url);
							coverArtImage.setAttribute('alt', localise('Umschlagbild'));
							jQuery(coverArtImage).addClass('bookCover');
						}

						jElements = jQuery([dt, dd]);
						jElements.addClass('pz2-googleBooks');
						jElements.hide();
						container.appendChild(dt);
						container.appendChild(dd);
						if (!jQuery.browser.msie || jQuery.browser.version > 7) {
							jElements.slideDown('fast');
						}
						else {
							jElements.show()
						}
					}
				}
			);
		}



		/*	openPreview
			Called when the Google Books button is clicked.
			Opens Google Preview.
			output: false (so the click isn't handled any further)
		*/
		var openPreview = function() {
			var googlePreviewButton = this;
			// Get hold of containing <div>, creating it if necessary.
			var previewContainerDivName = 'googlePreviewContainer';
			var previewContainerDiv = document.getElementById(previewContainerDivName);
			var previewDivName = 'googlePreview';
			var previewDiv = document.getElementById(previewDivName);


			if (!previewContainerDiv) {
				previewContainerDiv = document.createElement('div');
				previewContainerDiv.setAttribute('id', previewContainerDivName);
				jQuery('#page').get(0).appendChild(previewContainerDiv);

				var titleBarDiv = document.createElement('div');
				jQuery(titleBarDiv).addClass('googlePreview-titleBar');
				previewContainerDiv.appendChild(titleBarDiv);

				var closeBoxLink = document.createElement('a');
				titleBarDiv.appendChild(closeBoxLink);
				jQuery(closeBoxLink).addClass('googlePreview-closeBox');
				closeBoxLink.setAttribute('href', '#');
				
				
				closeBoxLink.onclick = new Function('javascript:jQuery("#' + previewContainerDivName + '").hide(200);return false;');
				closeBoxLink.appendChild(document.createTextNode(localise('Vorschau schließen')));

				previewDiv = document.createElement('div');
				previewDiv.setAttribute('id', previewDivName);
				previewContainerDiv.appendChild(previewDiv);
			}
			else {
				jQuery(previewContainerDiv).show(200);
			}

			var viewer = new google.books.DefaultViewer(previewDiv);
			viewer.load(this.href);

			return false;
		}

	} // end of addGoogleBooksLinkIntoElement


	
	/*	locationDetails
		Returns markup for each location of the item found from the current data.
		output:	DOM object with information about this particular copy/location of the item found
	*/
	var locationDetails = function () {

		/*	detailInfoItemWithLabel
			input:	fieldContent - string with content to display in the field
					labelName - string displayed as the label
					dontTerminate - boolean:	false puts a ; after the text
												true puts nothing after the text
		*/
		var detailInfoItemWithLabel = function (fieldContent, labelName, dontTerminate) {
			var infoSpan;
			if ( fieldContent !== undefined ) {
				infoSpan = document.createElement('span');
				jQuery(infoSpan).addClass('pz2-info');
				if ( labelName !== undefined ) {
					var infoLabel = document.createElement('span');
					infoSpan.appendChild(infoLabel);
					jQuery(infoLabel).addClass('pz2-label');
					infoLabel.appendChild(document.createTextNode(labelName));
					infoSpan.appendChild(document.createTextNode(' '));
				}
				infoSpan.appendChild(document.createTextNode(fieldContent));

				if (!dontTerminate) {
					infoSpan.appendChild(document.createTextNode('; '));
				}
			}			
			return infoSpan;
		}



		/*	detailInfoItem
			input:	fieldName - string
			output:	DOM elements containing the label and information for fieldName data
						* the label is looked up from the localisation table
						* data[detail-label-fieldName] provides the data
		*/
		var detailInfoItem = function (fieldName) {
			var infoItem;
			var value = location['md-'+fieldName];

			if ( value !== undefined ) {
				var label;
				var labelID = 'detail-label-' + fieldName;
				var localisedLabelString = localise(labelID);

				if ( localisedLabelString != labelID ) {
					label = localisedLabelString;
				}

				var content = value.join(', ').replace(/^[ ]*/,'').replace(/[ ;.,]*$/,'');

				infoItem = detailInfoItemWithLabel(content, label);
			}

			return infoItem;
		}



		/*  cleanISBNs
			Takes the array of ISBNs in location['md-isbn'] and
				1. Normalises them
				2. Removes duplicates (particularly the ISBN-10 corresponding to an ISBN-13)
		*/
		var cleanISBNs = function () {
			/*	normaliseISBNsINString
				Vague matching of ISBNs and removing the hyphens in them.
				input: string
				output: string
			*/
			var normaliseISBNsInString = function (ISBN) {
				return ISBN.replace(/([0-9]*)-([0-9Xx])/g, '$1$2');
			}


			/*	pickISBN
				input: 2 ISBN number strings without dashes
				output: if both are 'the same': the longer one (ISBN-13)
				        if they aren't 'the same': undefined
			*/
			var pickISBN = function (ISBN1, ISBN2) {
				var result = undefined;
				var numberRegexp = /([0-9]{9,12})[0-9xX].*/;
				var numberPart1 = ISBN1.replace(numberRegexp, '$1');
				var numberPart2 = ISBN2.replace(numberRegexp, '$1');
				if (!(numberPart1 == numberPart2)) {
					if (numberPart1.indexOf(numberPart2) != -1) {
						result = ISBN1;
					}
					else if (numberPart2.indexOf(numberPart1) != -1) {
						result = ISBN2;
					}
				}
				return result;
			}



			if (location['md-isbn'] !== undefined) {
				var newISBNs = []
				for (var index in location['md-isbn']) {
					var normalisedISBN = normaliseISBNsInString(location['md-isbn'][index]);
					for (var newISBNNumber in newISBNs) {
						var newISBN = newISBNs[newISBNNumber];
						var preferredISBN = pickISBN(normalisedISBN, newISBN);
						if (preferredISBN !== undefined) {
							newISBNs.splice(newISBNNumber, 1, preferredISBN);
							normalisedISBN = undefined;
							break;
						}
					}
					if (normalisedISBN !== undefined) {
						newISBNs.push(normalisedISBN);
					}
				}
				location['md-isbn'] = newISBNs;
			}
		}



		/*	electronicURLs
			Create markup for URLs in current location data.
			output:	DOM element containing URLs as links.
		*/
		var electronicURLs = function() {
			
			/*	cleanURLList
				Returns a cleaned list of URLs for presentation.
				1. Removes duplicates of URLs if they exist, preferring URLs with label
				2. Removes URLs duplicating DOI information
				3. Sorts URLs to have those with a label at the beginning
				output:	array of URL strings or URL objects (with #text and other properties)
			*/
			var cleanURLList = function () {
				var URLs = location['md-electronic-url'];
	
				if (URLs) {
					// Figure out which URLs are duplicates and collect indexes of those to remove.
					var indexesToRemove = {};
					for (var URLIndex = 0; URLIndex < URLs.length; URLIndex++) {
						var URLInfo = URLs[URLIndex];
						var URL = URLInfo;
						if (typeof(URLInfo) === 'object' && URLInfo['#text']) {
							URL = URLInfo['#text'];
						}

						// Check for duplicates in the electronic-urls field.
						for (var remainingURLIndex = URLIndex + 1; remainingURLIndex < URLs.length; remainingURLIndex++) {
							var remainingURLInfo = URLs[remainingURLIndex];
							var remainingURL = remainingURLInfo;
							if (typeof(remainingURLInfo) === 'object' && remainingURLInfo['#text']) {
								remainingURL = remainingURLInfo['#text'];
							}

							if (URL == remainingURL) {
								// Two of the URLs are identical.
								// Keep the one with the title if only one of them has one,
								// keep the first one otherwise.
								var URLIndexToRemove = URLIndex + remainingURLIndex;
								if (typeof(URLInfo) !== 'object' && typeof(remainingURLInfo) === 'object') {
									URLIndexToRemove = URLIndex;
								}
								indexesToRemove[URLIndexToRemove] = true;
							}
						}

						// Check for duplicates among the DOIs.
						for (var DOIIndex in data['md-doi']) {
							if (URL.search(data['md-doi'][DOIIndex]) != -1) {
								indexesToRemove[URLIndex] = true;
								break;
							}
						}

					}

					// Remove the duplicate URLs.
					var indexesToRemoveArray = [];
					for (var i in indexesToRemove) {
						if (indexesToRemove[i]) {
							indexesToRemoveArray.push(i);
						}
					}
					indexesToRemoveArray.sort( function(a, b) {return b - a;} )
					for (var j in indexesToRemoveArray) {
						URLs.splice(indexesToRemoveArray[j], 1);
					}

					// Re-order URLs so those with explicit labels appear at the beginning.
					URLs.sort( function(a, b) {
							if (typeof(a) === 'object' && typeof(b) !== 'object') {
								return -1;
							}
							else if (typeof(a) !== 'object' && typeof(b) === 'object') {
								return 1;
							}
							return 0;
						}
					);
				}
				
				return URLs;
			}



			var electronicURLs = cleanURLList();

			var URLsContainer;
			if (electronicURLs && electronicURLs.length != 0) {
				URLsContainer = document.createElement('span');

				for (var URLNumber in electronicURLs) {
					var URLInfo = electronicURLs[URLNumber];
					var linkText = localise('Link', linkDescriptions); // default link name
					var linkURL = URLInfo;
	
					if (typeof(URLInfo) === 'object' && URLInfo['#text'] !== undefined) {
						// URLInfo is not just an URL but an array also containing the link name
						if (URLInfo['@name'] !== undefined) {
							linkText = localise(URLInfo['@name'], linkDescriptions);
							if (URLInfo['@note'] !== undefined) {
								linkText += ', ' + localise(URLInfo['@note'], linkDescriptions);
							}
						}
						else if (URLInfo['@note'] !== undefined) {
							linkText = localise(URLInfo['@note'], linkDescriptions);
						}
						else if (URLInfo['@fulltextfile'] !== undefined) {
							linkText = localise('Document', linkDescriptions);
						}
						linkURL = URLInfo['#text'];
					}
					
					linkText = '[' + linkText +  ']';

					if (URLsContainer.childElementCount > 0) {
						// add , as separator if not the first element
						URLsContainer.appendChild(document.createTextNode(', '));
					}
					var link = document.createElement('a');
					URLsContainer.appendChild(link);
					link.setAttribute('href', linkURL);
					turnIntoNewWindowLink(link);
					link.appendChild(document.createTextNode(linkText));
				}
				URLsContainer.appendChild(document.createTextNode('; '));
			}

			return URLsContainer;		
		}



		/*	catalogueLink
			Returns a link for the current record that points to the catalogue page for that item.
			output:	DOM anchor element pointing to the catalogue page.
		*/
		var catalogueLink = function () {
			var catalogueURL = location['md-catalogue-url'];
			
			if (catalogueURL && catalogueURL.length > 0) {
				catalogueURL = catalogueURL[0];
				
				/*	If the user does not have a Uni Göttingen IP address
					and we don’t generally prefer using the Opac, redirect Opac links
					to GVK which is a superset and offers better services for non-locals.
				*/
				if (!preferSUBOpac && clientIPAddress.search('134.76.') !== 0) {
					var opacBaseURL = 'http://opac.sub.uni-goettingen.de/DB=1';
					var GVKBaseURL = 'http://gso.gbv.de/DB=2.1';
					catalogueURL = catalogueURL.replace(opacBaseURL, GVKBaseURL);
				}
			}

			var targetName = localise(location['@name'], catalogueNames);
			if (catalogueURL && targetName) {
				var linkElement = document.createElement('a');
				linkElement.setAttribute('href', catalogueURL);
				turnIntoNewWindowLink(linkElement);
				jQuery(linkElement).addClass('pz2-detail-catalogueLink')
				var linkTitle = localise('Im Katalog ansehen');
				linkElement.title = linkTitle;
				linkElement.appendChild(document.createTextNode(targetName));
			}

			return linkElement;
		}



		var locationDetails = [];

		for ( var locationNumber in data.location ) {
			var location = data.location[locationNumber];
			var localURL = location['@id'];
			var localName = location['@name'];

			var detailsHeading = document.createElement('dt');
			locationDetails.push(detailsHeading);
			detailsHeading.appendChild(document.createTextNode(localise('Ausgabe')+':'));

			var detailsData = document.createElement('dd');
			locationDetails.push(detailsData);

			if (location['md-medium'] != 'article') {
				appendInfoToContainer( detailInfoItem('edition'), detailsData );
				appendInfoToContainer( detailInfoItem('publication-name'), detailsData );
				appendInfoToContainer( detailInfoItem('publication-place'), detailsData );
				appendInfoToContainer( detailInfoItem('date'), detailsData );
				appendInfoToContainer( detailInfoItem('physical-extent'), detailsData );
				cleanISBNs();
				appendInfoToContainer( detailInfoItem('isbn'), detailsData );
			}
			appendInfoToContainer( electronicURLs(), detailsData);
			appendInfoToContainer( catalogueLink(), detailsData);

			if (detailsData.childNodes.length == 0) {locationDetails = [];}
		}

		return locationDetails;
	}



	/*	exportLinks
		Returns list of additional links provided for the current location.
		output:	DOMElement - markup for additional links
	*/
	var exportLinks = function () {

		/*	copyObjectContentTo
			Copies the content of a JavaScript object to an XMLElement.
				(non-recursive!)
			Used to create XML markup for JavaScript data we have.

			input:	object - the object whose content is to be copied
					target - XMLElement the object content is copied into
		*/
		var copyObjectContentTo = function (object, target) {
			for (var fieldName in object) {
				if (fieldName[0] === '@') {
					// We are dealing with an attribute.
					target.setAttribute(fieldName.substr(1), object[fieldName]);
				}
				else {
					// We are dealing with a sub-element.
					var fieldArray = object[fieldName];
					for (var index in fieldArray) {
						var child = fieldArray[index];
						var targetChild = target.ownerDocument.createElement(fieldName);
						target.appendChild(targetChild);
						if (typeof(child) === 'object') {
							for (childPart in child) {
								if (childPart === '#text') {
									targetChild.appendChild(target.ownerDocument.createTextNode(child[childPart]));
								}
								else if (childPart[0] === '@') {
									targetChild.setAttribute(childPart.substr(1), child[childPart]);
								}
							}
						}
						else {
							targetChild.appendChild(target.ownerDocument.createTextNode(child))
						}
					}
				}
			}
		}



		/*	newXMLDocument
			Helper function for creating an XML Document in proper browsers as well as IE.

			Taken from Flanagan: JavaScript, The Definitive Guide
			http://www.webreference.com/programming/javascript/definitive2/

			Create a new Document object. If no arguments are specified,
			the document will be empty. If a root tag is specified, the document
			will contain that single root tag. If the root tag has a namespace
			prefix, the second argument must specify the URL that identifies the
			namespace.

			inputs:	rootTagName - string
					namespaceURL - string [optional]
			output:	XMLDocument
		*/
		newXMLDocument = function(rootTagName, namespaceURL) {
			if (!rootTagName) rootTagName = "";
			if (!namespaceURL) namespaceURL = "";
			if (document.implementation && document.implementation.createDocument) {
				// This is the W3C standard way to do it
				return document.implementation.createDocument(namespaceURL, rootTagName, null);
			}
			else {
				// This is the IE way to do it
				// Create an empty document as an ActiveX object
				// If there is no root element, this is all we have to do
				var doc = new ActiveXObject("MSXML2.DOMDocument");
				// If there is a root tag, initialize the document
				if (rootTagName) {
					// Look for a namespace prefix
					var prefix = "";
					var tagname = rootTagName;
					var p = rootTagName.indexOf(':');
					if (p != -1) {
						prefix = rootTagName.substring(0, p);
						tagname = rootTagName.substring(p+1);
					}
					// If we have a namespace, we must have a namespace prefix
					// If we don't have a namespace, we discard any prefix
					if (namespaceURL) {
						if (!prefix) prefix = "a0"; // What Firefox uses
					}
					else prefix = "";
					// Create the root element (with optional namespace) as a
					// string of text
					var text = "<" + (prefix?(prefix+":"):"") +	tagname +
							(namespaceURL
							 ?(" xmlns:" + prefix + '="' + namespaceURL +'"')
							 :"") +
							"/>";
					// And parse that text into the empty document
					doc.loadXML(text);
				}
				return doc;
			}
		};



		/*	dataConversionForm
			Returns the form needed to submit data for converting the pazpar2
			record for exporting in an end-user bibliographic format.
			inputs:	locations - pazpar2 location array
					exportFormat - string
					labelFormat - string
			output:	DOMElement - form
		*/
		var dataConversionForm = function (locations, exportFormat, labelFormat) {

			/*	serialiseXML
				Serialises the passed XMLNode to a string.
				input:	XMLNode
				ouput:	string - serialisation of the XMLNode or null
			*/
			var serialiseXML = function (XMLNode) {
				var result = null;
				try {
					// Gecko- and Webkit-based browsers (Firefox, Chrome), Opera.
					result = (new XMLSerializer()).serializeToString(XMLNode);
				}
				catch (e) {
					try {
						// Internet Explorer.
						result = XMLNode.xml;
					}
					catch (e) {
						//Other browsers without XML Serializer
						//alert('XMLSerializer not supported');
					}
				}
				return result;
			}


			// Convert location data to XML and serialise it to a string.
			var recordXML = newXMLDocument('locations');
			var locationsElement = recordXML.childNodes[0];
			for (var locationIndex in locations) {
				var location = locations[locationIndex];
				var locationElement = recordXML.createElement('location');
				locationsElement.appendChild(locationElement);
				copyObjectContentTo(location, locationElement);
			}
			var XMLString = serialiseXML(locationsElement);

			var form;
			if (XMLString) {
				form = document.createElement('form');
				form.method = 'POST';
				var scriptPath = 'typo3conf/ext/pazpar2/Resources/Public/convert-pazpar2-record.php';
				var scriptGetParameters = {'format': exportFormat}
				if (pageLanguage !== undefined) {
					scriptGetParameters.language = pageLanguage;
				}
				if (siteName !== undefined) {
					scriptGetParameters.filename = siteName;
				}
				form.action = scriptPath + '?' + jQuery.param(scriptGetParameters);

				var qInput = document.createElement('input');
				qInput.name = 'q';
				qInput.setAttribute('type', 'hidden');
				qInput.setAttribute('value', XMLString);
				form.appendChild(qInput);

				var submitButton = document.createElement('input');
				submitButton.setAttribute('type', 'submit');
				form.appendChild(submitButton);
				var buttonText = localise('download-label-' + exportFormat);
				submitButton.setAttribute('value', buttonText);
				if (labelFormat) {
					var labelText = labelFormat.replace(/\*/, buttonText);
					submitButton.setAttribute('title', labelText);
				}
			}

			return form;
		}



		/*	exportItem
			Returns a list item containing the form for export data conversion.
			The parameters are passed to dataConversionForm.

			inputs:	locations - pazpar2 location array
					exportFormat - string
					labelFormat - string
			output:	DOMElement - li containing a form
		*/
		var exportItem = function (locations, exportFormat, labelFormat) {
			var form = dataConversionForm(locations, exportFormat, labelFormat);
			var item;
			if (form) {
				item = document.createElement('li');
				item.appendChild(form);
			}

			return item;
		}

		
		
		/*	appendExportItemsTo
			Appends list items with an export form for each exportFormat to the container.
			inputs:	locations - pazpar2 location array
					labelFormat - string
					container - DOMULElement the list items are appended to
		*/
		var appendExportItemsTo = function (locations, labelFormat, container) {
			for (var formatIndex in exportFormats) {
				container.appendChild(exportItem(locations, exportFormats[formatIndex], labelFormat));
			}
		}



		/*	exportItemSubmenu
			Returns a list item containing a list of export forms for each location in exportFormat.
			inputs:	locations - pazpar2 location array
					exportFormat - string
			output:	DOMLIElement
		*/
		var exportItemSubmenu = function (locations, exportFormat) {
			var submenuContainer = document.createElement('li');
			jQuery(submenuContainer).addClass('pz2-extraLinks-hasSubmenu');
			var formatName = localise('download-label-' + exportFormat);
			var submenuName = localise('download-label-submenu-format').replace(/\*/, formatName);
			submenuContainer.appendChild(document.createTextNode(submenuName));
			var submenuList = document.createElement('ul');
			submenuContainer.appendChild(submenuList);
			for (var locationIndex in locations) {
				var itemLabel = localise('download-label-submenu-index-format').replace(/\*/, parseInt(locationIndex) + 1);
				submenuList.appendChild(exportItem([locations[locationIndex]], exportFormat, itemLabel));
			}
			
			return submenuContainer;
		}



		/*	KVKItem
			Returns a list item containing a link to a KVK catalogue search for data.
			Uses ISBN or title/author data for the search.
			input:	data - pazpar2 record
			output:	DOMLIElement
		*/
		var KVKItem = function (data) {
			var KVKItem = undefined;

			// Check whether there are ISBNs and use the first one we find.
			// (KVK does not seem to support searches for multiple ISBNs.)
			var ISBN = undefined;
			for (var locationIndex in data.location) {
				var location = data.location[locationIndex];
				if (location['md-isbn']) {
					ISBN = location['md-isbn'][0];
					// Trim parenthetical information from ISBN which may be in
					// Marc Field 020 $a
					ISBN = ISBN.replace(/(\s*\(.*\))/, '');
					break;
				}
			}

			var query = '';
			if (ISBN) {
				// Search for ISBN if we found one.
				query += '&SB=' + ISBN;
			}
			else {
				// If there is no ISBN only proceed when we are dealing with a book
				// and create a search for the title and author.
				var wantKVKLink = false;
				for (var mediumIndex in data['md-medium']) {
					var medium = data['md-medium'][mediumIndex];
					if (medium === 'book') {
						wantKVKLink = true;
						break;
					}
				}

				if (wantKVKLink) {
					if (data['md-title']) {
						query += '&TI=' + encodeURIComponent(data['md-title'][0]);
					}
					if (data['md-author']) {
						var authors = [];
						for (var authorIndex in data['md-author']) {
							var author = data['md-author'][authorIndex];
							authors.push(author.split(',')[0]);
						}
						query += '&AU=' + encodeURIComponent(authors.join(' '));
					}
				}
			}

			if (query !== '') {
				var KVKLink = document.createElement('a');
				var KVKLinkURL = 'http://kvk.ubka.uni-karlsruhe.de/hylib-bin/kvk/nph-kvk2.cgi?input-charset=utf-8&Timeout=120';
				KVKLinkURL += localise('&lang=de');
				KVKLinkURL += '&kataloge=SWB&kataloge=BVB&kataloge=NRW&kataloge=HEBIS&kataloge=KOBV_SOLR&kataloge=GBV';
				KVKLink.href = KVKLinkURL + query;
				var label = localise('KVK');
				KVKLink.appendChild(document.createTextNode(label));
				var title = localise('deutschlandweit im KVK suchen');
				KVKLink.setAttribute('title', title);
				turnIntoNewWindowLink(KVKLink);

				KVKItem = document.createElement('li');
				KVKItem.appendChild(KVKLink);
			}

			return KVKItem;
		}



		var exportLinks = document.createElement('div');
		jQuery(exportLinks).addClass('pz2-extraLinks');
		var exportLinksLabel = document.createElement('span');
		exportLinks.appendChild(exportLinksLabel);
		jQuery(exportLinksLabel).addClass('pz2-extraLinksLabel');
		exportLinksLabel.appendChild(document.createTextNode(localise('mehr Links')));
		var extraLinkList = document.createElement('ul');
		exportLinks.appendChild(extraLinkList);

		if (showKVKLink == 1) {
			appendInfoToContainer(KVKItem(data), extraLinkList);
		}
		
		if (data.location.length == 1) {
			var labelFormat = localise('download-label-format-simple');
			appendExportItemsTo(data.location, labelFormat, extraLinkList);
		}
		else {
			var labelFormatAll = localise('download-label-format-all');
			appendExportItemsTo(data.location, labelFormatAll, extraLinkList);
			
			if (showExportLinksForEachLocation) {
				for (var formatIndex in exportFormats) {
					extraLinkList.appendChild(exportItemSubmenu(data.location, exportFormats[formatIndex]));
				}
			}
		}

		return exportLinks;
	}



	
	var data = hitList[recordID];

	if (data) {
		var detailsDiv = document.createElement('div');
		jQuery(detailsDiv).addClass('pz2-details');
		detailsDiv.setAttribute('id', 'det_' + HTMLIDForRecordData(data));

		var detailsList = document.createElement('dl');
		detailsDiv.appendChild(detailsList);
		var clearSpan = document.createElement('span');
		detailsDiv.appendChild(clearSpan);
		jQuery(clearSpan).addClass('pz2-clear');

		/*	A somewhat sloppy heuristic to create cleaned up author and other-person
			lists to avoid duplicating names listed in title-responsiblity already:
			* Do _not_ separately display authors and other-persons whose apparent
				surname appears in the title-reponsibility field to avoid duplication.
			* Completely omit the author list if no title-responsibility field is present
				as author fields are used in its place then.
		*/
		var allResponsibility = '';
		if (data['md-title-responsibility']) {
			allResponsibility = data['md-title-responsibility'].join('; ');
			data['md-author-clean'] = [];
			for (var authorIndex in data['md-author']) {
				var authorName = jQuery.trim(data['md-author'][authorIndex].split(',')[0]);
				if (allResponsibility.match(authorName) == null) {
					data['md-author-clean'].push(data['md-author'][authorIndex]);
				}
			}
		}
		data['md-other-person-clean'] = [];
		for (var personIndex in data['md-other-person']) {
			var personName = jQuery.trim(data['md-other-person'][personIndex].split(',')[0]);
			if (allResponsibility.match(personName) == null) {
				data['md-other-person-clean'].push(data['md-other-person'][personIndex]);
			}
		}
		
		appendInfoToContainer( detailLineAuto('author-clean'), detailsList );
		appendInfoToContainer( detailLineAuto('other-person-clean'), detailsList )
		appendInfoToContainer( detailLineAuto('abstract'), detailsList )
		appendInfoToContainer( detailLineAuto('description'), detailsList );
		appendInfoToContainer( detailLineAuto('series-title'), detailsList );
		appendInfoToContainer( ISSNsDetailLine(), detailsList );
		appendInfoToContainer( detailLineAuto('doi'), detailsList );
		appendInfoToContainer( detailLineAuto('creator'), detailsList );
		appendInfoToContainer( locationDetails(), detailsList );
		appendGoogleBooksElementTo(detailsList);
		if (useZDB === true) {
			addZDBInfoIntoElement( detailsList );
		}
		if (exportFormats.length > 0) {
			appendInfoToContainer( exportLinks(), detailsDiv );
		}
	}

	return detailsDiv;

} // end of renderDetails




/* 	HTMLIDForRecordData
	input:	pz2 record data object
	output:	ID of that object in HTML-compatible form
			(replacing spaces by dashes)
*/
function HTMLIDForRecordData (recordData) {
	var result = undefined;

	if (recordData.recid[0] !== undefined) {
		result = recordData.recid[0].replace(/ /g, '-pd-').replace(/\//g, '-pe-').replace(/\./g,'-pf-');
	}

	return result;
}



/*	recordIDForHTMLID
	input:	record ID in HTML compatible form
	output:	input with dashes replaced by spaces
*/
function recordIDForHTMLID (HTMLID) {
	return HTMLID.replace(/-pd-/g, ' ').replace(/-pe-/g, '/').replace(/-pf-/g, '.');
}



/* Localised Media Types
*/
var mediaNames = {
	'de': {
		'article': 'Aufsatz',
		'audio-visual': 'Video',
		'book': 'Buch',
		'electronic': 'Datei',
		'journal': 'Zeitschrift',
		'map': 'Karte',
		'microform': 'Mikroform',
		'multivolume': 'Mehrbändig',
		'music-score': 'Noten',
		'other': 'Andere',
		'recording': 'Aufnahme',
		'website': 'Website',
		'multiple': 'Verschiedene Medien'
	},
	
	'en': {
		'article': 'Article',
		'audio-visual': 'Video',
		'book': 'Book',
		'electronic': 'Computer File',
		'journal': 'Journal',
		'map': 'Map',
		'microform': 'Microform',
		'music-score': 'Music Score',
		'multivolume': 'Multiple volumes',
		'other': 'Other',
		'recording': 'Recording',
		'website': 'Website',
		'multiple': 'Mixed Media Types'
	}
};


/*	Localised Catalogue names
*/
var catalogueNames = {
	'de': {

	},
	'en': {
		'Geschichte Aufsätze': 'Essays: History',
		'Anglistik Aufsätze': 'Essays: Literature'
	}
}



/*	Localised Link Descriptions
	For link terminology found in:
	* GVK Catalogue records
	* Repository stylesheets
*/
var linkDescriptions = {
	'de': {
		'Book review (H-Net)': 'Rezension',
		'Buchrezension (H-Soz-u-Kult)': 'Rezension',
		'Contributor biographical information': 'Biographische Informationen',
		'Deutschlandweit zugänglich': 'deutschlandweit zugänglich',
		'Document': 'Volltext',
		'Gesamtes Dokument': 'Volltext',
		'Inhaltsverzeichnis': 'Inhaltsverzeichnis',
		'kostenfrei': 'kostenfrei',
		'Leseprobe': 'Leseprobe',
		'Link': 'Link',
		'Publisher Description': 'Verlagsbeschreibung',
		'Repository': 'Repository',
		'Rezension': 'Rezension',
		'Table of Contents': 'Inhaltsverzeichnis',
		'Table of contents': 'Inhaltsverzeichnis',
		'Table of contents only': 'Inhaltsverzeichnis',
		'TOC': 'Inhaltsverzeichnis',
		'Volltext': 'Volltext'
	},
	'en': {
		'Book review (H-Net)': 'Review',
		'Buchrezension (H-Soz-u-Kult)': 'Review',
		'Contributor biographical information': 'Biographical Information',
		'Deutschlandweit zugänglich': 'accessible in Germany',
		'Document': 'Full Text',
		'Gesamtes Dokument': 'Full Text',
		'Inhaltsverzeichnis': 'Table of Contents',
		'kostenfrei': 'free',
		'Leseprobe': 'Excerpt',
		'Link': 'Link',
		'Publisher Description': 'Publisher Description',
		'Repository': 'Repository',
		'Rezension': 'Review',
		'Table of Contents': 'Table of Contents',
		'Table of contents': 'Table of Contents',
		'Table of contents only': 'Table of Contents',
		'TOC': 'Table of Contents',
		'Volltext': 'Full text'
	}
}


/* Localised Language Codes
*/
var languageNames = {
	'de': {
		'ace': 'Aceh',
		'ach': 'Acholi',
		'ada': 'Adangme',
		'ady': 'Adyghe',
		'egy': 'Ägyptisch',
		'aar': 'Afar',
		'pus': 'Afghanisch (=Paschtu)',
		'afh': 'Afrihili',
		'afr': 'Afrikaans',
		'aka': 'Akan (=Volta-Comeo)',
		'akk': 'Akkadisch',
		'alb': 'Albanisch',
		'ale': 'Aleut, Atka',
		'alg': 'Algonkin-Sprachen',
		'tut': 'Altaische Sprachen (andere)',
		'chu': 'Altbulgarisch, Altslawisch, Kirchenslawisch',
		'ang': 'Altenglisch (ca. 450-1100)',
		'fro': 'Altfranzösisch (842-ca. 1400)',
		'grc': 'Altgriechisch (bis 1453)',
		'qhe': 'Althebräisch, Hebräisch/Althebräisch',
		'goh': 'Althochdeutsch (ca. 750-1050)',
		'sga': 'Altirisch (bis 1100)',
		'kaw': 'Altjavanisch, Kawi',
		'non': 'Altnordisch',
		'peo': 'Altpersisch (ca. 600 -400 v.Chr.)',
		'pro': 'Altprovenzalisch, Altokzitanisch (bis 1500)',
		'amh': 'Amharisch',
		'apa': 'Apachen-Sprache',
		'ara': 'Arabisch',
		'arg': 'Aragonese',
		'arc': 'Aramäisch',
		'arp': 'Arapaho',
		'arn': 'Arauka-Sprachen',
		'arw': 'Arawak-Sprachen',
		'arm': 'Armenisch',
		'aze': 'Aserbeidschanisch',
		'asm': 'Assemesisch',
		'ast': 'Asturian, Bable',
		'ath': 'Athapaskische Sprachen',
		'aus': 'Australische Sprachen',
		'map': 'Austronesische Sprachen (andere)',
		'ave': 'Avestisch',
		'awa': 'Awadhi',
		'ava': 'Awarisch',
		'aym': 'Aymará',
		'ban': 'Balinesisch',
		'qbk': 'Balkarisch',
		'bat': 'Baltische Sprachen (andere)',
		'bam': 'Bambara',
		'bai': 'Bamileke',
		'bad': 'Banda',
		'lam': 'Banjari, Lamba',
		'bnt': 'Bantusprachen (andere)',
		'bas': 'Basaa',
		'bak': 'Baschkir',
		'baq': 'Baskisch',
		'btk': 'Batak (Indonesien)',
		'bej': 'Bedauye',
		'bal': 'Belutschisch',
		'bem': 'Bemba',
		'ben': 'Bengali',
		'ber': 'Berbersprachen',
		'bho': 'Bhodschpuri',
		'bik': 'Bicol',
		'bih': 'Bihari',
		'bin': 'Bini (=Pini)',
		'bur': 'Birmanisch',
		'bis': 'Bislama, Beach-la-Mar',
		'bla': 'Blackfoot',
		'byn': 'Blin, Bilin',
		'nob': 'Bokmål, Norwegen',
		'bos': 'Bosnisch',
		'bra': 'Braj-Bhakha',
		'bre': 'Bretonisch',
		'bug': 'Bugi',
		'bul': 'Bulgarisch',
		'bua': 'Burjatisch',
		'cad': 'Caddo-Sprachen',
		'ceb': 'Cebuano',
		'qqa': 'Chakassisch',
		'cmc': 'Cham-Sprachen',
		'cha': 'Chamorro',
		'qoj': 'chantisch, Ostjakisch',
		'chr': 'Cherokee',
		'nya': 'Chewa, Nyanja',
		'chy': 'Cheyenne',
		'chb': 'Chibcha-Sprachen',
		'chi': 'Chinesisch',
		'chn': 'Chinook',
		'chp': 'Chipewyan',
		'cho': 'Choctaw',
		'cre': 'Cree',
		'dan': 'Dänisch',
		'day': 'Dajakisch',
		'dak': 'Dakota',
		'dar': 'Dari',
		'del': 'Delaware',
		'qdn': 'Dendi',
		'ger': 'Deutsch',
		'din': 'Dinka',
		'doi': 'Dogri',
		'dgr': 'Dogrib',
		'qdo': 'Dolganisch',
		'dra': 'Drawidische Sprachen (andere)',
		'dua': 'Duala-Sprachen',
		'dyu': 'Dyula',
		'dzo': 'Dzongkha',
		'efi': 'Efik',
		'eka': 'Ekajuk',
		'elx': 'Elamisch',
		'tvl': 'Elliceanisch',
		'eng': 'Englisch',
		'myv': 'Erzya',
		'esk': 'Eskimoisch (alter Sprachcode)', // deprecated
		'kal': 'Eskimoisch (Grönländisch)',
		'esp': 'Esperanto (alter Sprachcode)', // deprecated
		'epo': 'Esperanto',
		'est': 'Estnisch',
		'eth': 'Ethiopisch (alter Sprachcode)', // deprecated
		'gez': 'Ethiopisch',
		'ewe': 'Ewe',
		'qlm': 'Ewenisch, Lamutisch',
		'qev': 'Ewenkisch',
		'ewo': 'Ewondo',
		'far': 'Färöisch (alter Sprachcode)', // deprecated
		'fao': 'Färöisch',
		'fat': 'Fanti',
		'fij': 'Fidschi',
		'fin': 'Finnisch',
		'fiu': 'Finnougrische Sprachen (andere)',
		'fon': 'Fon',
		'fre': 'Französisch',
		'fur': 'Friaulisch',
		'fri': 'Friesisch', // deprecated
		'frr': 'Nordfriesisch',
		'frs': 'Ostfriesisch',
		'ful': 'Ful',
		'gaa': 'Ga',
		'qgd': 'Gade',
		'gae': 'Gälisch (= Schottisch, alter Sprachcode)', // deprecated
		'gla': 'Gälisch (=Schottisch)',
		'gag': 'Galizisch (alter Sprachcode)', // deprecated
		'glg': 'Galizisch',
		'lug': 'Ganda',
		'gay': 'Gayo',
		'gba': 'Gbaya',
		'geo': 'Georgisch (=Grusinisch)',
		'gem': 'Germanische Sprachen (andere)',
		'gil': 'Gilbertesisch',
		'qnv': 'Giljakisch',
		'gon': 'Gondi',
		'gor': 'Gorontalesisch',
		'got': 'Gotisch',
		'grb': 'Grebo',
		'gre': 'Griechisch',
		'grn': 'Guaraní',
		'guj': 'Gujarati',
		'hai': 'Haida',
		'hat': 'Haitisch',
		'afa': 'Hamitosemitische Sprachen',
		'hau': 'Haussa',
		'haw': 'Hawaiisch',
		'heb': 'Hebräisch',
		'her': 'Herero',
		'hit': 'Hethitisch',
		'hil': 'Hiligaynon',
		'him': 'Himachali',
		'hin': 'Hindi',
		'hmo': 'Hiri-Motu',
		'hsb': 'Hochsorbisch',
		'hup': 'Hupa',
		'iba': 'Iban',
		'ibo': 'Ibo',
		'ido': 'Ido',
		'ijo': 'Ijo',
		'ilo': 'Ilokano',
		'smn': 'Inari Sami',
		'nai': 'Indianersprachen (Nordamerika) (andere)',
		'sai': 'Indianersprachen (Südamerika) (andere)',
		'cai': 'Indianersprachen (Zentralamerika) (andere)',
		'inc': 'Indoarische Sprachen',
		'ine': 'Indogermanische Sprachen (andere)',
		'ind': 'Indonesisch',
		'inh': 'Inguschisch',
		'ina': 'Interlingua (IALA)',
		'ile': 'Interlingue',
		'iku': 'Inuktitut',
		'ipk': 'Inupiaq',
		'ira': 'Iranische Sprachen (andere)',
		'iri': 'Irisch (alter Sprachcode)', // deprecated
		'gle': 'Irisch',
		'iro': 'Irokesische Sprachen',
		'ice': 'Isländisch',
		'ita': 'Italienisch',
		'qkc': 'Itelmenisch, Kamtschadalisch',
		'sah': 'Jakutisch',
		'jpn': 'Japanisch',
		'jav': 'Javanisch',
		'yid': 'Jiddisch',
		'lad': 'Judenspanisch',
		'jrb': 'Jüdisch-Arabisch',
		'jpr': 'Jüdisch-Persisch',
		'qju': 'Jukagirisch',
		'kbd': 'Kabardisch',
		'kab': 'Kabylisch',
		'kac': 'Kachin',
		'xal': 'Kalmükisch',
		'kam': 'Kamba',
		'kan': 'Kannada',
		'kau': 'Kanuri',
		'krc': 'Karachay-Balkar',
		'kaa': 'Karakalpakisch',
		'qkr': 'Karelisch',
		'kar': 'Karenisch',
		'car': 'Karibische Sprachen',
		'kaz': 'Kasachisch',
		'kas': 'Kaschmiri',
		'csb': 'Kaschubisch',
		'cat': 'Katalanisch',
		'cau': 'Kaukasische Sprachen (andere)',
		'cel': 'Keltische Sprachen (andere)',
		'que': 'Ketchua (=Quechua)',
		'kha': 'Khasi',
		'cam': 'Khmer (Kambodschanisch, alterSprachcode)', // deprecated
		'khm': 'Khmer (Kambodschanisch)',
		'khi': 'Khoisan-Sprachen (andere)',
		'mag': 'Khotta',
		'kik': 'Kikuyu',
		'kir': 'Kirgisisch',
		'qrn': 'Kirundi',
		'nwc': 'Klass. Newari, Altnewari, Klass. Nepalesisch, Bhasa',
		'kom': 'Komi-Sprachen',
		'kon': 'Kongo',
		'kok': 'Konkani',
		'cop': 'Koptisch',
		'kor': 'Koreanisch',
		'qkj': 'Korjakisch',
		'cor': 'Kornisch',
		'cos': 'Korsisch',
		'kos': 'Kosraeanisch',
		'kpe': 'Kpelle',
		'cpe': 'Kreolisch-Englisch (andere), Krio',
		'cpf': 'Kreolisch-Französisch (andere)',
		'cpp': 'Kreolisch-Portugiesisch (andere)',
		'crp': 'Kreolische Sprachen (andere)',
		'crh': 'Krimtatarisch, Krimtürkisch',
		'kro': 'Kru-Sprachen',
		'kum': 'Kumükisch',
		'art': 'Kunst-, Hilfssprache (andere)',
		'kur': 'Kurdisch',
		'cus': 'Kuschitische Sprachen (andere)',
		'gwi': 'Kutchin',
		'kut': 'Kutenai',
		'kua': 'Kwanyama',
		'lah': 'Lahnda',
		'lao': 'Laotisch',
		'lat': 'Latein',
		'lez': 'Lesgisch',
		'lav': 'Lettisch',
		'lim': 'Limburgan',
		'lin': 'Lingala',
		'lit': 'Litauisch',
		'jbo': 'Lojban',
		'lub': 'Luba-Katanga',
		'lua': 'Luba-Lulua',
		'lui': 'Luiseno',
		'smj': 'Lule Sami',
		'lun': 'Lunda',
		'luo': 'Luo (Kenia, Tansania)',
		'lus': 'Lushai',
		'ltz': 'Luxemburgisch',
		'qmg': 'Madagassisch',
		'mad': 'Maduresisch',
		'mai': 'Maithili',
		'mak': 'Makassarisch',
		'mac': 'Makedonisch',
		'mla': 'Madagassisch (alter Sprachcode)', // deprecated
		'mlg': 'Madagassisch',
		'may': 'Malaiisch',
		'mal': 'Malayalam',
		'div': 'Maledivisch',
		'mlt': 'Maltesisch',
		'mnc': 'Manchu, Mandschurisch',
		'mdr': 'Mandaresisch',
		'man': 'Mande-Sprachen, Mandingo, Malinke',
		'mno': 'Manobo',
		'max': 'Manx (alter Sprachcode)', // deprecated
		'glv': 'Manx',
		'mao': 'Maori',
		'mar': 'Marathi',
		'mah': 'Marschallesisch',
		'mwr': 'Marwari',
		'mas': 'Massai (=Masai)',
		'myn': 'Maya-Sprachen',
		'kmb': 'Mbundu (Kimbundu)',
		'umb': 'Mbundu (Umbundu)',
		'mul': 'mehrsprachig, Polyglott',
		'mni': 'Meithei',
		'men': 'Mende',
		'hmn': 'Miao-Sprachen',
		'mic': 'Micmac',
		'min': 'Minangkabau',
		'enm': 'Mittelenglisch (1100-1500)',
		'frm': 'Mittelfranzösisch (ca. 1400-1600)',
		'gmh': 'Mittelhochdeutsch (ca. 1050-1500)',
		'mga': 'Mittelirisch (ca. 1100-1550)',
		'dum': 'Mittelniederländisch (ca. 1050-1350)',
		'pal': 'Mittelpersisch',
		'moh': 'Mohawk',
		'mdf': 'Moksha',
		'mol': 'Moldawisch',
		'mkh': 'Mon-Khmer-Sprachen (andere)',
		'lol': 'Mongo',
		'mon': 'Mongolisch',
		'qmw': 'Mordwinisch',
		'mos': 'Mossi',
		'mun': 'Mundasprachen',
		'mus': 'Muskogee-Sprachen',
		'nqo': 'N’Ko',
		'nah': 'Nahuatl (=Aztekisch)',
		'qnn': 'Nanaisch',
		'nau': 'Nauruanisch',
		'nav': 'Navajo',
		'nde': 'Ndebele (Nord)',
		'nbl': 'Ndebele (Süd)',
		'ndo': 'Ndonga',
		'nap': 'Neapolitanisch',
		'nep': 'Nepali',
		'tpi': 'Neumelanesisch, Pidgin',
		'new': 'Newari, Nepal Bhasa',
		'nia': 'Nias',
		'nds': 'Niederdeutsch',
		'dut': 'Niederländisch',
		'dsb': 'Niedersorbisch',
		'nic': 'Nigerkordofanische Sprachen',
		'ssa': 'Nilosaharanische Sprachen',
		'niu': 'Niue',
		'nyn': 'Nkole',
		'nog': 'Nogai',
		'nor': 'Norwegisch',
		'nub': 'Nubische Sprachen',
		'nym': 'Nyamwezi',
		'nno': 'Nynorsk, Norwegen',
		'nyo': 'Nyoro',
		'nzi': 'Nzima',
		'oji': 'Ojibwa',
		'lan': 'Okzitanisch (nach 1500), Provencal (alter Sprachcode)', // deprecated
		'oci': 'Okzitanisch (nach 1500), Provencal',
		'kru': 'Oraon (=Kurukh)',
		'ori': 'Oriya',
		'gal': 'Oromo, Galla (alter Sprachcode)', // deprecated
		'orm': 'Oromo, Galla',
		'osa': 'Osage',
		'oss': 'Ossetisch',
		'rap': 'Osterinsel',
		'oto': 'Otomangue-Sprachen',
		'ota': 'Ottomanisch, (=Osmanisch =Türkisch) (1500-1928)',
		'pau': 'Palau',
		'pli': 'Pali',
		'pam': 'Pampanggan',
		'pan': 'Pandschabi',
		'pag': 'Pangasinan',
		'fan': 'Pangwe',
		'pap': 'Papiamento',
		'paa': 'Papuasprachen (andere)',
		'per': 'Persisch (=Farsi)',
		'phi': 'Philippinen-Austronesisch (andere)',
		'phn': 'Phönikisch',
		'pol': 'Polnisch',
		'pon': 'Ponapeanisch',
		'por': 'Portugiesisch',
		'pra': 'Prakrit',
		'roh': 'Rätoromanisch',
		'raj': 'Rajasthani',
		'rar': 'Rarotonganisch',
		'roa': 'Romanische Sprachen (andere)',
		'loz': 'Rotse',
		'rum': 'Rumänisch',
		'run': 'Rundi',
		'rys': 'Rusinisch',
		'rus': 'Russisch',
		'kin': 'Rwanda',
		'lap': 'Sami, Lappisch (alter Sprachcode)', // deprecated
		'smi': 'Sami, Lappisch',
		'kho': 'Sakisch',
		'sal': 'Salish',
		'sam': 'Samaritanisch',
		'sme': 'Sami (Nord)',
		'sma': 'Sami (Süd)',
		'sao': 'Samoisch (alter Sprachcode)', // deprecated
		'smo': 'Samoisch',
		'sad': 'Sandawe',
		'sag': 'Sango',
		'san': 'Sanskrit',
		'sat': 'Santali',
		'srd': 'Sardisch',
		'sas': 'Sassak (=Sasak)',
		'shn': 'Schan',
		'sho': 'Shona (alter Sprachcode)', // deprecated
		'sna': 'Shona',
		'sco': 'Schottisch',
		'swe': 'Schwedisch',
		'sel': 'Selkupisch',
		'sem': 'Semitische Sprachen (andere)',
		'scc': 'Serbisch (alter Sprachcode)', // deprecated
		'srp': 'Serbisch',
		'scr': 'Serbokroatisch (alter Sprachcode)', // deprecated
		'hrv': 'Kroatisch',
		'srr': 'Serer',
		'iii': 'Sichuan Yi',
		'sid': 'Sidamo',
		'snd': 'Sindhi',
		'snh': 'Singhalesisch (alter Sprachcode)', // deprecated
		'sin': 'Singhalesisch',
		'sit': 'Sinotibetische Sprachen (andere)',
		'sio': 'Sioux-Sprachen',
		'sms': 'Skolt Sami',
		'den': 'Slave (Athapaskische Sprachen)',
		'sla': 'Slawische Sprachen (andere)',
		'slo': 'Slowakisch',
		'slv': 'Slowenisch',
		'sog': 'Sogdisch',
		'som': 'Somali',
		'son': 'Songhai',
		'snk': 'Soninke',
		'wen': 'Sorbisch',
		'nso': 'Sotho (Nord), Pedi',
		'sot': 'Sotho (Süd)',
		'sso': 'Sotho (alter Sprachcode)', // deprecated
		'spa': 'Spanisch, Kastilianisch',
		'swa': 'Suaheli (=Swaheli)',
		'suk': 'Sukuma',
		'sux': 'Sumerisch',
		'sun': 'Sundanesisch',
		'sus': 'Susu',
		'swz': 'Swazi (alter Sprachcode)', // deprecated
		'ssw': 'Swazi',
		'syr': 'Syrisch',
		'taj': 'Tadschikisch (alter Sprachcode)', // deprecated
		'tgk': 'Tadschikisch',
		'tag': 'Tagalog (alter Sprachcode)', // deprecated
		'tgl': 'Tagalog',
		'tah': 'Tahitisch',
		'tmh': 'Tamaseq',
		'tam': 'Tamil',
		'tar': 'Tatarisch (alter Sprachcode)', // deprecated
		'tat': 'Tatarisch',
		'tel': 'Telugu',
		'tem': 'Temne',
		'ter': 'Tereno',
		'tet': 'Tetum',
		'tha': 'Thailändisch',
		'tai': 'Thaisprachen (andere)',
		'tib': 'Tibetisch',
		'tig': 'Tigre',
		'tir': 'Tigrinja',
		'tiv': 'Tiv',
		'tli': 'Tlingit',
		'tkl': 'Tokelauanisch',
		'ton': 'Tonga (Tonga Islands)',
		'tog': 'Tonga, Bantus, Malawi',
		'chk': 'Trukesisch',
		'chg': 'Tschagataisch',
		'cze': 'Tschechisch',
		'chm': 'Tscheremissisch, Mari',
		'qce': 'Tscherkessisch ',
		'che': 'Tschetschenisch',
		'qtc': 'Tschukktschisch',
		'chv': 'Tschuwaschisch',
		'tsi': 'Tsimshian',
		'tso': 'Tsonga (Tsonga)',
		'tsw': 'Tswana (alter Sprachcode)', // deprecated
		'tsn': 'Tswana',
		'tur': 'Türkisch',
		'tum': 'Tumbuka',
		'tup': 'Tupi',
		'tuk': 'Turkmenisch',
		'tyv': 'Tuwinisch',
		'twi': 'Twi',
		'udm': 'Udmurt',
		'uga': 'Ugaritisch',
		'uig': 'Uigurisch',
		'ukr': 'Ukrainisch',
		'und': 'Unbestimmbare Sprachen',
		'hun': 'Ungarisch',
		'urd': 'Urdu',
		'uzb': 'Usbekisch',
		'vai': 'Vai',
		'ven': 'Venda',
		'mis': 'verschiedene Sprachen',
		'vie': 'Vietnamesisch',
		'vol': 'Volapük',
		'wak': 'Wakash-Sprachen',
		'wal': 'Walamo',
		'wel': 'Walisisch',
		'wln': 'Wallonisch',
		'war': 'Waray',
		'was': 'Washo',
		'bel': 'Weißrussisch',
		'qqg': 'Wogulisch, Mansi',
		'wol': 'Wolof',
		'vot': 'Wotisch',
		'xho': 'Xhosa',
		'yao': 'Yao (=Mien=Man)',
		'yap': 'Yapesisch',
		'yor': 'Yoruba (=Joruba)',
		'ypk': 'Yupik',
		'znd': 'Zande',
		'zap': 'Zapoteca',
		'zza': 'Zaza',
		'sgn': 'Zeichensprachen',
		'zen': 'Zenaga',
		'zha': 'Zhuang',
		'rom': 'Zigeunersprache, Romani',
		'zul': 'Zulu',
		'zun': 'Zuni',
		'zzz': 'Ohne Sprachschlüssel'
	},

	'en': {
		'ace': 'Achinese',
		'ach': 'Acoli',
		'ada': 'Adangme',
		'ady': 'Adygei',
		'aar': 'Afar',
		'afh': 'Afrihili (Artificial language)',
		'afr': 'Afrikaans',
		'afa': 'Afroasiatic (Other)',
		'aka': 'Akan',
		'akk': 'Akkadian',
		'alb': 'Albanian',
		'ale': 'Aleut',
		'alg': 'Algonquian (Other)',
		'tut': 'Altaic (Other)',
		'amh': 'Amharic',
		'apa': 'Apache languages',
		'ara': 'Arabic',
		'arg': 'Aragonese Spanish',
		'arc': 'Aramaic',
		'arp': 'Arapaho',
		'arw': 'Arawak',
		'arm': 'Armenian',
		'art': 'Artificial (Other)',
		'asm': 'Assamese',
		'ath': 'Athapascan (Other)',
		'aus': 'Australian languages',
		'map': 'Austronesian (Other)',
		'ava': 'Avaric',
		'ave': 'Avestan',
		'awa': 'Awadhi',
		'aym': 'Aymara',
		'aze': 'Azerbaijani',
		'ast': 'Bable',
		'ban': 'Balinese',
		'bat': 'Baltic (Other)',
		'bal': 'Baluchi',
		'bam': 'Bambara',
		'bai': 'Bamileke languages',
		'bad': 'Banda',
		'bnt': 'Bantu (Other)',
		'bas': 'Basa',
		'bak': 'Bashkir',
		'baq': 'Basque',
		'btk': 'Batak',
		'bej': 'Beja',
		'bel': 'Belarusian',
		'bem': 'Bemba',
		'ben': 'Bengali',
		'ber': 'Berber (Other)',
		'bho': 'Bhojpuri',
		'bih': 'Bihari',
		'bik': 'Bikol',
		'bis': 'Bislama',
		'bos': 'Bosnian',
		'bra': 'Braj',
		'bre': 'Breton',
		'bug': 'Bugis',
		'bul': 'Bulgarian',
		'bua': 'Buriat',
		'bur': 'Burmese',
		'cad': 'Caddo',
		'car': 'Carib',
		'cat': 'Catalan',
		'cau': 'Caucasian (Other)',
		'ceb': 'Cebuano',
		'cel': 'Celtic (Other)',
		'cai': 'Central American Indian (Other)',
		'chg': 'Chagatai',
		'cmc': 'Chamic languages',
		'cha': 'Chamorro',
		'che': 'Chechen',
		'chr': 'Cherokee',
		'chy': 'Cheyenne',
		'chb': 'Chibcha',
		'chi': 'Chinese',
		'chn': 'Chinook jargon',
		'chp': 'Chipewyan',
		'cho': 'Choctaw',
		'chu': 'Church Slavic',
		'chv': 'Chuvash',
		'cop': 'Coptic',
		'cor': 'Cornish',
		'cos': 'Corsican',
		'cre': 'Cree',
		'mus': 'Creek',
		'crp': 'Creoles and Pidgins (Other)',
		'cpe': 'Creoles and Pidgins, English-based (Other)',
		'cpf': 'Creoles and Pidgins, French-based (Other)',
		'cpp': 'Creoles and Pidgins, Portuguese-based (Other)',
		'crh': 'Crimean Tatar',
		'hrv': 'Croatian',
		'cus': 'Cushitic (Other)',
		'cze': 'Czech',
		'dak': 'Dakota',
		'dan': 'Danish',
		'dar': 'Dargwa',
		'day': 'Dayak',
		'del': 'Delaware',
		'din': 'Dinka',
		'div': 'Divehi',
		'doi': 'Dogri',
		'dgr': 'Dogrib',
		'dra': 'Dravidian (Other)',
		'dua': 'Duala',
		'dut': 'Dutch',
		'dum': 'Dutch, Middle (ca. 1050-1350)',
		'dyu': 'Dyula',
		'dzo': 'Dzongkha',
		'bin': 'Edo',
		'efi': 'Efik',
		'egy': 'Egyptian',
		'eka': 'Ekajuk',
		'elx': 'Elamite',
		'eng': 'English',
		'enm': 'English, Middle (1100-1500)',
		'ang': 'English, Old (ca. 450-1100)',
		'esp': 'Esperanto (old language code)', // deprecated
		'epo': 'Esperanto',
		'est': 'Estonian',
		'eth': 'Ethiopic (old language code)', // deprecated
		'gez': 'Ethiopic',
		'ewe': 'Ewe',
		'ewo': 'Ewondo',
		'fan': 'Fang',
		'fat': 'Fanti',
		'far': 'Faroese (old language code)', // deprecated
		'fao': 'Faroese',
		'fij': 'Fijian',
		'fin': 'Finnish',
		'fiu': 'Finno-Ugrian (Other)',
		'fon': 'Fon',
		'fre': 'French',
		'frm': 'French, Middle (ca. 1400-1600)',
		'fro': 'French, Old (ca. 842-1400)',
		'fri': 'Frisian', // deprecated
		'frr': 'North Frisian',
		'frs': 'East Frisian',
		'fur': 'Friulian',
		'ful': 'Fula',
		'gag': 'Galician (old language code)', // deprecated
		'glg': 'Galician',
		'lug': 'Ganda',
		'gay': 'Gayo',
		'gba': 'Gbaya',
		'geo': 'Georgian',
		'ger': 'German',
		'gmh': 'German, Middle High (ca. 1050-1500)',
		'goh': 'German, Old High (ca. 750-1050)',
		'gem': 'Germanic (Other)',
		'gil': 'Gilbertese',
		'gon': 'Gondi',
		'gor': 'Gorontalo',
		'got': 'Gothic',
		'grb': 'Grebo',
		'grc': 'Greek, Ancient (to 1453)',
		'gre': 'Greek',
		'grn': 'Guaraní',
		'guj': 'Gujarati',
		'gwi': 'Gwich’in',
		'gaa': 'Gã',
		'hai': 'Haida',
		'hat': 'Haitian French Creole',
		'hau': 'Hausa',
		'haw': 'Hawaiian',
		'heb': 'Hebrew',
		'her': 'Herero',
		'hil': 'Hiligaynon',
		'him': 'Himachali',
		'hin': 'Hindi',
		'hmo': 'Hiri Motu',
		'hit': 'Hittite',
		'hmn': 'Hmong',
		'hun': 'Hungarian',
		'hup': 'Hupa',
		'iba': 'Iban',
		'ice': 'Icelandic',
		'ido': 'Ido',
		'ibo': 'Igbo',
		'ijo': 'Ijo',
		'ilo': 'Iloko',
		'smn': 'Inari Sami',
		'inc': 'Indic (Other)',
		'ine': 'Indo-European (Other)',
		'ind': 'Indonesian',
		'inh': 'Ingush',
		'ina': 'Interlingua (International Auxiliary Language Association)',
		'ile': 'Interlingue',
		'iku': 'Inuktitut',
		'ipk': 'Inupiaq',
		'ira': 'Iranian (Other)',
		'iri': 'Irish (old language code)', // deprecated
		'gle': 'Irish',
		'mga': 'Irish, Middle (ca. 1100-1550)',
		'sga': 'Irish, Old (to 1100)',
		'iro': 'Iroquoian (Other)',
		'ita': 'Italian',
		'jpn': 'Japanese',
		'jav': 'Javanese',
		'jrb': 'Judeo-Arabic',
		'jpr': 'Judeo-Persian',
		'kbd': 'Kabardian',
		'kab': 'Kabyle',
		'kac': 'Kachin',
		'xal': 'Kalmyk',
		'esk': 'Eskimo', // deprecated
		'kal': 'Kalâtdlisut',
		'kam': 'Kamba',
		'kan': 'Kannada',
		'kau': 'Kanuri',
		'kaa': 'Kara-Kalpak',
		'kar': 'Karen',
		'kas': 'Kashmiri',
		'kaw': 'Kawi',
		'kaz': 'Kazakh',
		'kha': 'Khasi',
		'cam': 'Khmer (old code)', // deprecated
		'khm': 'Khmer',
		'khi': 'Khoisan (Other)',
		'kho': 'Khotanese',
		'kik': 'Kikuyu',
		'kmb': 'Kimbundu',
		'kin': 'Kinyarwanda',
		'kom': 'Komi',
		'kon': 'Kongo',
		'kok': 'Konkani',
		'kor': 'Korean',
		'kpe': 'Kpelle',
		'kro': 'Kru',
		'kua': 'Kuanyama',
		'kum': 'Kumyk',
		'kur': 'Kurdish',
		'kru': 'Kurukh',
		'kos': 'Kusaie',
		'kut': 'Kutenai',
		'kir': 'Kyrgyz',
		'lad': 'Ladino',
		'lah': 'Lahnda',
		'lam': 'Lamba',
		'lao': 'Lao',
		'lat': 'Latin',
		'lav': 'Latvian',
		'ltz': 'Letzeburgesch',
		'lez': 'Lezgian',
		'lim': 'Limburgish',
		'lin': 'Lingala',
		'lit': 'Lithuanian',
		'nds': 'Low German',
		'loz': 'Lozi',
		'lub': 'Luba-Katanga',
		'lua': 'Luba-Lulua',
		'lui': 'Luiseño',
		'smj': 'Lule Sami',
		'lun': 'Lunda',
		'luo': 'Luo (Kenya and Tanzania)',
		'lus': 'Lushai',
		'mac': 'Macedonian',
		'mad': 'Madurese',
		'mag': 'Magahi',
		'mai': 'Maithili',
		'mak': 'Makasar',
		'mlg': 'Malagasy',
		'may': 'Malay',
		'mal': 'Malayalam',
		'mlt': 'Maltese',
		'mnc': 'Manchu',
		'mdr': 'Mandar',
		'man': 'Mandingo',
		'mni': 'Manipuri',
		'mno': 'Manobo languages',
		'max': 'Manx (old language code)', // deprecated
		'glv': 'Manx',
		'mao': 'Maori',
		'arn': 'Mapuche',
		'mar': 'Marathi',
		'chm': 'Mari',
		'mah': 'Marshallese',
		'mwr': 'Marwari',
		'mas': 'Masai',
		'myn': 'Mayan languages',
		'men': 'Mende',
		'mic': 'Micmac',
		'min': 'Minangkabau',
		'mis': 'Miscellaneous languages',
		'moh': 'Mohawk',
		'mol': 'Moldavian',
		'mkh': 'Mon-Khmer (Other)',
		'lol': 'Mongo-Nkundu',
		'mon': 'Mongolian',
		'mos': 'Mooré',
		'mul': 'Multiple languages',
		'mun': 'Munda (Other)',
		'nqo': 'N’Ko',
		'nah': 'Nahuatl',
		'nau': 'Nauru',
		'nav': 'Navajo',
		'nbl': 'Ndebele (South Africa)',
		'nde': 'Ndebele (Zimbabwe)',
		'ndo': 'Ndonga',
		'nap': 'Neapolitan Italian',
		'nep': 'Nepali',
		'new': 'Newari',
		'nia': 'Nias',
		'nic': 'Niger-Kordofanian (Other)',
		'ssa': 'Nilo-Saharan (Other)',
		'niu': 'Niuean',
		'nog': 'Nogai',
		'nai': 'North American Indian (Other)',
		'sme': 'Northern Sami',
		'nso': 'Northern Sotho',
		'nor': 'Norwegian',
		'nob': 'Norwegian (Bokmål)',
		'nno': 'Norwegian (Nynorsk)',
		'nub': 'Nubian languages',
		'nym': 'Nyamwezi',
		'nya': 'Nyanja',
		'nyn': 'Nyankole',
		'nyo': 'Nyoro',
		'nzi': 'Nzima',
		'lan': 'Occitan (post-1500, old language code)', // deprecated
		'oci': 'Occitan (post-1500)',
		'oji': 'Ojibwa',
		'non': 'Old Norse',
		'peo': 'Old Persian (ca. 600-400 B.C.)',
		'ori': 'Oriya',
		'gal': 'Oromo (old language code)', // deprecated
		'orm': 'Oromo',
		'osa': 'Osage',
		'oss': 'Ossetic',
		'oto': 'Otomian languages',
		'pal': 'Pahlavi',
		'pau': 'Palauan',
		'pli': 'Pali',
		'pam': 'Pampanga',
		'pag': 'Pangasinan',
		'pan': 'Panjabi',
		'pap': 'Papiamento',
		'paa': 'Papuan (Other)',
		'per': 'Persian',
		'phi': 'Philippine (Other)',
		'phn': 'Phoenician',
		'pol': 'Polish',
		'pon': 'Ponape',
		'por': 'Portuguese',
		'pra': 'Prakrit languages',
		'pro': 'Provençal (to 1500)',
		'pus': 'Pushto',
		'que': 'Quechua',
		'roh': 'Raeto-Romance',
		'raj': 'Rajasthani',
		'rap': 'Rapanui',
		'rar': 'Rarotongan',
		'roa': 'Romance (Other)',
		'rom': 'Romani',
		'rum': 'Romanian',
		'run': 'Rundi',
		'rus': 'Russian',
		'sal': 'Salishan languages',
		'sam': 'Samaritan Aramaic',
		'lap': 'Sami (old language code)', // deprecated
		'smi': 'Sami',
		'smo': 'Samoan',
		'sad': 'Sandawe',
		'sag': 'Sango (Ubangi Creole)',
		'san': 'Sanskrit',
		'sat': 'Santali',
		'srd': 'Sardinian',
		'sas': 'Sasak',
		'sco': 'Scots',
		'gae': 'Scottish Gaelic (old language code)', // deprecated
		'gla': 'Scottish Gaelic',
		'sel': 'Selkup',
		'sem': 'Semitic (Other)',
		'scc': 'Serbian (old language code)', // deprecated
		'scr': 'Serbocroatian (old language code)', // deprecated
		'srp': 'Serbian',
		'srr': 'Serer',
		'shn': 'Shan',
		'sho': 'Shona (old language code)', // deprecated
		'sna': 'Shona',
		'iii': 'Sichuan Yi',
		'sid': 'Sidamo',
		'sgn': 'Sign languages',
		'bla': 'Siksika',
		'snd': 'Sindhi',
		'snh': 'Sinhalese (old language code)', // deprecated
		'sin': 'Sinhalese',
		'sit': 'Sino-Tibetan (Other)',
		'sio': 'Siouan (Other)',
		'sms': 'Skolt Sami',
		'den': 'Slave',
		'sla': 'Slavic (Other)',
		'slo': 'Slovak',
		'slv': 'Slovenian',
		'sog': 'Sogdian',
		'som': 'Somali',
		'son': 'Songhai',
		'snk': 'Soninke',
		'wen': 'Sorbian languages',
		'sso': 'Sotho (old language code)', // deprecated
		'sot': 'Sotho',
		'sai': 'South American Indian (Other)',
		'sma': 'Southern Sami',
		'spa': 'Spanish',
		'suk': 'Sukuma',
		'sux': 'Sumerian',
		'sun': 'Sundanese',
		'sus': 'Susu',
		'swa': 'Swahili',
		'swz': 'Swazi (old language code)', // deprecated
		'ssw': 'Swazi',
		'swe': 'Swedish',
		'syr': 'Syriac',
		'tag': 'Tagalog (old language code)', // deprecated
		'tgl': 'Tagalog',
		'tah': 'Tahitian',
		'tai': 'Tai (Other)',
		'taj': 'Tajik (old language code)', // deprecated
		'tgk': 'Tajik',
		'tmh': 'Tamashek',
		'tam': 'Tamil',
		'tar': 'Tatar (old language code)', // deprecated
		'tat': 'Tatar',
		'tel': 'Telugu',
		'tem': 'Temne',
		'ter': 'Terena',
		'tet': 'Tetum',
		'tha': 'Thai',
		'tib': 'Tibetan',
		'tig': 'Tigré',
		'tir': 'Tigrinya',
		'tiv': 'Tiv',
		'tli': 'Tlingit',
		'tpi': 'Tok Pisin',
		'tkl': 'Tokelauan',
		'tog': 'Tonga (Nyasa)',
		'ton': 'Tongan',
		'chk': 'Truk',
		'tsi': 'Tsimshian',
		'tso': 'Tsonga',
		'tsw': 'Tswana (old language code)', // deprecated
		'tsn': 'Tswana',
		'tum': 'Tumbuka',
		'tup': 'Tupi languages',
		'tur': 'Turkish',
		'ota': 'Turkish, Ottoman',
		'tuk': 'Turkmen',
		'tvl': 'Tuvaluan',
		'tyv': 'Tuvinian',
		'twi': 'Twi',
		'udm': 'Udmurt',
		'uga': 'Ugaritic',
		'uig': 'Uighur',
		'ukr': 'Ukrainian',
		'umb': 'Umbundu',
		'und': 'Undetermined',
		'urd': 'Urdu',
		'uzb': 'Uzbek',
		'vai': 'Vai',
		'ven': 'Venda',
		'vie': 'Vietnamese',
		'vol': 'Volapük',
		'vot': 'Votic',
		'wak': 'Wakashan languages',
		'wal': 'Walamo',
		'wln': 'Walloon',
		'war': 'Waray',
		'was': 'Washo',
		'wel': 'Welsh',
		'wol': 'Wolof',
		'xho': 'Xhosa',
		'sah': 'Yakut',
		'yao': 'Yao (Africa)',
		'yap': 'Yapese',
		'yid': 'Yiddish',
		'yor': 'Yoruba',
		'ypk': 'Yupik languages',
		'znd': 'Zande',
		'zap': 'Zapotec',
		'zza': 'Zaza',
		'zen': 'Zenaga',
		'zha': 'Zhuang',
		'zul': 'Zulu',
		'zun': 'Zuni',
		'zzz': 'Without language code'
	}
};
