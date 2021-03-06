<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\Component\FilterUtil;

/**
 * FilterUtil replace interface
 */
interface JoinInterface
{
    /**
     * add Join to QueryBuilder.
     */
    public function addJoinsToQuery();
}
