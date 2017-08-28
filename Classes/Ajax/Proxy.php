<?php

namespace Subugoe\Pazpar2\Ajax;

use TYPO3\CMS\Core\Http\RequestFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Controller for handling the http stuff in proxying
 */
class Proxy
{
    public function proxyAction()
    {
        $arguments = GeneralUtility::_GET();
        unset($arguments['id']);
        unset($arguments['eID']);
        unset($arguments['type']);
        $arguments = http_build_query($arguments);

        $configuration = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['pazpar2']);
        // Initiate the Request Factory, which allows to run multiple requests
        $requestFactory = GeneralUtility::makeInstance(RequestFactory::class);
        $url = sprintf('%s?%s', $configuration['backendUrl'], $arguments);

        $options = [
            'headers' => [
                'Accept' => 'application/xml'
            ]
        ];

        // Return a PSR-7 compliant response object
        $response = $requestFactory->request($url, 'GET', $options);
        // Get the content as a string on a successful request
        if ($response->getStatusCode() === 200) {
            return trim($response->getBody()->getContents());
        }
        return '';
    }
}

header('Content-type: text/xml');

echo (new Proxy())->proxyAction();
