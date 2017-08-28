<?php
namespace Subugoe\Pazpar2\Controller;

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

/**
 * Provides the main controller for pazpar2 plug-in.
 */
use Subugoe\Pazpar2\Domain\Model\QueryPazpar2;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\VersionNumberUtility;
use TYPO3\CMS\Fluid\Core\ViewHelper\TagBuilder;

/**
 * pazpar2 controller for the pazpar2 extension.
 */
class Pazpar2Controller extends \TYPO3\CMS\Extbase\Mvc\Controller\ActionController
{

    /**
     * Query object handling the pazpar2 logic.
     * @var \Subugoe\Pazpar2\Domain\Model\Query
     */
    protected $query;

    /**
     * @var array
     */
    protected $conf;

    /**
     * Returns the path of the pazpar2 service on the server or NULL.
     *
     * @return String|NULL
     */
    protected function getPazpar2Path()
    {
        return $this->conf['pazpar2Path'];
    }

    /**
     * @return \Subugoe\Pazpar2\Domain\Model\Query
     */
    protected function createQuery()
    {
        /** @var QueryPazpar2 $query */
        $query = GeneralUtility::makeInstance(\Subugoe\Pazpar2\Domain\Model\QueryPazpar2::class);
        $query->setPazpar2Path($this->getPazpar2Path());
        $query->setServiceName($this->conf['serviceID']);
        return $query;
    }

    /**
     * Initialiser
     */
    public function initializeAction()
    {
        foreach ($this->settings as $key => $value) {
            // Transfer settings to conf
            $this->conf[$key] = $value;

            if (strpos($key, 'Path') !== false) {
                // Let TYPO3 try to process path settings as a path, so we can
                // use EXT: in the paths.
                $processedPath = $GLOBALS['TSFE']->tmpl->getFileName($value);
                if ($processedPath) {
                    $this->conf[$key] = $processedPath;
                }
            }
        }

        $this->query = $this->createQuery();
        $this->query->setQueryFromArguments($this->request->getArguments());
    }

    /**
     * Index:
     * 1. Insert pazpar2 CSS <link> and JavaScript <script>-tags into
     * the page’s <head> which are required to make the search work.
     * 2. Get parameters and run the query. Display results if there are any.
     */
    public function indexAction()
    {
        $this->addResourcesToHead();
        $arguments = $this->request->getArguments();
        $this->view->assign('extended', $arguments['extended']);
        $this->view->assign('query', $this->query);
        if (array_key_exists('useJS', $arguments) && $arguments['useJS'] !== 'yes') {
            $this->query->setSortOrder($this->determineSortCriteria($arguments));
            $this->query->run();
        }
        $this->view->assign('conf', $this->conf);
    }

    /**
     * Determine which sort criteria to use and return them as an array whose
     *    elements are arrays with two elements: 'fieldName' and 'direction'.
     * @param array $arguments
     * @return array
     */
    private function determineSortCriteria($arguments)
    {
        $sortCriteria = [];

        if (array_key_exists('sort', $arguments)) {
            // Sort order has been set by select on the page.
            $criteria = explode('--', $arguments['sort']);
            foreach ($criteria as $criterion) {
                $parts = explode('-', $criterion);
                if (count($parts) == 2) {
                    $sortCriteria[] = [
                            'fieldName' => $parts[0],
                            'direction' => ($parts[1] == 'd') ? 'descending' : 'ascending'
                    ];
                }
            }
        } else {
            // Use default sort order.
            $sortCriteria = $this->conf['sortOrder'];
        }

        return $sortCriteria;
    }

