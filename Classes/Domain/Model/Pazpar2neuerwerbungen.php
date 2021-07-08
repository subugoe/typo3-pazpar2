<?php

namespace Subugoe\Pazpar2\Domain\Model;

/*******************************************************************************
 * Copyright notice
 *
 * Copyright (C) 2013 by SUB Göttingen
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 ******************************************************************************/

/*
 * Pazpar2neuerwerbungen model class.
 */
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

/**
 * pazpar2 Neuerwerbungen model object.
 */
class Pazpar2neuerwerbungen extends AbstractEntity
{
    /**
     * PPN (i.e. an ID) of the root element used for the Neuerwerbungen subject list.
     * A record with this value in the »ppn« field should be in the tx_nkwgok_data table.
     *
     * @var string
     */
    protected $rootPPN;

    /**
     * Stores the request’s arguments needed to determine the parameters submitted by the user.
     *
     * @var array
     */
    protected $requestArguments;
    /**
     * Array with subject tree.
     *
     * @var array
     */
    protected $subjects;
    /**
     * Number of months to display, defaults to 13.
     *
     * @var int
     */
    protected $monthCount;
    /**
     * Array used for month selection menu.
     * Array elements are associative arrays with two elements:
     *    * name - the localised string used for display, e.g. Oktober 2010
     *    * searchTerms - string used to query the catalogue’s NEL field, e.g. 201010.
     *        In case several months are used, the different strings are comma separated.
     *
     * @var array
     */
    protected $months;

    /**
     * @return string
     */
    public function getRootPPN()
    {
        return $this->rootPPN;
    }

    /**
     * @param string $newRootPPN
     */
    public function setRootPPN($newRootPPN)
    {
        $this->rootPPN = $newRootPPN;
    }

    /**
     * Value of the selected item in the month selection menu:
     * The key of the second item in the $months array.
     *
     * @return string|null value of default month or null
     */
    public function getDefaultMonth()
    {
        $result = null;

        $keys = array_keys($this->getMonths());
        if (count($keys) >= 2) {
            $result = (string) $keys[1];
        }

        return $result;
    }

    /**
     * @return array
     */
    public function getMonths()
    {
        if (null == $this->months) {
            $this->months = $this->monthsArray();
        }

        return $this->months;
    }

    /**
     * Returns array of months preceding the current one.
     *    * Keys are of the form YYYY-MM.
     *    * Values are localised names of the months followed by the year.
     *        Localised »(incomplete)« is appended to the name of the current month.
     *
     * @return array
     */
    public function monthsArray()
    {
        $months = [];
        $year = date('Y');
        $month = date('n');

        for ($i = 1; $i <= $this->getMonthCount(); ++$i) {
            $searchString = $this->picaSearchStringForMonth($month, $year);

            /* make sure the text encoding in the locale_all setting matches the encoding
                    of the page, otherwise umlauts in month names may appear broken */
            $monthName = strftime('%B', mktime(0, 0, 0, $month, 1, 2010));
            $displayString = $monthName.' '.$year;

            if (1 == $i) {
                $displayString .= ' ('.LocalizationUtility::translate('unvollständig', 'pazpar2').')';
            }

            $months[$searchString] = $displayString;

            $this->reduceMonth($month, $year);
        }

        return $months;
    }

    /**
     * @return int
     */
    public function getMonthCount()
    {
        if (null === $this->monthCount) {
            return 13;
        }

        return $this->monthCount;
    }

    /**
     * @param int $newMonthCount
     */
    public function setMonthCount($newMonthCount)
    {
        $this->monthCount = $newMonthCount;
    }

    /**
     * Return search string for Pica format of the given month: YYYYMM.
     *
     * @param $month int month number
     * @param $year int year number
     *
     * @return string the given month in YYYYMM format
     */
    private function picaSearchStringForMonth($month, $year)
    {
        $leadingZero = '';

        if ($month < 10) {
            $leadingZero = '0';
        }

        return $year.' '.$leadingZero.$month;
    }

    /**
     * reduceMonth: Assume the passed references to month and year numbers
     *    indicate a month; reduce them to indicate the previous month.
     *
     * @param $month int reference to month number
     * @param $year int reference to year number
     */
    private function reduceMonth(&$month, &$year)
    {
        if (1 == $month) {
            $month = 12;
            --$year;
        } else {
            --$month;
        }
    }

