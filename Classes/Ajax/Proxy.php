<?php

namespace Subugoe\Pazpar2\Ajax;

use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Http\RequestFactory;
use TYPO3\CMS\Core\Http\Response;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Controller for handling the http stuff in proxying.
 */
class Proxy
{
    public function proxyAction(ServerRequestInterface $request, Response $response = null)
    {
        $method = 'GET';
        $arguments = $request->getQueryParams();

        if (!$response) {
            $response = GeneralUtility::makeInstance(Response::class);
        }

        if ('POST' === $request->getMethod()) {
            $arguments = $request->getQueryParams();
            $method = $request->getMethod();
        }

        unset($arguments['id'], $arguments['eID'], $arguments['type']);
        $arguments = http_build_query($arguments);

        $configuration = GeneralUtility::makeInstance(ExtensionConfiguration::class)->get('pazpar2', 'backendUrl');

        // Initiate the Request Factory, which allows to run multiple requests
        $requestFactory = GeneralUtility::makeInstance(RequestFactory::class);
        $url = sprintf('%s?%s', $configuration, $arguments);

        $options = [
            'headers' => [
                'Accept' => 'application/xml',
            ],
        ];

        // Return a PSR-7 compliant response object
        $data = $requestFactory->request($url, $method, $options);
        // Get the content as a string on a successful request
        if (200 === $data->getStatusCode()) {
            $response->getBody()->write(trim($data->getBody()->getContents()));
            return $response->withHeader('Content-Type', 'text/xml');
        }

        $response->getBody()->write('');

        return $response->withHeader('Content-Type', 'text/xml');
    }
}
