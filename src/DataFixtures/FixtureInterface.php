<?php
/**
 * @copyright Copyright (c) 2016 Code-Source
 */

namespace CDSRC\Flow\Behat\DataFixtures;

use Doctrine\Common\DataFixtures\FixtureInterface as FixtureInterfaceBase;
use Doctrine\ORM\EntityManagerInterface;

interface FixtureInterface extends FixtureInterfaceBase
{
    /**
     * Load fixtures with the passed EntityManager after purging
     *
     * @param EntityManagerInterface $manager
     */
    public function up(EntityManagerInterface $manager);

    /**
     * Load fixtures with the passed EntityManager before purging
     *
     * @param EntityManagerInterface $manager
     */
    public function down(EntityManagerInterface $manager);

    /**
     * Add a dependency that is called before up and down
     *
     * @param \CDSRC\Flow\Behat\DataFixtures\FixtureInterface $fixture
     *
     * @return FixtureInterface
     */
    public function addDependency(FixtureInterface $fixture);

    /**
     * Get all dependencies
     *
     * @return array
     */
    public function getDependencies();
}