    /**
     * Return URL for the Atom feed of the current query.
     *
     * @return string
     */
    public function getAtomURL()
    {
        $atomURL = null;

        $searchQuery = $this->searchQueryWithEqualsAndWildcard(' ', '*', true);
        if ($searchQuery) {
            $searchQuery = urlencode($searchQuery);

            $atomBaseURL = GeneralUtility::getIndpEnv('TYPO3_SITE_URL').'opac.atom?q=';
            $atomURL = $atomBaseURL.$searchQuery;
        }

        return $atomURL;
    }

    /**
     * Builds a query string using the queries of the selected checkboxes in the form.
     * The strings used for equals assignment and wildcard can be configured
     *  to yield string that can be used for both Pica- and CCL-style queries.
     * Null is returned when no search queries are selected.
     *
     * @param string $equals             [defaults to '=']
     * @param string $wildcard           [defaults to '']
     * @param bool   $ignoreSelectedDate [defaults to False]
     *
     * @return string
     */
    public function searchQueryWithEqualsAndWildcard($equals = '=', $wildcard = '', $ignoreSelectedDate = false)
    {
        $queryString = null;

        $queries = $this->selectedQueriesInFormWithWildcard($wildcard);
        if (count($queries) > 0) {
            $queryString = $this->oredSearchQueries($queries, '', '');
            $queryString = str_replace('=', $equals, $queryString);

            if (!$ignoreSelectedDate) {
                $dates = $this->selectedMonthInFormWithWildcard($wildcard);
                $NELQueryString = $this->oredSearchQueries($dates, 'nel', $equals);
                $queryString .= ' and '.$NELQueryString;
            }
        }

        return $queryString;
    }

    /**
     * Return the array of all queries selected in the form, taking into account
     *  group checkboxes.
     *
     * @param string $wildcard replace a final (?) in each term with this string
     *
     * @return array of query strings
     */
    private function selectedQueriesInFormWithWildcard($wildcard)
    {
        return $this->selectedQueriesInGroupWithWildcard($this->getSubjects(), $wildcard);
    }

    /**
     * Return the array of all queries selected in a subject group, taking into account
     *  group checkboxes.
     *
     * @param array  $subjects
     * @param string $wildcard replace a final (?) in each term with this string
     *
     * @return array of query strings
     */
    private function selectedQueriesInGroupWithWildcard($subjects, $wildcard)
    {
        $queries = [];

        foreach ($subjects as $subject) {
            if ($subject['selected'] && $subject['queries']) {
                $this->addSearchTermsToList($subject['queries'], $queries, $wildcard);
            } elseif ($subject['subjects']) {
                $subsubjects = $this->selectedQueriesInGroupWithWildcard($subject['subjects'], $wildcard);
                $queries = array_merge($queries, $subsubjects);
            }
        }

        return $queries;
    }

    /**
     * Helper function adding the elements of an array to a given array,
     *  potentially appending a wildcard to each of them in the process.
     *
     * @param array  $searchTerms strings to be added to the list
     * @param array  $list        the terms are added to
     * @param string $wildcard    replace a final (?) in each term with this string
     */
    private function addSearchTermsToList($searchTerms, &$list, $wildcard)
    {
        foreach ($searchTerms as $term) {
            if ('' != $term) {
                if ($wildcard && '?' === substr($term, -1)) {
                    $term = substr($term, 0, -1).$wildcard;
                }
                $list[] = $term;
            }
        }
    }

    /**
     * @return array
     */
    public function getSubjects()
    {
        if (null == $this->subjects) {
            $this->setupSubjects();
        }

        return $this->subjects;
    }

