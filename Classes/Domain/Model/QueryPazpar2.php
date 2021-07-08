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
 * Pazpar2 specific aspects of the Query class.
 */
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Query model object.
 */
class QueryPazpar2 extends Query
{
    /**
     * VARIABLES FOR INTERNAL USE.
     */

    /**
     * Stores session ID while pazpar2 is running.
     *
     * @var string
     */
    protected $pazpar2SessionID;

    /**
     * Returns URL for pazpar2 search command.
     *
     * @return string
     */
    protected function pazpar2SearchURL()
    {
        return $this->appendSessionID(parent::pazpar2SearchURL());
    }

    /**
     * Appends the session ID to $URL.
     *
     * @param string $URL
     *
     * @return string
     */
    protected function appendSessionID($URL)
    {
        return $URL.'&session='.$this->pazpar2SessionID;
    }

    /**
     * Returns URL for a status request of the current pazpar2 session.
     *
     * @return string
     */
    protected function pazpar2StatURL()
    {
        return $this->appendSessionID(parent::pazpar2StatURL());
    }

    /**
     * Returns URL for downloading pazpar2 results.
     * The parameters can be used to give the the start record
     * as well as the number of records required.
     *
     * TYPO3 typically starts running into out of memory errors when fetching
     * around 1000 records in one go with a 128MB memory limit for PHP.
     *
     * @param int $start index of first record to retrieve (optional, default: 0)
     * @param int $num   number of records to retrieve (optional, default: 500)
     *
     * @return string
     */
    protected function pazpar2ShowURL($start = 0, $num = 500)
    {
        return $this->appendSessionID(parent::pazpar2ShowURL($start, $num));
    }

    /**
     * Initialise the pazpar2 session and store the session ID in $pazpar2SessionID.
     *
     * @return bool TRUE when initialisation was successful
     */
    protected function initialiseSession()
    {
        $this->queryStartTime = time();
        $initReplyString = $this->fetchURL($this->pazpar2InitURL());
        $initReply = GeneralUtility::xml2array($initReplyString);

        if ($initReply) {
            $status = $initReply['status'];
            if ('OK' === $status) {
                $sessionID = $initReply['session'];
                if ($sessionID) {
                    $this->pazpar2SessionID = $sessionID;
                } else {
                    GeneralUtility::devLog('did not receive pazpar2 session ID', 'pazpar2', 3);
                }

                // Extract access rights information if it is available.
                if (array_key_exists('accessRights', $initReply)) {
                    $accessRights = $initReply['accessRights'];
                    $this->setInstitutionName($accessRights['institutionName']);
                    $this->setAllTargetsActive('1' === $accessRights['allTargetsActive']);
                }
            } else {
                GeneralUtility::devLog('pazpar2 init status is not "OK" but "'.$status.'"', 'pazpar2', 3);
            }
        } else {
            GeneralUtility::devLog('could not parse pazpar2 init reply', 'pazpar2', 3);
        }

        return null !== $this->pazpar2SessionID;
    }
}
