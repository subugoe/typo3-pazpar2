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

/**
 * Service Proxy specific aspects of the Query class.
 */
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Query model object.
 */
class QueryServiceProxy extends Query
{

    /**
     * VARIABLES FOR INTERNAL USE
     */

    /**
     * Stores the cookie provided by Service Proxy.
     * @var string
     */
    protected $cookie;

    /**
     * Absolute path of the Service Proxy authentication service on the host.
     *
     * @var string
     */
    protected $serviceProxyAuthPath;

    /**
     * TODO: Query Service Proxy auth URL and store the cookie received.
     *
     * @return bool TRUE when initialisation was successful.
     */
    protected function initialiseSession()
    {
        $this->queryStartTime = time();
        $authReplyString = $this->fetchURL($this->getServiceProxyAuthURL());
        $authReply = GeneralUtility::xml2array($authReplyString);

        $success = false;
        if ($authReply) {
            $status = $authReply['status'];
            if ($status === 'OK') {
                $success = true;
            } else {
                GeneralUtility::devLog('Service Proxy init status is not "OK" but "' . $status . '"', 'pazpar2', 3);
            }
        } else {
            GeneralUtility::devLog('could not parse Service Proxy init reply', 'pazpar2', 3);
        }

        return ($this->cookie !== null) && $success;
    }

    /**
     * Returns the content loaded from the given URL.
     * Uses curl to fetch the cookie from the response and insert it into
     * requests, if available.
     *
     * @param string $URL to fetch
     * @return string
     */
    protected function fetchURL($URL)
    {
        $cookieHeader = [];
        if ($this->cookie) {
            $cookieHeader[] = 'Cookie: ' . $this->cookie . ';';
        }

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $URL);
        curl_setopt($curl, CURLOPT_HEADER, 1);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $cookieHeader);

        $body = null;
        $result = curl_exec($curl);
        if ($result) {
            $parts = explode("\r\n\r\n", $result, 2);
            $headers = explode("\r\n", $parts[0]);
            if (!$this->cookie) {
                $setCookieString = 'Set-Cookie: ';
                foreach ($headers as $header) {
                    if (strpos($header, $setCookieString) === 0) {
                        $cookieString = substr($header, strlen($setCookieString));
                        $cookieStringParts = explode(';', $cookieString);
                        $this->cookie = $cookieStringParts[0];
                        break;
                    }
                }
            }

            $body = $parts[1];
        }

        return $body;
    }

    /**
     * Return URL of pazpar2 service.
     * If it is not set, return default URL on localhost.
     *
     * @return string
     */
    public function getServiceProxyAuthURL()
    {
        $URL = 'http://' . GeneralUtility::getIndpEnv('HTTP_HOST') . $this->getServiceProxyAuthPath();
        return $URL;
    }

    /**
     * @return string
     */
    public function getServiceProxyAuthPath()
    {
        return $this->serviceProxyAuthPath;
    }

    /**
     * @param string $newPazpar2Path
     */
    public function setServiceProxyAuthPath($newServiceProxyAuthPath)
    {
        $this->serviceProxyAuthPath = $newServiceProxyAuthPath;
    }
}