    /**
     * Get the subjects array, add information about the selected ones
     *  from our arguments to it, and store it.
     *
     * If all child elements in a subject group are selected, also select the
     *    group itself.
     *
     * If a subject group is selected, also select all the subjects contained in
     *  it. (If a subject group is not selected, do _not_ deselect all the
     *  children. Imperfect but probably the most reasonable thing to be done in
     *  a non-interactive setup like this one.)
     */
    protected function setupSubjects()
    {
        $subjects = $this->makeSubjectsArrayForPPN($this->rootPPN);

        // Figure out selected subjects from the request’s arguments. Subject argument names
        // are "pz2subject-x0-x1-x2-..." where xi is the index of the subject group
        // at level i.
        if ($this->requestArguments['button']) {
            $selectedCheckboxes = [];
            // Our form was submitted: use form values only.
            foreach ($this->requestArguments as $argumentName => $argument) {
                $fieldNameStart = 'pz2subject-';
                if (('' != $argument) && (0 === strpos($argumentName, $fieldNameStart))) {
                    $nameParts = explode('-', substr($argumentName, strlen($fieldNameStart)));
                    if (count($nameParts) > 0) {
                        $mySubjects = &$subjects;
                        while (count($nameParts) > 1) {
                            $subjectIndex = intval(array_shift($nameParts));
                            $mySubjects = &$mySubjects[$subjectIndex]['subjects'];
                        }

                        $subjectIndex = intval(array_shift($nameParts));
                        $subject = &$mySubjects[$subjectIndex];
                        $subject['selected'] = true;
                        $selectedCheckboxes[] = implode(',', $subject['queries']);
                    }
                }
            }

            // Also write the selected values to our cookie.
            $cookieString = implode(':', $selectedCheckboxes);
            setcookie('pz2neuerwerbungen-previousQuery', $cookieString);
        } else {
            // Our form was not submitted: use cookie to set the selected checkboxes.
            $previousQuery = $_COOKIE['pz2neuerwerbungen-previousQuery'];
            $queryItems = explode(':', $previousQuery);

            // Turn on the selection for each group if its queries have been
            // passed in the cookie xor for each included subject if its queries
            // have been passed in the cookie.
            foreach ($subjects as &$group) {
                $queriesString = implode(',', $group['queries']);
                if (in_array($queriesString, $queryItems)) {
                    $group['selected'] = true;
                } else {
                    foreach ($group['subjects'] as &$subject) {
                        $queriesString = implode(',', $subject['queries']);
                        if (in_array($queriesString, $queryItems)) {
                            $subject['selected'] = true;
                        }
                    }
                }
            }
        }

        // Turn on the checkbox if there only is a single one.
        if (1 === count($subjects) && count($subjects[0]['subjects'] === 1)) {
            $subjects[0]['subjects'][0]['selected'] = true;
        }

        // Turn on the selection for the group if all containing subjects are selected.
        foreach ($subjects as &$group) {
            $this->turnOnGroupSelectionIfNeeded($group);
        }

        // Turn on the selection for all included subjects if the containing group is selected.
        foreach ($subjects as &$group) {
            $this->turnOnChildSelectionIfNeeded($group);
        }

        $this->subjects = $subjects;
    }

    /**
     * Return array of subjects for the parentPPN passed.
     * The data needed are loaded from the tx_nkwgok_data table of the database.
     * They are expected to be imported from CSV-data by the nkwgok extension.
     *    See its documentation or code for the fields required in teh CSV-file.
     *
     * The information is converted to nested arrays as required by the »neuerwerbungen-form«
     * Partial that handles the display. The data format is:
     *    * Array [subject groups]
     *        * Array [subject group, associative]
     *            * id => string - id/ppn of subject group [required]
     *            * name => string - name of subject group [required]
     *            * queries => Array [optional]
     *                * string that is a CCL query
     *            * subjects => Array [required, subjects]
     *                * Array [subject, associative]
     *                    * name => string - name of subject group [required]
     *                    * queries => Array [required]
     *                        * string that is a CCL query
     *                    * inline => boolean - displayed in one line with other items?
     *                        [optional, defaults to false]
     *                    * break => boolean - insert <br> before current element?
     *                        [optional, should only be used with inline => true, defaults to false]
     *
     * If the »queries« field of a subject group is not specified, create it by
     *    taking the union of the »queries« arrays of all its »subjects«.
     *
     * @param string $parentPPN
     *
     * @return array subjects to be displayed
     */
    protected function makeSubjectsArrayForPPN($parentPPN)
    {
        $rootNodes = $this->queryForChildrenOf($parentPPN);
        $subjects = [];

        foreach ($rootNodes as $nodeRecord) {
            $subject = [];

            // Add PPN for separating distinct subject fieldsets
            $subject['id'] = strtolower($nodeRecord['ppn']);

            // Use English subject name if it exists and the language is English
            // and the German subject name otherwise.
            if ('en' == $GLOBALS['TSFE']->lang && $nodeRecord['descr_en']) {
                $subject['name'] = $nodeRecord['descr_en'];
            } else {
                $subject['name'] = $nodeRecord['descr'];
            }

            // Recursively add child elements if they exist.
            if ($nodeRecord['childcount'] > 0) {
                $subject['subjects'] = $this->makeSubjectsArrayForPPN($nodeRecord['ppn']);
            }

            // Extract each search term from the 'search' field and add an array with all of them.
            if ('' != $nodeRecord['search']) {
                $searchComponents = [];
                foreach (explode(' or ', urldecode($nodeRecord['search'])) as $searchComponent) {
                    $component = trim($searchComponent, ' *');
                    $component = preg_replace('/^LKL /', '', $component);
                    $searchComponents[] = trim($component);
                }
                $subject['queries'] = $searchComponents;
            } else {
                $subqueries = [];
                foreach ($subject['subjects'] as $subsubject) {
                    foreach ($subsubject['queries'] as $subquery) {
                        $subqueries[] = $subquery;
                    }
                }
                $subject['queries'] = $subqueries;
            }

            // Add tag fields to subject (inline and break).
            foreach (explode(',', $nodeRecord['tags']) as $tag) {
                if ('' != $tag) {
                    $subject[$tag] = true;
                }
            }

            $subjects[] = $subject;
        }

        return $subjects;
    }

