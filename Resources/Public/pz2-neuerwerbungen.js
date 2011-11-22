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
	jQuery('.pz2-neuerwerbungenForm input[type="submit"]').hide();
	// Overwrite pz-client.js search trigger function with our own.
	triggerSearchFunction = neuerwerbungenRunSearchForForm;
	triggerSearchFunction();
	restoreCookieState ();
}



/*
 * restoreCookieState
 *
 * Restore previously checked checkboxes from the stated stored in the
 *	'pz2neuerwerbungen-previousQuery' cookie.
 *
 * Cookie data is a string whose components are separated by colons (:).
 * Each item corresponds to the value of a checkbox. Select those checkboxes and
 * turn them on.
 *
 */
function restoreCookieState () {
	var cookieInfo = getPz2NeuerwerbungenCookie();
	for (var fieldName in cookieInfo) {
		jQuery('.pz2-neuerwerbungenForm :checkbox[value="' + fieldName + '"]').attr({'checked': true});
	}
}



/**
 * getPz2NeuerwerbungenCookie
 * 
 * Returns the content of our cookie as an object with a property for each
 * checkbox that should be active.
 * 
 * As the same cookie is used across all pages, it may contain information about
 * checkboxes that are not currently present.
 * 
 * output:	object with a property for each active search query
 */
function getPz2NeuerwerbungenCookie () {
	var cookieInfo = {};
	
	var cookies = document.cookie.split('; ');
	for (var cookieIndex in cookies) {
		var cookie = cookies[cookieIndex];
		var equalsLocation = cookie.search('=');
		if (equalsLocation != -1) {
			var cookieName = cookie.substring(0, equalsLocation);
			if (cookieName == 'pz2neuerwerbungen-previousQuery') {
				var cookieValue = cookie.substring(equalsLocation +1);
				var fieldNames = cookieValue.split(':');
				for (var fieldNameIndex in fieldNames) {
					var fieldName = fieldNames[fieldNameIndex];
					cookieInfo[fieldName] = true;
				}
				break;
			}
		}
	}
	
	return cookieInfo;
}



/**
 * saveFormStateInCookie
 *
 * Get the checked checkboxes from the passed form and concatenate their values
 * with colon (:) separators. Store the result in the
 * 'pz2neuerwerbungen-previousQuery' cookie.
 *
 * input:	form - DOM form element in which to look for checked checkboxes
 */
function saveFormStateInCookie (form) {
	var cookieInfo = getPz2NeuerwerbungenCookie();
	var formStatus = searchFormStatus();
	
	for (var statusItem in formStatus) {
		if (formStatus[statusItem]) {
			// This search query is active, add it to the cookie.
			cookieInfo[statusItem] = true;
		}
		else {
			// This search query is inactive, remove it from the cookie.
			delete cookieInfo[statusItem];
		}
	}
	
	var cookieTerms = [];
	for (var cookieItem in cookieInfo) {
		cookieTerms.push(cookieItem);
	}
	var termsString = 'pz2neuerwerbungen-previousQuery=' + cookieTerms.join(':') + '; '
	var expires = new Date((new Date).getTime() + 1000*60*60*24*365);
	var expiresString = 'expires=' + expires.toGMTString() + '; ';
	var pathString = 'path=/;'
	document.cookie = termsString + expiresString + pathString;
}



/*
 * neuerwerbungenRunSearchForForm
 *
 * Build search query from the selected checkboxes. If it is non-empty,use it
 *	to kick off pazpar2 and set the Atom subscription URL.
 *
 * input:	form - DOM form element in which to look for checked checkboxes
 */
function neuerwerbungenRunSearchForForm (form) {
	/* Only start the query if pazpar2 is initialised. Otherwise this function
		will be called by on_myinit in pz2-client.js once initialisation has finished.
	*/
	if (domReadyFired && pz2Initialised) {
		var myForm = form;
		// If no form is passed use the first .pz2-neuerwerbungenForm.
		if (myForm === undefined) {
			var mainForms = jQuery('.pz2-neuerwerbungenForm form');
			if (mainForms.length > 0) {
				myForm = mainForms[0];
			}
		}

		setSortCriteriaFromString('date-d--author-a--title-a');
		var jAtomLink = jQuery('.pz2-atomLink', form);
		var linkElement = document.getElementById('pz2neuerwerbungen-atom-linkElement');

		var query = searchQueryWithEqualsAndWildcard(form, '=', '');
		if (query) {
			query = query.replace('*', '?');
			my_paz.search(query, 2000, null, null);

			// Only manipulate Atom link if it is present already.
			if (jAtomLink.length > 0) {
				var myAtomURL = atomURL(form);
				// Update clickable link to Atom feed.
				jAtomLink.attr('href', myAtomURL);

				// Add Atom <link> element if it is not present, then set the link.
				if (!linkElement) {
					linkElement = document.createElement('link');
					linkElement.setAttribute('id', 'pz2neuerwerbungen-atom-linkElement');
					linkElement.setAttribute('rel', 'alternate');
					linkElement.setAttribute('type', 'application/atom+xml');
					document.head.appendChild(linkElement);
				}
				linkElement.setAttribute('href', myAtomURL);
			}

			trackPiwik('search', query);
		}
		else {
			// There is no query: Remove the clickable Atom link and the Atom <link> element.
			jAtomLink.removeAttr('href');
			if (linkElement) {
				linkElement.parentNode.removeChild(linkElement);
			}

			/* Manually set my_paz’ currQuery to the empty string. We can’t pass the empty
				to my_paz.search because it throws an error. */
			my_paz.currQuery = undefined;
		}

		resetPage();
	}
}



