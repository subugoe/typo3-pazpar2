/* Adapted from Indexdata's js-client.js by ssp */

/* A very simple client that shows a basic usage of pz2.js */

/* Create a parameters array and pass it to the pz2's constructor.
 Then register the form submit event with the pz2.search function.
 autoInit is set to true on default.
*/
var usesessions = true;
var pazpar2path = '/pazpar2/search.pz2';
var showResponseType = '';

/*	Maintain a list of all facet types so we can loop over it.
	Don't forget to also set termlist attributes in the corresponding
	metadata tags for the service.
*/
var termListNames = ['xtargets', 'medium', 'language', 'author', 'date'];
var termListMax = {'xtargets': 25, 'medium': 10, 'language': 6, 'author': 10, 'date': 10 };

if (document.location.hash == '#useproxy') {
	usesessions = false;
	pazpar2path = '/service-proxy/';
	showResponseType = 'json';
}


/*	Simple-minded localisation:
	Create a hash for each language, then use the appropriate one on the page.
*/
var germanTerms = {
	'facet-title-xtargets': 'Kataloge',
	'facet-title-medium': 'Art',
	'facet-title-author': 'Autoren',
	'facet-title-language': 'Sprache',
	'facet-title-subject': 'Themengebiete',
	'facet-title-date': 'Jahre',
	'detail-label-title': 'Titel',
	'detail-label-series-title': 'Serie',
	'detail-label-author': 'Autor',
	'detail-label-author-plural': 'Autoren',
	'detail-label-other-person': 'Person',
	'detail-label-other-person-plural': 'Personen',
	'detail-label-medium': 'Art',
	'detail-label-description': 'Information',
	'detail-label-description-plural': 'Informationen',
	'detail-label-series-title': 'Reihe',
	'detail-label-issn': 'ISSN',
	'detail-label-acronym-issn': 'Internationale Standardseriennummer',
	'detail-label-eissn': 'eISSN',
	'detail-label-acronym-eissn': 'Internationale Standardseriennummer für digitale Serien',
	'detail-label-pissn': 'pISSN',
	'detail-label-acronym-pissn': 'Internationale Standardseriennummer für gedruckte Serien',	
	'detail-label-acronym-isbn': 'Internationale Standardbuchnummer',
	'detail-label-doi': 'DOI',
	'detail-label-acronym-doi': 'Document Object Identifier: Mit dem Link zu dieser Nummer kann das Dokument im Netz gefunden werden.',
	'detail-label-doi-plural': 'DOIs',
	'detail-label-verfügbarkeit': 'Verfügbarkeit',
	'elektronisch': 'digital',
	'gedruckt': 'gedruckt',
	'detail-label-id': 'PPN',
	'link': '[Link]',
	'Kataloge': 'Kataloge',
	'Google Books Vorschau': 'Google Books Vorschau',
	'Umschlagbild': 'Umschlagbild',
}


var localisations = {
	'de': germanTerms,
};



/*	localise
	Return localised term using the passed dictionary
		or the one stored in localisations variable.
	The localisation dictionary has two-letter language codes as keys.
		For each of them there is a dictionary with translations to that language.
	input:	term - string to localise
			externalDictionary (optional) - localisation dictionary
	output:	localised string
*/
function localise (term, externalDictionary) {
	var dictionary = localisations;

	if (externalDictionary) {
		dictionary = externalDictionary;
	}

	var localised = dictionary['de'][term];
	if ( localised == undefined ) {
		localised = term;
	}

	return localised;
}


function my_errorHandler (error) {
	if (error.code == 1 && this.request.status == 417) {
		// session has expired, create a new one
		my_paz.init(undefined, my_paz.serviceId);
	}
}




my_paz = new pz2( {	"onshow": my_onshow,
					"showtime": 1000,//each timer (show, stat, term, bytarget) can be specified this way
					"pazpar2path": pazpar2path,
					"oninit": my_oninit,
					"onstat": my_onstat,
					"onterm": my_onterm,
					"termlist": termListNames.join(","),
					"onbytarget": my_onbytarget,
	 				"usesessions" : usesessions,
					"showResponseType": showResponseType,
					"onrecord": my_onrecord,
					"serviceId": my_serviceID,
					"errorhandler": my_errorHandler,
} );



// some state vars
var curPage = 1;
var recPerPage = 100;
var fetchRecords = 500;
var curDetRecId = '';
var curDetRecData = null;
var curSort = [];
var curFilter = null;
var facetData = {}; // stores faceting information as sent by pazpar2
var filterArray = {};
var displaySort =  [{'fieldName': 'date', 'direction': 'descending'},
						{'fieldName': 'author', 'direction': 'ascending'},
						{'fieldName': 'title', 'direction': 'ascending'}];
var displayFilter = undefined;
var hitList = []; // local storage for the records sent from pazpar2
var displayHitList = []; // filtered and sorted list used for display
var submitted = false;
var useGoogleBooks = false;
var useZDB = false;


//
// pz2.js event handlers:
//
function my_oninit() {
	my_paz.stat();
	my_paz.bytarget();
}