    /**
     * Queries the database for all records having the $parentPPN parameter as their parent element
     *  and returns the query result.
     *
     * Uses table tx_nkwgok_data from the GOK extension.
     *
     * @param string $parentPPN
     *
     * @return array
     */
    protected function queryForChildrenOf(string $parentPPN)
    {
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('tx_nkwgok_data');

        return $queryBuilder
            ->select('*')
            ->from('tx_nkwgok_data')
            ->where($queryBuilder->expr()->eq('parent', '?'))
            ->setParameter(0, $parentPPN)
            ->andWhere($queryBuilder->expr()->eq('statusID', 0))
            ->orderBy('notation', 'ASC')
            ->execute()
            ->fetchAll();
    }

    /**
     * Takes the passed $group array and sets its »selected« field to True if the »selected« fields
     * of all the objects in its »subjects« array are set to True. Uses recursion to check
     * potentially existant nested groups.
     *
     * @param array $group (passed by reference)
     */
    protected function turnOnGroupSelectionIfNeeded(&$group)
    {
        $isSelected = true;
        foreach ($group['subjects'] as &$subject) {
            if ($subject['subjects']) {
                $this->turnOnGroupSelectionIfNeeded($subject);
            }
            $isSelected &= $subject['selected'];
        }

        if ($isSelected) {
            $group['selected'] = true;
        }
    }

    /**
     * Checks whether the »selected« field of the passed $group array is true and recursively sets
     * the »selected« fields of all contained subjects in the »subject« element to True if that is
     * the case.
     *
     * @param array $group (passed by reference)
     */
    protected function turnOnChildSelectionIfNeeded(&$group)
    {
        if (true == $group['selected']) {
            foreach ($group['subjects'] as &$subject) {
                $subject['selected'] = true;
                if ($subject['subjects']) {
                    $this->turnOnChildSelectionIfNeeded($subject);
                }
            }
        }
    }

    /**
     * Helper function for preparing search queries.
     *
     * @param array  $queryTerms strings, each of which will be a sub-query
     * @param string $key        search key the query is made for
     * @param string $equals     string used to separate the key and the query term
     *
     * @return string query
     */
    private function oredSearchQueries($queryTerms, $key, $equals)
    {
        $query = '('.$key.$equals.implode(' or '.$key.$equals, $queryTerms).')';

        return $query;
    }

    /**
     * Return an array containing the selected month(s). If no month is selected
     *  use the previous month.
     *
     * @param string $wildcard
     *
     * @return array
     */
    private function selectedMonthInFormWithWildcard($wildcard)
    {
        $arguments = $this->getRequestArguments();
        $months = explode(',', $arguments['months']);
        // If there is no selection, use the previous month (i.e. the item at
        // index 0 of the monthsArray() result).
        if ('' == $months[0]) {
            $monthKeysArray = array_keys($this->monthsArray());
            $months = [$monthKeysArray[1]];
        }

        $dates = [];
        $this->addSearchTermsToList($months, $dates, $wildcard);

        return $dates;
    }

    /**
     * @return array
     */
    public function getRequestArguments()
    {
        return $this->requestArguments;
    }

    /**
     * @param array $newRequestArguments
     */
    public function setRequestArguments($newRequestArguments)
    {
        $this->requestArguments = $newRequestArguments;
    }
}