    /**
     * Helper: Inserts pazpar2 headers into page.
     */
    protected function addResourcesToHead()
    {
        // Add pazpar2.css to <head>.

        /** @var TagBuilder $cssTag */
        $cssTag = new TagBuilder('link');
        $cssTag->addAttribute('rel', 'stylesheet');
        $cssTag->addAttribute('type', 'text/css');
        $cssTag->addAttribute('href', $this->conf['CSSPath']);
        $cssTag->addAttribute('media', 'all');
        $this->response->addAdditionalHeaderData($cssTag->render());

        $this->addServiceConfigurationToHead();

        // Add pz2.js to <head>.
        // This is Indexdata’s JavaScript that ships with the pazpar2 software.

        /** @var TagBuilder $scriptTag */
        $scriptTag = new TagBuilder('script');
        $scriptTag->addAttribute('type', 'text/javascript');
        $scriptTag->addAttribute('src', $this->conf['pz2JSPath']);
        $scriptTag->forceClosingTag(true);
        $this->response->addAdditionalHeaderData($scriptTag->render());

        // Add pz2-client.js to <head>.
        /** @var TagBuilder $scriptTag */
        $scriptTag = new TagBuilder('script');
        $scriptTag->addAttribute('type', 'text/javascript');
        $scriptTag->addAttribute('src', $this->conf['pz2-clientJSPath']);
        $scriptTag->forceClosingTag(true);
        $this->response->addAdditionalHeaderData($scriptTag->render());

        // Create additional settings that are needed by pz-client.js.
        $jsVariables = [
                'useGoogleBooks' => (($this->conf['useGoogleBooks']) ? 'true' : 'false'),
                'useMaps' => (($this->conf['useMaps']) ? 'true' : 'false'),
                'useZDB' => (($this->conf['useZDB']) ? 'true' : 'false'),
                'ZDBUseClientIP' => ((!$this->conf['ZDBIP']) ? 'true' : 'false'),
                'useHistogramForYearFacets' => (($this->conf['useHistogramForYearFacets'] == '1') ? 'true' : 'false'),
                'provideCOinSExport' => (($this->conf['provideCOinSExport']) ? 'true' : 'false'),
                'showExportLinksForEachLocation' => (($this->conf['showExportLinksForEachLocation']) ? 'true' : 'false'),
                'showKVKLink' => (($this->conf['showKVKLink']) ? 'true' : 'false'),
                'useKeywords' => (($this->conf['useKeywords']) ? 'true' : 'false')
        ];
        if (array_key_exists('exportFormats', $this->conf)) {
            $exportFormats = [];
            foreach ($this->conf['exportFormats'] as $format => $value) {
                if ($value) {
                    $exportFormats[] = $format;
                }
            }
            $jsVariables['exportFormats'] = json_encode($exportFormats);
        }
        if ($this->conf['siteName']) {
            $jsVariables['siteName'] = json_encode($this->conf['siteName']);
        }
        if ($this->conf['sortOrder']) {
            $jsVariables['displaySort'] = json_encode(array_values(array_filter($this->conf['sortOrder'])));
        }
        if ($this->conf['termLists']) {
            $jsVariables['termLists'] = json_encode($this->conf['termLists']);
        }
        if ($this->conf['autocompleteURLs']) {
            $jsVariables['autocompleteURLs'] = json_encode($this->conf['autocompleteURLs']);
        }
        if ($this->conf['autocompleteSetupFunction']) {
            $jsVariables['autocompleteSetupFunction'] = $this->conf['autocompleteSetupFunction'];
        }

        $jsCommand = "\n";
        foreach ($jsVariables as $name => $value) {
            $jsCommand .= $name . ' = ' . $value . ";\n";
        }

        // Set up JavaScript function that is called by nkwgok if asked to do so.
        if ($this->conf['triggeredByNKWGOK']) {
            $jsCommand .= 'var nkwgokItemSelected = function (element) {
	var searchTerm = element.getAttribute("query");
	if (searchTerm) {
		triggerSearchForForm(undefined, ["(" +  searchTerm + ")"]);
	}
}
';
        }

        // Add the JavaScript setup commands to <head>.

        /** @var TagBuilder $scriptTag */
        $scriptTag = new TagBuilder('script');
        $scriptTag->addAttribute('type', 'text/javascript');
        $scriptTag->setContent($jsCommand);
        $this->response->addAdditionalHeaderData($scriptTag->render());

