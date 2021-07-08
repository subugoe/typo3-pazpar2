<?php

namespace Subugoe\Pazpar2\Service;

use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/*******************************************************************************
 * Copyright notice
 *
 * Copyright (C) 2011 by Sven-S. Porst, SUB Göttingen
 * <porst@sub.uni-goettingen.de>
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

class Flexform
{
    /**
     * Called from Flexform to provide menu items with Neuerwerbungen subjects.
     *
     * @param array $config
     *
     * @return array
     */
    public function buildMenu($config)
    {
        $rootNodes = $this->queryForChildrenOf('NE');

        $options = [['', '']];
        foreach ($rootNodes as $row) {
            $optionTitle = $row['descr'];
            $optionValue = $row['ppn'];
            $options[] = [$optionTitle, $optionValue];
        }

        $config['items'] = array_merge($config['items'], $options);

        return $config;
    }

    /**
     * Queries the database for all records having the $parentGOK parameter as their parent element
     *  and returns the query result.
     *
     * This requires the GOK plug-in and its database table to work.
     *
     * @param string $parentGOK
     *
     * @return array
     */
    private function queryForChildrenOf(string $parentGOK)
    {
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('tx_nkwgok_data');

        return $queryBuilder
            ->select('*')
            ->from('tx_nkwgok_data')
            ->where($queryBuilder->expr()->eq('parent', '?'))
            ->setParameter(0, $parentGOK)
            ->orderBy('notation', 'ASC')
            ->execute()
            ->fetchAll();
    }
}
