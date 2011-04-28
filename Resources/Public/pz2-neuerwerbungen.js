/*
 * pz2-neuerwerbungen.js
 *
 * 2010-2011 by Sven-S. Porst, SUB Göttingen
 * porst@sub.uni-goettingen.de
 *
 * JavaScript for interactive loading and display of new acquisitions by
 * the library.
 *
 * For use with the pazpar2neuerwerbungen Typo3 extension.
 *
 */



/*
 * pz2neuerwerbungenDOMReady
 *
 * To be called when the Document is ready (usually by jQuery).
 * Hides the submit button as it's not needed when we're using JavaScript.
 * Restores state from cookies and kicks off the search.
 */
function pz2neuerwerbungenDOMReady () {
	jQuery('.pz2-searchForm input[type="submit"]').hide();
	restoreCookieState ();
	runSearchForForm (jQuery('.pz2-searchForm')[0]);
}



/*
 * restoreCookieState
 *
 * Restore previously checked checkboxes from the stated stored in the
 *	'previousQuery' cookie.
 *
 * Cookie data is a string whose components are separated by colons (:).
 * Each item corresponds to the value of a checkbox. Select those checkboxes and
 * turn them on.
 *
 */
function restoreCookieState () {
	var cookies = document.cookie.split('; ');
	for (var cookieIndex in cookies) {
		var cookie = cookies[cookieIndex];
		var equalsLocation = cookie.search('=');
		if (equalsLocation != -1) {
			var cookieName = cookie.substring(0, equalsLocation);
			if (cookieName == 'previousQuery') {
				var cookieValue = cookie.substring(equalsLocation +1);
				var fieldNames = cookieValue.split(':');
				for (var fieldNameIndex in fieldNames) {
					var fieldName = fieldNames[fieldNameIndex];
					jQuery('.pz2-searchForm :checkbox[value="' + fieldName + '"]').attr({'checked': true});
				}
				break;
			}
		}
		var cookieParts = cookies[cookieIndex].split
	}
}



/*
 * saveFormStateAsCookie
 *
 * Get the checked checkboxes from the passed form and concatenate their values
 *	with colon (:) separators. Store the result in the 'previousQuery' cookie.
 *
 * input:	form - DOM form element in which to look for checked checkboxes
 */
function saveFormStateAsCookie (form) {
	var selectedValues = [];
	jQuery(':checked', form).each( function (index) {
			selectedValues.push(this.value);
		}
	)

	document.cookie = 'previousQuery=' +  selectedValues.join(':');
}



/*
 * runSearchForForm
 *
 * Build search query from the selected checkboxes. If it is non-empty,use it
 *	to kick off pazpar2 and set the Atom subscription URL.
 *
 * input:	form - DOM form element in which to look for checked checkboxes
 */
function runSearchForForm (form) {
	resetPage();
	setSortCriteriaFromString('author-a--title-a--date-d');
	var query = buildSearchQueryWithEqualsAndWildcard(form, '=', '');

	if (query) {
		my_paz.search(query, 2000, null, null);
		
		var myAtomURL = atomURL(form);
		jQuery('.pz2-atomLink').show().attr('href', myAtomURL);

		var linkElement = document.getElementById('pz2neuerwerbungen-atom-linkElement');
		if (!linkElement) {
			linkElement = document.createElement('link');
			linkElement.setAttribute('id', 'pz2neuerwerbungen-atom-linkElement');
			linkElement.setAttribute('rel', 'alternate');
			linkElement.setAttribute('type', 'application/atom+xml');
			document.head.appendChild(linkElement);
		}
		linkElement.setAttribute('href', myAtomURL);
	}
	else {
		jQuery('.pz2-atomLink').hide();
	}

	saveFormStateAsCookie(form);
}



/*
 * checkboxChanged
 *
 * Callback for the lowest-level checkboxes' onclick handler.
 * Toggles parent checkbox if necessary, then starts the query.
 *
 * input:	checkbox - DOM element of the clicked checkbox
 */
function checkboxChanged (checkbox) {
	toggleParentCheckboxOf(checkbox);
	runSearchForForm(checkbox.form);
}



/*
 * groupCheckboxChanged
 *
 * Callbock for the top-level checkboxes' onlick handler.
 * Toggles the child checboxes, then starts the query.
 *
 * input:	checkbox - DOM element of the clicked checkbox
 */
function groupCheckboxChanged (checkbox) {
	toggleChildCheckboxesOf(checkbox);
	runSearchForForm(checkbox.form);
}



/*
 * toggleParentCheckboxOf
 *
 * Helper function to update the parent checkbox' state when one of its child
 * checkboxes was changed:
 *	* all child checkboxes on => parent checkbox on
 *	* any child checkbox off => parent checkbox off
 *
 * input:	checkbox - DOM element of the changed checkbox
 */