        // Load flot graphing library if needed.
        if ($this->conf['useHistogramForYearFacets']) {
            /** @var TagBuilder $scriptTag */
            $scriptTag = new TagBuilder('script');
            $scriptTag->addAttribute('type', 'text/javascript');
            $scriptTag->addAttribute('src', $this->conf['flotJSPath']);
            $scriptTag->forceClosingTag(true);
            $this->response->addAdditionalHeaderData($scriptTag->render());

            /** @var TagBuilder $scriptTag */
            $scriptTag = new TagBuilder('script');
            $scriptTag->addAttribute('type', 'text/javascript');
            $scriptTag->addAttribute('src', $this->conf['flotSelectionJSPath']);
            $scriptTag->forceClosingTag(true);
            $this->response->addAdditionalHeaderData($scriptTag->render());
        }

        // Make jQuery initialise pazpar2 when the DOM is ready.
        $jsCommand = "jQuery(document).ready(pz2ClientDomReady);\n";

        // Add Google Books support if asked to do so.
        if ($this->conf['useGoogleBooks'] || $this->conf['useMaps']) {
            // Structurally this might be better in a separate extension?
            /** @var TagBuilder $scriptTag */
            $scriptTag = new TagBuilder('script');
            $scriptTag->addAttribute('type', 'text/javascript');
            $scriptTag->addAttribute('src', 'https://www.google.com/books/jsapi.js');
            $scriptTag->forceClosingTag(true);
            $this->response->addAdditionalHeaderData($scriptTag->render());

            if ($this->conf['useGoogleBooks']) {
                $jsCommand .= "google.books.load();\n";
            }
        }

        // Write custom localisations to pz2-client.js’ localisation array $localisationOverrides;
        if (VersionNumberUtility::convertVersionNumberToInteger(TYPO3_version) < 6000000) {
            // TYPO3 4: read from TSFE (ugly)
            $localisationOverrides = $GLOBALS['TSFE']->tmpl->setup['plugin.']['tx_pazpar2.']['_LOCAL_LANG.'];
        } else {
            // TYPO3 6+: use configuration manager
            $configFramework = $this->configurationManager->getConfiguration(\TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface::CONFIGURATION_TYPE_FRAMEWORK, 'pazpar2');
            $localisationOverrides = $configFramework['_LOCAL_LANG'];
        }
        if ($localisationOverrides) {
            foreach ($localisationOverrides as $languageCode => $dictionary) {
                // remove '.' from language codes (only appear when using TYPO3 4)
                $cleanLanguageCode = str_replace('.', '', $languageCode);

                foreach ($dictionary as $key => $localisedString) {
                    $jsCommand .= 'overrideLocalisation(' . json_encode($cleanLanguageCode) . ', '
                            . json_encode($key) . ', '
                            . json_encode($localisedString) . ");\n";
                }
            }
        }

        // Add further JavaScript initialisation commands to <head>.
        /** @var TagBuilder $scriptTag */
        $scriptTag = new TagBuilder('script');
        $scriptTag->addAttribute('type', 'text/javascript');
        $scriptTag->setContent($jsCommand);
        $this->response->addAdditionalHeaderData($scriptTag->render());
    }

    /**
     * Adds <script> element to <head> containing the configuration of the
     * pazpar2 Service to use.
     */
    protected function addServiceConfigurationToHead()
    {
        $jsCommand = PHP_EOL . 'my_serviceID = ' . json_encode($this->conf['serviceID']) . ';' . PHP_EOL;
        if ($this->getPazpar2Path()) {
            $jsCommand .= 'pazpar2Path = ' . json_encode($this->getPazpar2Path()) . ';' . PHP_EOL;
        }

        /** @var TagBuilder $scriptTag */
        $scriptTag = new TagBuilder('script');
        $scriptTag->addAttribute('type', 'text/javascript');
        $scriptTag->setContent($jsCommand);
        $this->response->addAdditionalHeaderData($scriptTag->render());
    }
}
