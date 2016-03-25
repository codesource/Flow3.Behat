<?php
/**
 * @copyright Copyright (c) 2016 Code-Source
 */

namespace CDSRC\Flow\Behat\Dictionary;

use Behat\Testwork\Hook\Scope\BeforeSuiteScope;
use CDSRC\Flow\Behat\DataFixtures\Purger\ORMPurger;
use CDSRC\Flow\Behat\RequestHandler;
use CDSRC\Flow\Behat\Utility\BootstrapUtility;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\DataFixtures\Loader;
use Doctrine\Common\DataFixtures\Executor\ORMExecutor;

trait FixturesDictionary
{

    /**
     *
     * @var \Doctrine\Common\Persistence\ObjectManager
     */
    protected $doctrineEntityManager;

    /**
     * Initialize Flow bootstrap
     *
     * @param BeforeSuiteScope $scope
     *
     * @BeforeSuite
     */
    public static function initializeFlowBootstrap(BeforeSuiteScope $scope){
        BootstrapUtility::getBootstrap();
    }

    /**
     * Fixtures present in given file or directory should be loaded
     *
     * @param string $source
     *
     * @Then /^I load fixtures in "(?P<source>.*?)"$/
     *
     * @throws \Exception
     */
    public function iLoadFixturesIn($source)
    {
        $loader = new Loader();
        if (file_exists($source)) {
            if (is_dir($source)) {
                $loader->loadFromDirectory($source);
            } elseif (is_file($source)) {
                $loader->loadFromFile($source);
            }
        } elseif (class_exists($source)) {
            if (!is_subclass_of($source, FixtureInterface::class)){
                throw new \Exception(sprintf('"%s" must implement "Doctrine\Common\DataFixtures\FixtureInterface"',
                    $source));
            }
            $loader->addFixture(new $source());
        } else {
            throw new \Exception(sprintf('"%s" is not a valid fixtures reference', $source));
        }
        $fixtures = $loader->getFixtures();
        // TODO: Find a good solution to have other purger and executor (MongoDB, PHPCR)
        $purger = new ORMPurger();
        $purger->setFixtures($fixtures);
        $executor = new ORMExecutor(BootstrapUtility::getObjectManager()->get('Doctrine\Common\Persistence\ObjectManager'), $purger);
        $executor->execute($fixtures, false);
    }
}