/*	appendInfoToContainer
	Convenince method to append an item to another one, even if undefineds and arrays are involved.
	input:	* info - the DOM element to insert
			* container - the DOM element to insert info to
*/
var appendInfoToContainer = function (info, container) {
	if (info != undefined && container != undefined ) {
		if (typeof(info.length) === 'undefined') {
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



/*	fieldContentInRecord
	Returns a record's md-fieldName field.
		* Concatenated when several instances of the field are present.
	input:	fieldName - name of the field to use
			record - pazpar2 record
			lowerCase (optional) - boolean indicating whether to transform the string to lower case
	output: string with content of the field in the record
*/
function fieldContentInRecord (fieldName, record, lowerCase) {
	var result = String(fieldContentsInRecord(fieldName, record));

	if (lowerCase) {
		result.toLowerCase();
	}

	return result;
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
		for ( var locationNumber in record.location) {
			result.push(record.location[locationNumber]['@name']);
		}
	}
	else if ( fieldName === 'date' ) {
		// special case for dates: go through locations and collect date for each edition
		for ( var locationNumber in record.location) {
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





/*	displayList
	Converts a given list of data to a the list used for display by:
		1. applying filters
		2. sorting
	according to the setup in the displaySort and displayFilter variables.
*/
function displayList (list) {
	/*	filter
		Returns a filtered list of pazpar2 records according to the current filterArray.
		input:	list - list of pazpar2 records
		output:	list of pazpar2 records
	*/
	var filter = function (listToFilter) {
		/*	matchesFilters
			Returns whether the passed record passes all filters.
				I.e.: for each facet type it meets one of the listed conditions.
			input:	record - pazpar2 record
			output: boolean
		*/
		var matchesFilters = function (record) {
			var matches = true;
			for (var facetType in filterArray) {
				for (var filterIndex in filterArray[facetType]) {
					matches = false;
					var filterValue = filterArray[facetType][filterIndex];
					if (facetType === 'xtargets') {
						for (var locationIndex in record.location) {
							matches = (record.location[locationIndex]['@name'] == filterValue);
							if (matches) { break; }
						}
					}
					else {
						var contents = fieldContentsInRecord(facetType, record);
						for (var index in contents) {
							matches = (String(contents[index]).toLowerCase() == filterValue.toLowerCase());
							if (matches) { break; }
						}
					}

					if (!matches) { break; }
				}

				if (!matches) {	break; }
			}

			return matches;
		}


		var filteredList = [];
		for (var index in listToFilter) {
			var item = listToFilter[index];
			if ( matchesFilters(item) ) {
				filteredList.push(item);
			}
		}
		return filteredList;
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
			If no year is present
			input:	record - pazpar2 record
			output: Date object with year found in the pazpar2 record
		*/
		function dateForRecord (record) {
			var dateArray = record['md-date'];
			if (dateArray) {
				var dateString = record['md-date'][0];
				if (dateString) {
					var dateArray = dateString.split('-');
					var date = new Date(dateArray[dateArray.length - 1]);
				}
			}

			// Records without a date are treated as very old.
			// Except when they are Guide Links which are treated as coming from the future.
			if (date == undefined) {
				if (record['location'][0]['@id'].search('ssgfi') != -1) {
					date = new Date(2500,1,1);
				}
				else {
					date = new Date(0,1,1);
				}	
			}

			return date;
		}

		
		var result = 0;

		for (var sortCriterionIndex in displaySort) {
			var fieldName = displaySort[sortCriterionIndex].fieldName;
			var direction = (displaySort[sortCriterionIndex].direction === 'ascending') ? 1 : -1;

			if (fieldName == 'date') {
				var date1 = dateForRecord(record1);
				var date2 = dateForRecord(record2);
				
				result = (date1.getTime() - date2.getTime()) * direction;
			}
			else {
				var string1 = fieldContentInRecord(fieldName, record1, true);
				var string2 = fieldContentInRecord(fieldName, record2, true);
				
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
	

	return filter(list).sort(sortFunction);
}



/*	updateAndDisplay
	Updates the displayHitList and redraws.
*/
function updateAndDisplay () {
	displayHitList = displayList(hitList);
	display();
	updateFacetLists();
}



/*	my_onshow
	Callback for pazpar2 when data become available.
	Goes through the records and adds them to hitList.
	Regenerates displayHitList and triggers redisplay.
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
				if (oldHit.location.count == hit.location.count) {
					// preserve old details Div, if the location info hasn't changed
					hit.detailsDiv = hitList[hitID].detailsDiv;
				}
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
		Creates DOM element and content for a field name; Appends it to given container.
		input:	fieldName - string with key for a field stored in hit
				container (optional)- the DOM element we created is appended here
				prepend (optional) - string inserted before the DOM element with the field data
				append (optional) - string appended after the DOM element with the field data
		output: the DOM SPAN element that was appended
	*/
	var markupForField = function (fieldName, container, prepend, append) {
		var theHit = hit['md-' + fieldName];

		if (theHit !== undefined && container) {
			var span = document.createElement('span');
			span.setAttribute('class', 'pz2-' + fieldName);
			span.appendChild(document.createTextNode(theHit));
		
			if (container) {
				if (prepend) {
					container.appendChild(document.createTextNode(prepend));
				}
				container.appendChild(span);
				if (append) {
					container.appendChild(document.createTextNode(append));
				}
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
		titleCompleteElement.setAttribute('class', 'pz2-title-complete');

		var titleMainElement = document.createElement('span');
		titleCompleteElement.appendChild(titleMainElement);
		titleMainElement.setAttribute('class', 'pz2-title-main');
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
			output.setAttribute('class', 'pz2-item-responsibility');
			output.appendChild(document.createTextNode(outputText + extraFullStop))
		}
		
		return output;
	}



	/*	appendJournalInfo
		Appends DOM SPAN element with the current hit's journal information to linkElement.
	*/
	var appendJournalInfo = function () {
		var output = document.createElement('span');
		output.setAttribute('class', 'pz2-journal');

		var journalTitle = markupForField('journal-title', linkElement, localise(' In') + ': ');
		if (journalTitle) {
			markupForField('journal-subpart', journalTitle, ', ')
			journalTitle.appendChild(document.createTextNode('.'));
		}
	}


	/* 	updatePagers
		Updates pagers and record counts shown in .pz2-pager elements.
	*/
	var updatePagers = function () {
		$('div.pz2-pager').each( function(index) {
				// Remove existing pager content, preserving the progressIndicator
				var progress = $(this).children('.pz2-progressIndicator');
				$(this).empty()
				if (progress.length == 1) {
					this.appendChild(progress[0]);
				}

				var onsides = 6;
				var pages = Math.ceil(displayHitList.length / recPerPage);
	
				var firstClickable = ( curPage - onsides > 0 ) ? curPage - onsides : 1;
				var lastClickable = firstClickable + 2*onsides < pages ? firstClickable + 2*onsides	: pages;

				// create pager
				if (curPage > 1) {
					var prevLink = document.createElement('a');
					prevLink.setAttribute('class', 'pz2-prev');
					prevLink.setAttribute('href', '#');
					prevLink.setAttribute('onclick', 'pagerPrev();return false;');
					prevLink.setAttribute('title', localise('Vorige Trefferseite anzeigen'));
					prevLink.appendChild(document.createTextNode('«'));
					this.appendChild(prevLink);
				}
		
				var pageList = document.createElement('ol');
				pageList.setAttribute('class', 'pz2-pages');
				this.appendChild(pageList);	
			
				var dotsItem = document.createElement('li');
				dotsItem.appendChild(document.createTextNode('…'));
			
				if (firstClickable > 1) {
					pageList.appendChild(dotsItem.cloneNode());
				}
		
				for(var pageNumber = firstClickable; pageNumber <= lastClickable; pageNumber++) {
					var pageItem = document.createElement('li');
					pageList.appendChild(pageItem);
					if(pageNumber != curPage) {
						var linkElement = document.createElement('a');
						linkElement.setAttribute('href', '#');
						linkElement.setAttribute('onclick', 'showPage(' + pageNumber + ');return false;');
						linkElement.appendChild(document.createTextNode(pageNumber));
						pageItem.appendChild(linkElement);
					}
					else {
						pageItem.setAttribute('class', 'currentPage');
						pageItem.appendChild(document.createTextNode(pageNumber));
					}
				}

				if (pages - curPage > 0) {
					var nextLink = document.createElement('a');
					nextLink.setAttribute('class', 'pz2-next');
					nextLink.setAttribute('href', '#');
					nextLink.setAttribute('onclick', 'pagerNext();return false;');
					nextLink.setAttribute('title', localise('Nächste Trefferseite anzeigen'));
					nextLink.appendChild(document.createTextNode('»'));
					this.appendChild(nextLink);			
				}
		
				if (lastClickable < pages) {
					pageList.appendChild(dotsItem);
				}
			
			
				// add record count information
				var recordCountDiv = document.createElement('div');
				recordCountDiv.setAttribute('class', 'pz2-recordCount');
				var infoString = String(firstIndex + 1) + '-'
									+ String(firstIndex + numberOfRecordsOnPage)
									+ ' ' + localise('von') + ' '
									+ String(displayHitList.length);
				if (displayFilter) {
					infoString += ' ' + localise('gefiltert');
				}
				recordCountDiv.appendChild(document.createTextNode(infoString));
				this.appendChild(recordCountDiv);
			}
		);	
	}



	// Create results list.
	var OL = document.createElement('ol');
	var firstIndex = recPerPage * (curPage - 1);
	var numberOfRecordsOnPage = Math.min(displayHitList.length - firstIndex, recPerPage);
	OL.setAttribute('start', firstIndex + 1);

	for (var i = 0; i < numberOfRecordsOnPage; i++) {
		var hit = displayHitList[firstIndex + i];

		var LI = document.createElement('li');
		OL.appendChild(LI);
		LI.setAttribute('id', 'recdiv_' + HTMLIDForRecordData(hit));

		var linkElement = document.createElement('a');
		LI.appendChild(linkElement);
		linkElement.setAttribute('href', '#');
		linkElement.setAttribute('class', 'pz2-recordLink');
		linkElement.setAttribute('onclick', 'toggleDetails(this.id);return false;');
		linkElement.setAttribute('id', 'rec_' + HTMLIDForRecordData(hit));

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

		if (hit.detailsDivVisible) {
			var detailsDiv = hit.detailsDiv;
			if (!detailsDiv) {
				detailsDiv = renderDetails(hit.recid[0]);
				hit.detailsDiv = detailsDiv;
			}
			appendInfoToContainer(detailsDiv, LI);	
		}
	}

	// Replace old results list
	var results = document.getElementById("pz2-results");
	$(results).empty();
	results.appendChild(OL);

	updatePagers();
}



/*	my_onstat
	Callback for pazpar2 status information. Updates status display.
	input:	data - object with status information from pazpar2
*/
function my_onstat(data) {
	// Display progress bar.
	var progress = (data.clients - data.activeclients) / data.clients * 100;
	var opacityValue = (progress == 100) ? 0 : 1;
	$('.pz2-pager .pz2-progressIndicator').animate({width: progress + '%', opacity: opacityValue}, 100);

	// Write out status information.
	var statDiv = document.getElementById('pz2-stat');
	if (statDiv) {
		$(statDiv).empty();

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
				if (isFiltered) { break; }
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
			for (var recordIndex in displayHitList) {
				var record = displayHitList[recordIndex];
				var dataArray = fieldContentsInRecord(type, record);
				for (var index in dataArray) {
					var data = dataArray[index];
					if (termArray[data]) {
						termArray[data]++;
					}
					else {
						termArray[data] = 1;
					}
				}
			}
			
			// Sort by term frequency.
			for (var term in termArray) {
				termList.push({'name': term, 'freq': termArray[term]});
			}

			if (termList.length > 0) {
				termList.sort( function(term1, term2) {
						if (term1.freq < term2.freq) { return 1; }
						else if (term1.freq == term2.freq) {
							if (term1.name < term2.name) { return -1; }
							else { return 1; }
						}
						else { return -1; }
					}
				);
	
				// Note the maximum number
				termList['maximumNumber'] = termList[0].freq;

				// Special case for dates: take the most frequent items and sort by date.
				if (type === 'date') {
					if (termList.length > termListMax['date']) {
						termList.splice(termListMax['date'], termList.length - termListMax['date']);
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


	// Create container and heading.
	var container = document.createElement('div');
	container.setAttribute('class', 'pz2-termList pz2-termList-' + type);
	var heading = document.createElement('h5')
	container.appendChild(heading);
	var headingText = localise('facet-title-'+type);
	if (isFilteredForType(type)) {
		headingText += ' [' + localise('gefiltert') + ']';
	}
	heading.appendChild(document.createTextNode(headingText));
	var list = document.createElement('ol');
	container.appendChild(list);

	// Loop through list of terms for the type and create an item with link for each one.
	var terms = facetInformationForType(type);
	for (var i = 0; i < terms.length && i < termListMax[type]; i++) {
		var facetName = terms[i].name;

		var item = document.createElement('li');
		list.appendChild(item);

		// Link
		var link = document.createElement('a');
		item.appendChild(link);
		link.setAttribute('href', '#');
		link.setAttribute('onclick',
			'limitResults("' + type + '","' + facetName + '");return false;');

		// 'Progress bar'
		var progressBar = document.createElement('div');
		link.appendChild(progressBar);
		var progress = terms[i].freq / terms['maximumNumber'] * 100;
		progressBar.setAttribute('style', 'width:' + progress + '%;');
		progressBar.setAttribute('class', 'pz2-progressIndicator');

		// Facet Display Name
		var facetDisplayName = facetName;
		if (type === 'language') {
			facetDisplayName = localise(facetName, languageNames);
		}
		else if (type === 'medium') {
			facetDisplayName = localise(facetName, mediaNames);			
		}
		var textSpan = document.createElement('span');
		link.appendChild(textSpan);
		textSpan.appendChild(document.createTextNode(facetDisplayName));

		// Hit Count
		var count = document.createElement('span');
		link.appendChild(count);
		count.setAttribute('class', 'pz2-facetCount');
			count.appendChild(document.createTextNode(terms[i].freq));

		// Mark facets which are currently active and add button to remove faceting.
		if (isFilteredForType(type)) {
			for (var filterIndex in filterArray[type]) {
				if (facetName === filterArray[type][filterIndex]) {
					item.setAttribute('class', 'pz2-activeFacet');
					var cancelLink = document.createElement('a');
					item.appendChild(cancelLink);
					cancelLink.setAttribute('href', '#');
					cancelLink.setAttribute('class', 'pz2-facetCancel');
					cancelLink.setAttribute('onclick',
						'delimitResults("' + type + '","' + facetName + '"); return false;');
					cancelLink.appendChild(document.createTextNode(localise('Filter aufheben')));
					break;
				}
			}
		}
	}
	return container;		
}



/*	updateFacetLists
	Updates all facet lists for the facet names stored in termListNames.
*/
function updateFacetLists () {
	var container = document.getElementById('pz2-termLists');
	$(container).empty();

	var mainHeading = document.createElement('h4');
	container.appendChild(mainHeading);
	mainHeading.appendChild(document.createTextNode(localise('Facetten')));

	for ( index in termListNames ) {
		container.appendChild(facetListForType(termListNames[index]));
	}
}



/*	my_onterm
	pazpar2 callback for receiving facet data.
		Stores faces data and recreates facets on page.
	input:	data - Array with facet information.
*/
function my_onterm (data) {
	facetData = data;
	updateFacetLists();
}


function my_onrecord(data) {
}



/*	my_onbytarget
	Callback for target status information. Updates the status display.
	input:	data - list coming from pazpar2
*/
function my_onbytarget(data) {
	var targetDiv = document.getElementById("pz2-byTarget");
	$(targetDiv).empty();

	var table = document.createElement('table');
	targetDiv.appendChild(table);

	var thead = document.createElement('thead');
	table.appendChild(thead);
	var tr = document.createElement('tr');
	thead.appendChild(tr);
	var td = document.createElement('td');
	tr.appendChild(td);
	td.appendChild(document.createTextNode(localise('Datenbank URL')));
	td = document.createElement('td');
	tr.appendChild(td);
	td.appendChild(document.createTextNode(localise('Treffer')));
	td = document.createElement('td');
	tr.appendChild(td);
	td.appendChild(document.createTextNode(localise('Code')));
	td = document.createElement('td');
	tr.appendChild(td);
	td.appendChild(document.createTextNode(localise('Gesamt')));
	td = document.createElement('td');
	tr.appendChild(td);
	td.appendChild(document.createTextNode(localise('Status')));
	
	var tbody = document.createElement('tbody');
	table.appendChild(tbody);

	for (var i = 0; i < data.length; i++ ) {
		tr = document.createElement('tr');
		tbody.appendChild(tr);
		td = document.createElement('td');
		tr.appendChild(td);
		td.appendChild(document.createTextNode(data[i].id));
		td = document.createElement('td');
		tr.appendChild(td);
		td.appendChild(document.createTextNode(data[i].hits));
		td = document.createElement('td');
		tr.appendChild(td);
		td.appendChild(document.createTextNode(data[i].diagnostic));
		td = document.createElement('td');
		tr.appendChild(td);
		td.appendChild(document.createTextNode(data[i].records));
		td = document.createElement('td');
		tr.appendChild(td);
		td.appendChild(document.createTextNode(localise(data[i].state)));
	}
}

////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////



/*	domReady
	Called when the page is loaded. Sets up JavaScript-based search mechanism.
*/
function domReady ()  {
	$('.pz2-searchForm').each( function(index, form) {
			form.onsubmit = onFormSubmitEventHandler;
			form.action = null;
			form.method = null;
		}
	);

	$('.pz2-searchForm .pz2-searchField').val('');
	$('.pz2-sort, .pz2-perPage').attr('onchange', 'onSelectDidChange');
}



/*	onFormSubmitEventHandler
	Called when the search button is pressed. Submits the query to the pazpar2 server.
*/
function onFormSubmitEventHandler () {
	resetPage();
	triggerSearchForForm(this);
	submitted = true;
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
	hitList = [];
	displayHitList = [];
	filterArray = {};
	display();
}



/*	triggerSearchForForm
	Triggers search by pazpar2 middleware.
		Called by clicking the search button.
	input:	form - DOM element of the form used to trigger the search
*/
function triggerSearchForForm (form) {
	var searchTerm = $('.pz2-searchField', form).val();
	loadSelectsFromForm(form);
	my_paz.search(searchTerm, fetchRecords, curSort, curFilter);
}



/*	setSortCriteriaFromString
	Takes the passed sort value string with sort criteria separated by --
		and labels and value inside the criteria separated by -,
			[this strange format is owed to escaping problems when creating a Flow3 template for the form]
		parses them and sets the displaySort and curSort variables accordingly.
	input:	curSortString - string giving the sort format
*/
function setSortCriteriaFromString (curSortString) {
	var sortCriteria = curSortString.split('--');

	displaySort = [];
	var curSortArray = [];

	for ( var criterionIndex in sortCriteria ) {
		var criterionParts = sortCriteria[criterionIndex].split('-');
		if (criterionParts.length == 2) {
			var fieldName = criterionParts[0];
			var direction = criterionParts[1];
			displaySort.push({'fieldName': fieldName,
								'direction': ((direction == 'd') ? 'descending' : 'ascending')});
			curSortArray.push(fieldName + ':' + ((direction == 'd') ? '0' : '1') );
		}
	}

	curSort = curSortArray.join(',');
}




/*	loadSelectsFromForm
	Retrieves current settings for sort order and items per page from the form that is passed.
	input:	form - DOM element of the form to get the data from
*/
function loadSelectsFromForm (form) {
	var sortOrderString = $('.pz2-sort option:selected', form).val();
	setSortCriteriaFromString(sortOrderString);

	recPerPage = $('.pz2-perPage option:selected', form).val();
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





// switching view between targets and records

function switchView(view) {
	
	var targets = document.getElementById('pz2-targetView');
	var records = document.getElementById('pz2-recordView');
	
	switch(view) {
		case 'pz2-targetView':
			targets.style.display = "block";			
			records.style.display = "none";
			break;
		case 'pz2-recordView':
			targets.style.display = "none";			
			records.style.display = "block";
			break;
		default:
			alert('Unknown view.');
	}
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

	var detRecordDivVisible = record.detailsDivVisible;

	if (detRecordDivVisible) {
		// Detailed record information is present: remove it
		$('#det_'+ recordIDHTML).remove();
		record.detailsDivVisible = false;
	}
	else {
		// Detailed record information is not present: get detail view and append it
		if (!record.detailsDiv) {
			record.detailsDiv = renderDetails(recordID);
		}

		var parent = document.getElementById('recdiv_'+ recordIDHTML);
		parent.appendChild(record.detailsDiv);
		record.detailsDivVisible = true;
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
	/*	deduplicate
		Removes duplicate entries from an array.
		The first occurrence of any item is kept, later ones are removed.
		This function works in place and alters the original array.
		input:	information - array to remove duplicate entries from.
	*/
	var deduplicate = function (information) {
		for (var i = 0; i < information.length; i++) {
			var item = information[i];
			var isDuplicate = false;
			for (var j = 0; j < i; j++) {
				var jtem = information[j];					
				if ( item == jtem) {
					isDuplicate = true;
					information.splice(i, 1);
					i--;
					break;
				}
			}
		}	
	}



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
			$(infoItems).each( function(index) {
					var LI = document.createElement('li');
					result.appendChild(LI);
					LI.appendChild(this);
				}
			);
		}

		return result;
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
		if (informationElements && title) {
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
				var line = [];

				var rowTitle = document.createElement('dt');
				line.push(rowTitle);
				var labelNode = document.createTextNode(headingText + ':');
				var acronymKey = 'detail-label-acronym-' + title;
				if (localise(acronymKey) !== acronymKey) {
					// acronym: add acronym element
					var acronymElement = document.createElement('acronym');
					acronymElement.setAttribute('title', localise(acronymKey));
					acronymElement.appendChild(labelNode);
					labelNode = acronymElement;
				}
				rowTitle.appendChild(labelNode);

				var rowData = document.createElement('dd');
				line.push(rowData);
				rowData.appendChild(infoItems);
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
		linkElement.setAttribute('target', 'pz2-linktarget');
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

			// run loop backwards as pazpar2 seems to reverse the order of metadata items
			for (var dataNumber = theData.length - 1; dataNumber >= 0; dataNumber--) {
				var rawDatum = theData[dataNumber];
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



	/*	ZDBQuery
		Loads XML journal info from ZDB via a proxy on our own server
			(to avoid cross-domain load problems).
		Inserts the information into the DOM.

		input:	element - DOM element that the resulting information is inserted into.
	*/
	var addZDBInfoIntoElement = function (element) {
		// Do nothing if there are no ISSNs.
		var ISSN = (data['md-issn']) ? data['md-issn'] : data['md-pissn'];
		var eISSN = data['md-eissn'];
		
		if ( !(ISSN || eISSN) ) { return; }

		var serviceID = 'sub:vlib';
		var parameters = 'sid=' + serviceID + '&genre=article';

		if ( ISSN ) {
			parameters += '&issn=' + ISSN;
		}
		
		if ( eISSN ) {
			parameters += '&eissn=' + eISSN;
		}

		// Add additional information to request to get more precise result and better display.
		var year = data['md-date'];
		if (year) {
			var yearNumber = parseInt(year[0], 10);
			parameters += '&date=' + yearNumber;
		}

		var volume = data['md-journal-volume'];
		if (volume) {
			var volumeNumber = parseInt(volume, 10);
			parameters += '&volume=' + volumeNumber;
		}

		var issue = data['md-journal-issue'];
		if (issue) {
			var issueNumber = parseInt(issue, 10);
			parameters += '&issue=' + issueNumber;
		}

		var pages = data['md-journal-pages'];
		if (pages) {
			parameters += '&pages=' + pages;
		}

		var title = data['md-title'];
		if (title) {
			parameters += '&atitle=' + encodeURI(title);
		}


		// Run the ZDB query.
		var ZDBURL = '/zdb/full.xml?' + parameters;

		$.get(ZDBURL,
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
						var statusDiv = document.createElement('div');
						statusDiv.setAttribute('class', 'pz2-ZDBStatusInfo');

						var accessLinkURL = $('AccessURL', ZDBResult);
						if (accessLinkURL.length > 0) {
							// Having an AccessURL implies this is inside ElectronicData.
							statusDiv.appendChild(document.createTextNode(statusText));
							var accessLink = document.createElement('a');
							statusDiv.appendChild(document.createTextNode(' – '));
							statusDiv.appendChild(accessLink);
							accessLink.setAttribute('href', accessLinkURL[0].textContent);
							accessLink.appendChild(document.createTextNode(localise('Zugriff')));
							accessLink.setAttribute('target', 'pz2-linktarget');

							var additionals = [];
							var ZDBAdditionals = $('Additional', ZDBResult);
							ZDBAdditionals.each( function (index) {
									additionals.push(this.textContent);
								}
							);
							if (additionals.length > 0) {
								accessLink.setAttribute('title', additionals.join('; ') + '.');
							}
						}
						else if (status < 4) {
							// Absence of an AccessURL implies this is inside PrintData.
							// status > 3 means the volume is not available. Don't print info then.
							var locationInfo = document.createElement('span');
							var infoText = '';

							var period = $('Period', ZDBResult)[0];
							if (period) {
								infoText += period.textContent + ': ';

							}
							var location = $('Location', ZDBResult)[0];
							if (location) {
								infoText += location.textContent + ' ';
							}

							var signature = $('Signature', ZDBResult)[0];
							if (signature) {
								infoText += signature.textContent;
							}

							locationInfo.appendChild(document.createTextNode(infoText));
							statusDiv.appendChild(locationInfo);
						}
						else {
							statusDiv = undefined;
						}
					}	
					return statusDiv;
				}



				/*	appendLibraryNameFromResultDataTo
					If we there is a Library name, insert it into the target container.
					input:	* data: ElectronicData or PrintData element from ZDB XML
							* target: DOM container to which the marked up library name is appended
				*/
				var appendLibraryNameFromResultDataTo = function (data, target) {
					var libraryName = $('Library', data)[0];
					if (libraryName) {
						var libraryNameSpan = document.createElement('span');
						libraryNameSpan.setAttribute('class', 'pz2-ZDBLibraryName');
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
					var results = $('Result', data);

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
					var container = document.createElement('div');
					var ZDBLink = document.createElement('a');
					container.appendChild(ZDBLink);
					var ZDBLinkURL = 'http://services.d-nb.de/fize-service/gvr/html-service.htm?' + parameters;
					ZDBLink.setAttribute('href', ZDBLinkURL);
					ZDBLink.setAttribute('class', 'pz2-ZDBLink');
					ZDBLink.setAttribute('target', 'pz2-linktarget');
					ZDBLink.appendChild(document.createTextNode(localise('Informationen bei der Zeitschriftendatenbank')));
					
					var electronicInfos = ZDBInfoElement( $('ElectronicData', data) );
					var printInfos = ZDBInfoElement( $('PrintData', data) );
					
					if (electronicInfos || printInfos) {
						appendLibraryNameFromResultDataTo(data, container);
					}

					if (electronicInfos) {
						var heading = document.createElement('h4');
						container.appendChild(heading);
						heading.appendChild(document.createTextNode(localise('elektronisch')));
						container.appendChild(electronicInfos);
					}

					if (printInfos) {
						var heading = document.createElement('h4');
						container.appendChild(heading);
						heading.appendChild(document.createTextNode(localise('gedruckt')));
						container.appendChild(printInfos);
					}

					return container
				}


				var infoBlock = [ZDBInformation(resultData)];
				appendInfoToContainer( detailLine(localise('verfügbarkeit'), infoBlock), element);

			}
		);
	}
	



	/*	googleBooksElement
		Figure out whether there is a Google Books Preview for the current data.
		output:	SPAN DOM element that will contain the Google Books button and cover art.
	*/
	var googleBooksElement = function () {
		var booksSpan = document.createElement('span');
		booksSpan.setAttribute('class', 'googleBooks');


		// Create list of search terms from ISBN and OCLC numbers.
		var searchTerms = [];
		for (locationNumber in data.location) {
			var numberField = String(data.location[locationNumber]['md-isbn']);
			var matches = numberField.replace(/-/g,'').match(/[0-9]{9,12}[0-9xX]/g);
			for (var matchNumber in matches) {
				searchTerms.push('ISBN:' + matches[matchNumber]);
			}
			numberField = String(data.location[locationNumber]['md-oclc-number']);
			matches = numberField.match(/[0-9]{4,}/g);
			for (var matchNumber in matches) {
				searchTerms.push('OCLC:' + matches[matchNumber]);
			}
		}
		

		// Query Google Books for the ISBN/OCLC numbers in question.
		var googleBooksURL = 'http://books.google.com/books?bibkeys=' + searchTerms
					+ '&jscmd=viewapi&callback=?';
		$.getJSON(googleBooksURL,
			function(data) {
				/*
					If there are multiple results choose the one we want:
						1. If available the first one with 'full' preview capabilities,
						2. otherwise the first one with 'partial' preview capabilities,
						3. undefined if none of the results has preview capabilities.
					Usually the first item in the list is also the newest one.
				*/
				var selectedBook;
				$.each(data,
					function(bookNumber, book) {
						if (book.preview === 'full') {
							selectedBook = book;
							return false;
						}
						else if (book.preview === 'partial' && selectedBook === undefined) {
							selectedBook = book;
						}
					}
				);
			
				// Add link to Google Books if there is a selected book.
				if (selectedBook !== undefined) {
					var bookLink = document.createElement('a');
					bookLink.setAttribute('href', selectedBook.preview_url);
					bookLink.onclick = openPreview;

					var language = $('html').attr('lang');
					if (language === undefined) {
						language = 'en';
					}
					var buttonImageURL = 'http://www.google.com/intl/' + language + '/googlebooks/images/gbs_preview_button1.gif';
					var buttonImage = document.createElement('img');
					buttonImage.setAttribute('src', buttonImageURL);
					buttonImage.setAttribute('alt', localise('Google Books Vorschau'));
					bookLink.appendChild(buttonImage);
					booksSpan.appendChild(bookLink);

					if (selectedBook.thumbnail_url !== undefined) {
						var coverArtImage = document.createElement('img');
						bookLink.appendChild(coverArtImage);
						coverArtImage.setAttribute('src', selectedBook.thumbnail_url);
						coverArtImage.setAttribute('alt', localise('Umschlagbild'));
						coverArtImage.setAttribute('class', 'bookCover');
					}
				}
			}
		);

		return booksSpan;


		/*	openPreview
			Called when the Google Books button is clicked.
			Opens Google Preview.
			output: false (so the click isn't handled any further)
		*/
		var openPreview = function() {
			// Get hold of containing <div>, creating it if necessary.
			var previewContainerDivName = 'googlePreviewContainer';
			var previewContainerDiv = document.getElementById(previewContainerDivName);
			var previewDivName = 'googlePreview';
			var previewDiv = document.getElementById(previewDivName);

			if (!previewContainerDiv) {
				previewContainerDiv = document.createElement('div');
				previewContainerDiv.setAttribute('id', previewContainerDivName);
				$('#page').get(0).appendChild(previewContainerDiv);

				var titleBarDiv = document.createElement('div');
				titleBarDiv.setAttribute('class', 'titleBar');
				previewContainerDiv.appendChild(titleBarDiv);
				$(titleBarDiv).css({height:'20px', width:'100%',
									position:'absolute', top:'-20px', background:'#eee'});

				var closeBoxLink = document.createElement('a');
				titleBarDiv.appendChild(closeBoxLink);
				$(closeBoxLink).css({display:'block', height:'16px', width:'16px',
									position:'absolute', right:'2px', top:'2px', background:'#666'})
				closeBoxLink.setAttribute('href', '#');
				closeBoxLink.setAttribute('onclick', 'javascript:$("#' + previewContainerDivName + '").hide(200);return false;');

				var previewDiv = document.createElement('div');
				previewDiv.setAttribute('id', previewDivName);
				previewContainerDiv.appendChild(previewDiv);
			}
			else {
				$(previewContainerDiv).show(200);
			}

			var viewer = new google.books.DefaultViewer(previewDiv);
			viewer.load(this.href);

			return false;
		}

	} // end of addGoogleBooksLinkIntoElement


	
	/*	extraLinks
		Returns an array with markup for extra links and information.
			* Google Books, if possible
		output:	Array with DT/DD pair containing the information.
	*/
	var extraLinks = function () {
		var titleElement = document.createElement('dt');

		var dataElement = document.createElement('dd');
		dataElement.setAttribute('class', 'pz2-extraLinks');
		
		if (useGoogleBooks) {
			dataElement.appendChild(googleBooksElement());
		}
		
		return [titleElement, dataElement];
	}



	/*	locationDetails
		Returns markup for each location of the item found from the current data.
		output:	DOM object with information about this particular copy/location of the item found
	*/
	var locationDetails = function () {

		/*	detailInfoItemWithLabel
			input:	fieldContent - DOM object with content to display in the field
					labelName - string displayed as the label
					dontTerminate - boolean:	false puts a ; after the text
												true puts nothing after the text
		*/
		var detailInfoItemWithLabel = function (fieldContent, labelName, dontTerminate) {
			var infoSpan;
			if ( fieldContent !== undefined ) {
				infoSpan = document.createElement('span');
				infoSpan.setAttribute('class', 'pz2-info');
				if ( labelName !== undefined ) {
					var infoLabel = document.createElement('span');
					infoSpan.appendChild(infoLabel);
					infoLabel.setAttribute('class', 'pz2-label');
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
			Create markup for URLs in current data.
			output:	DOM element containing URLs as links.
		*/
		var electronicURLs = function() {
			var electronicURLs = location['md-electronic-url'];
			var URLsContainer;

			if (electronicURLs && electronicURLs.length != 0) {
				URLsContainer = document.createElement('span');

				for (var URLNumber in electronicURLs) {
					var URLInfo = electronicURLs[URLNumber];
					var linkText = '[' + localise('Link') + ']';
					var linkURL = URLInfo;
	
					if (typeof(URLInfo) === 'object' && URLInfo['#text'] !== undefined) {
						// URLInfo is not just an URL but an array also containing the link name
						if (URLInfo['@name'] !== undefined) {
							linkText = '[' + URLInfo['@name'] + ']';
						}
						linkURL = URLInfo['#text'];
					}

					// make sure the same URL isn't listed as a DOI already
					var URLAlreadyListed = false;
					for (var DOINumber in data['md-doi']) {
						URLAlreadyListed |= (linkURL.search(data['md-doi'][DOINumber]) != -1);
					}

					if (!URLAlreadyListed) {
						if (URLsContainer.childElementCount > 0) {
							// add , as separator if not the first element
							URLsContainer.appendChild(document.createTextNode(', '));
						}
						var link = document.createElement('a');
						URLsContainer.appendChild(link);
						link.setAttribute('href', linkURL);
						link.setAttribute('target', 'pz2-linktarget');
						link.innerHTML = linkText;
					}
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
			var targetURL = location['@id'];
			var targetName = location['@name'];
			var PPN = location['md-id'][0].replace(/[a-zA-Z]*([0-9]*)/, '$1');

			var catalogueURL;			
			if (targetURL.search(/gso.gbv.de\/sru/) != -1) {
				catalogueURL = targetURL.replace(/(gso.gbv.de\/sru\/)(DB=[\.0-9]*)/,
										'http://gso.gbv.de/$2/PPNSET?PPN=' + PPN);
			}
			else if (targetURL.search(/z3950.gbv.de:20012\/subgoe_opc/) != -1) {
				catalogueURL = 'http://gso.gbv.de/DB=2.1/PPNSET?PPN=' + PPN;
			}
			else if (targetURL.search('134.76.176.48:2020/jfm') != -1) {
				catalogueURL = 'http://www.emis.de/cgi-bin/jfmen/MATH/JFM/quick.html?first=1&maxdocs=1&type=html&format=complete&an=' + PPN;
			}
			else if (targetURL.search('134.76.176.48:2021/arxiv') != -1) {
				if (location['md-electronic-url']) {
					catalogueURL = location['md-electronic-url'][0];
				}
			}

			if (catalogueURL) {
				var linkElement = document.createElement('a');
				linkElement.setAttribute('href', catalogueURL);
				linkElement.setAttribute('target', 'pz2-linktarget');
				linkElement.setAttribute('class', 'pz2-detail-catalogueLink')
				var linkText = localise('Ansehen und Ausleihen bei:');
				if (targetName) {
					linkText += ' ' + targetName;
				}
				linkElement.appendChild(document.createTextNode(linkText));
			}

			return linkElement;
		}



		var locationDetails = [];

		for ( var locationNumber in data.location ) {
			var localInfoItems = []

			var location = data.location[locationNumber];
			var localURL = location['@id'];
			var localName = location['@name'];

			var detailsHeading = document.createElement('dt');
			locationDetails.push(detailsHeading);
			detailsHeading.appendChild(document.createTextNode(localise('Ausgabe')+':'));

			var detailsData = document.createElement('dd');
			locationDetails.push(detailsData);

			appendInfoToContainer( detailInfoItem('edition'), detailsData );
			appendInfoToContainer( detailInfoItem('publication-name'), detailsData );
			appendInfoToContainer( detailInfoItem('publication-place'), detailsData );
			appendInfoToContainer( detailInfoItem('date'), detailsData );
			appendInfoToContainer( detailInfoItem('physical-extent'), detailsData );
			cleanISBNs();
			appendInfoToContainer( detailInfoItem('isbn'), detailsData );
			appendInfoToContainer( electronicURLs(), detailsData);
			appendInfoToContainer( catalogueLink(), detailsData);
		}

		return locationDetails;
	}

	
	var data = hitList[recordID];

	if (data) {
		var detailsDiv = document.createElement('div');
		detailsDiv.setAttribute('class', 'pz2-details');
		detailsDiv.setAttribute('id', 'det_' + HTMLIDForRecordData(data));

		var detailsTable = document.createElement('dl');
		detailsDiv.appendChild(detailsTable);

		appendInfoToContainer( detailLineAuto('author'), detailsTable );
		appendInfoToContainer( detailLineAuto('other-person'), detailsTable )
		appendInfoToContainer( detailLineAuto('description'), detailsTable );
	 	appendInfoToContainer( detailLineAuto('medium'), detailsTable );
		appendInfoToContainer( detailLineAuto('series-title'), detailsTable );
		appendInfoToContainer( detailLineAuto('issn'), detailsTable );
		appendInfoToContainer( detailLineAuto('pissn'), detailsTable );
		appendInfoToContainer( detailLineAuto('eissn'), detailsTable );
		appendInfoToContainer( detailLineAuto('doi'), detailsTable );
		appendInfoToContainer( locationDetails(), detailsTable );
		appendInfoToContainer( extraLinks(), detailsTable );
		if ( useZDB == true ) {
			addZDBInfoIntoElement( detailsTable );
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
	if (recordData.recid[0] !== undefined) {
		return recordData.recid[0].replace(/ /g,'-pd-');
	}
}



/*	recordIDForHTMLID
	input:	record ID in HTML compatible form
	output:	input with dashes replaced by spaces
*/
function recordIDForHTMLID (HTMLID) {
	return HTMLID.replace(/-pd-/g,' ');
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
		'music-score': 'Noten',
		'other': 'Andere',
		'recording': 'Aufnahme',
		'website': 'Website',
	},
	
	'en': {
		'article': 'Article',
		'audio-visual': 'Video',
		'book': 'Book',
		'electronic': 'Computer File',
		'journal': 'Journal',
		'map': 'Map',
		'microform': 'Microform',
		'music-score': 'Music score',
		'other': 'Other',
		'recording': 'Recording',
		'website': 'Website',
	}
};




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
		'gez': 'Altäthiopisch',
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
		'ind': 'Indonesisch (=Bahasa Indonesia=Bhasa Indonesia)',
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
		'gre': 'Neugriechisch (1453-)',
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
		'wel': 'Walisisch (Kymrisch)',
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
		'gre': 'Greek, Modern (1453- )',
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

//EOF
 