function toggleParentCheckboxOf (checkbox) {
	var fieldset = jQuery(checkbox).parents('fieldset')[0];
	parentCheckbox = jQuery('legend :checkbox', fieldset);
	
	parentCheckbox.attr({'checked': (jQuery('li :checkbox', fieldset).length == jQuery('li :checked', fieldset).length)});
}



/*
 * toggleChildCheckboxesOf
 *
 * Helper function to update the child checkboxes' state when their parent
 * checkbox was changed:
 *	* parent checkbox on => all child checkboxes on
 *	* parent checkbox off => all child checkboxes off
 *
 * input:	checkbox - DOM element of the changed checkbox
 */
function toggleChildCheckboxesOf (checkbox) {
	var fieldset = jQuery(checkbox).parents('fieldset')[0];
	jQuery(':checkbox', fieldset).attr({'checked': checkbox.checked});
}



/*
 * selectedGOKsInFormWithWildcard
 *
 * For a given form returns an array of all GOKs in the values of the checked
 *	checkboxes. Each checkbox' value can contain several GOKs, separated by a
 *	comma (,). A wildcard is appended to each GOKs if required.
 *
 * inputs:	form - DOM element of the form to get the data from
 *			wildcard - string to be appended to each extracted GOK
 * output:	array of strings, each of which is a GOK, potentially with a wildcard
 */
function selectedGOKsInFormWithWildcard (form, wildcard) {
	var GOKs = [];

	jQuery('fieldset', form).each( function (index) {
			if ( jQuery('legend>label :checked', this)[0]
					&& jQuery('legend>label :checked', this)[0].value !== 'CHILDREN') {
				var searchTerms = jQuery('legend>label :checked', this)[0].value.split(',');
				addSearchTermsToList(searchTerms, GOKs, wildcard);
			}
			else {
				jQuery('ul :checked', this).each( function (index) {
						addSearchTermsToList(this.value.split(','), GOKs, wildcard);
					}
				);
			}
		}
	);

	return GOKs;
}



/*
 * buildSearchQueryWithEqualsAndWildcard
 *
 * Builds a query string using the selected GOKs and date in the passed form.
 * Equals assignment and wildcard can be configured to yield strings that can
 *	be used as Pica Opac queries or as CCL queries.
 * undefined is returned when there are no GOKs to search for.
 *
 * inputs:	form - DOM element of the form to get the data from
 *			equals - string used between the field name and the query term
 *				(typically ' ' in Pica or '=' in CCL)
 *			wildcard - string to be appended to each extracted GOK
 * output:	string containing the complete query / undefined if no GOKs are found
 */
function buildSearchQueryWithEqualsAndWildcard (form, equals, wildcard) {
	var GOKs = selectedGOKsInFormWithWildcard(form, wildcard);

	if (GOKs.length > 0) {
		var LKLQueryString = oredSearchQueries(GOKs, 'lkl', equals);

		var dates = [];
		var searchTerms = jQuery('.pz2-months :selected', form)[0].value.split(',');
		addSearchTermsToList(searchTerms, dates, wildcard);
		var	DTMQueryString = oredSearchQueries(dates, 'dtm', equals);
		
		var queryString = LKLQueryString + ' and ' + DTMQueryString // + ' not ' + SLKQueryString;
	}

	return queryString;
}



/*
 * oredSearchQueries
 *
 * Helper function for preparing search queries.
 *
 * inputs:	queryTerms - array of strings, each of which will be a sub-query
 *			key - string with search key that each queryTerm should be found in
 *			equals - string used to separate the key and the query term
 * output:	string containing the query
 */
function oredSearchQueries (queryTerms, key, equals) {
	var query = '(' + key + equals + queryTerms.join(' or ' + key + equals) + ')';
	
	return query;
}



/*
 * addSearchTermsToList
 *
 * Helper function adding the elements of an array to a given array,
 *	potentially appending a wildcard to each of them in the process.
 *
 * inputs:	searchTerms - array of strings which will be added to the list
 *			list - array that each component will be added to
 *			wildcard - string that is appended to each component before adding
 *				it to the list
 */
function addSearchTermsToList (searchTerms, list, wildcard) {
	for (var termIndex in searchTerms) {
		var term = searchTerms[termIndex];
		if (term && term !== '') {
			list.push(term + wildcard);
		}
	}
}



/*
 * atomURL
 *
 * Creates the URL to the Atom feed for the query if the form contains a 
 * selection.
 *
 * Assumes that the script / redirect providing the Atom feed is available at
 * ./opac.atom.
 *
 * input:	form - DOM element of the form to get the data from
 * output:	string with the URL to the Atom feed / undefined if nothing is selected
 */
function atomURL (form) {
	var searchQuery = buildSearchQueryWithEqualsAndWildcard(form, ' ', '*');

	if (searchQuery) {
		searchQuery = searchQuery.replace(/ /g, '+');
		searchQuery = encodeURI(searchQuery);

		var atomBaseURL = document.baseURI + 'opac.atom?q=';
		var atomURL = atomBaseURL + searchQuery;
	}

	return atomURL;
}
 