/*
 * checkboxChanged
 *
 * Called by lowest-level checkboxes’ onclick handler.
 * Toggles parent checkbox if necessary, then starts the query.
 *
 * input:	checkbox - DOM element of the clicked checkbox
 */
function checkboxChanged (checkbox) {
	toggleParentCheckboxOf(checkbox);
	saveFormStateInCookie(checkbox.form);
	neuerwerbungenRunSearchForForm(checkbox.form);
}



/*
 * groupCheckboxChanged
 *
 * Called by top-level checkboxes’ onclick handler.
 * Toggles the child checboxes, then starts the query.
 *
 * input:	checkbox - DOM element of the clicked checkbox
 */
function groupCheckboxChanged (checkbox) {
	toggleChildCheckboxesOf(checkbox);
	saveFormStateInCookie(checkbox.form);
	neuerwerbungenRunSearchForForm(checkbox.form);
}



/*
 * monthChanged
 *
 * Called by the month select’s onchange handler.
 * Starts a new search with the selected month.
 *
 * input:	select - DOM Element of the month select
 *
 */
function monthChanged (select) {
	neuerwerbungenRunSearchForForm(select.form);
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



/**
 * selectedQueriesInFormWithWildcard
 *
 * For a given form returns an array of all GOKs in the values of the active
 * checkboxes. Each checkbox’ value can contain several GOKs, separated by a
 * comma (,). A wildcard is appended to each GOK if required.
 *
 * inputs:	form - DOM element of the form to get the data from
 *			wildcard - string to be appended to each extracted GOK
 * output:	array of strings, each of which is a GOK, potentially with a wildcard
 */
function selectedQueriesInFormWithWildcard (form, wildcard) {
	var GOKs = [];
	var formStatus = searchFormStatus();

	for (var searchTerms in formStatus) {
		if (formStatus[searchTerms]) {
			addSearchTermsToList(searchTerms.split(','), GOKs, wildcard);
		}
	}

	return GOKs;
}



/**
 * searchFormStatus
 * 
 * Returns an object with a boolean property for each search term in the given
 * form. The property’s value reflects whether the search term should be used.
 * This value is not necessarily the checked status of the checkbox as
 * the search term of checkboxes inside a fieldset may be unused in case the
 * fieldset’s super-checkbox is active and provides a more general search term
 * covering those of all its sub-checkboxes.
 * 
 * input:	form - DOM element of the form to get the data from
 * output:	object - a boolean property for each search term indicating whether it is used
 */
function searchFormStatus (form) {
	var status = {};

	// Loop over fieldsets and quasi-fieldsets.
	jQuery('fieldset, .pz2-fieldset-replacement', form).each( function (index) {
			var legendCheckbox = jQuery('legend :checkbox', this);
			
			if (legendCheckbox.length > 0
					&& legendCheckbox[0].checked
					&& legendCheckbox[0].value !== 'CHILDREN') {
				// This is a checked super-checkbox in a fieldset which has a custom search term:
				// 1. Mark its search term as active.
				status[legendCheckbox[0].value] = true;
				// 2. Mark the search term of all child-checkboxes as inactive.
				jQuery('ul :checkbox', this).each( function (index) {
						status[this.value] = false;
					}
				);
			}
			else {
				// This is an unchecked fieldset:
				// 1. Mark the fieldset’s search term as inactive.
				if (legendCheckbox.length > 0
						&& !legendCheckbox[0].checked
						&& legendCheckbox[0].value !== 'CHILDREN') {
					status[legendCheckbox[0].value] = false;
				}
				// 2. Mark the search terms of child-checkboxes according to their status.
				jQuery('ul :checkbox', this).each( function (index) {
						status[this.value] = this.checked;
					}
				);
			}
		}
	);
	
	return status;
}



/*
 * searchQueryWithEqualsAndWildcard
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
 *			ignoreSelectedDate - boolean indicating whether the date is to be
 *				included in the search query [optional, defaults to false]
 * output:	string containing the complete query / undefined if no GOKs are found
 */
function searchQueryWithEqualsAndWildcard (form, equals, wildcard, ignoreSelectedDate) {
	var queries = selectedQueriesInFormWithWildcard(form, wildcard);

	if (queries.length > 0) {
		var queryString = oredSearchQueries(queries, '', '');
		queryString = queryString.replace(/=/g, equals);
		if (!ignoreSelectedDate) {
			var dates = [];
			var searchTerms = jQuery('.pz2-months :selected', form)[0].value.split(',');
			addSearchTermsToList(searchTerms, dates, wildcard);
			var	NELQueryString = oredSearchQueries(dates, 'nel', equals);
			queryString += ' and ' + NELQueryString;
		}
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
	var searchQuery = searchQueryWithEqualsAndWildcard(form, ' ', '*', true);
	var atomURL = '';

	if (searchQuery) {
		searchQuery = searchQuery.replace(/ /g, '+');
		searchQuery = encodeURI(searchQuery);

		var atomBaseURL = document.baseURI + 'opac.atom?q=';
		atomURL = atomBaseURL + searchQuery;
	}

	return atomURL;
}
 