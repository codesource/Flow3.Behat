<?php
/**
 * @copyright Copyright (c) 2016 Code-Source
 */

namespace CDSRC\Flow\Behat\DataFixtures\Purger;

use Doctrine\Common\DataFixtures\Purger\PurgerInterface as PurgerInterfaceBase;

interface PurgerInterface extends PurgerInterfaceBase
{

    /**
     * Set fixtures that allow purging hooking
     *
     * @param array $fixtures
     *
     * @return PurgerInterface
     */
    public function setFixtures(array $fixtures);
}