<?php
/**
 * @copyright Copyright (c) 2016 Code-Source
 */

namespace CDSRC\Flow\Behat\Dictionary;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\DataFixtures\Loader;
use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;

trait FixturesDictionary
{
    /**
     * @var bool
     */
    protected $flowBootstrapLoaded = false;

    /**
     *
     * @var \Doctrine\Common\Persistence\ObjectManager
     */
    protected $doctrineEntityManager;

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
        $this->loadFlowBootstrap();

        $loader = new Loader();
        if (file_exists($source)) {
            if (is_dir($source)) {
                $loader->loadFromDirectory($source);
            } elseif (is_file($source)) {
                $loader->loadFromFile($source);
            }
        } elseif (class_exists($source)) {
            if (!$source instanceof FixtureInterface::class){
                throw new \Exception(sprintf('"%s" must implement "Doctrine\Common\DataFixtures\FixtureInterface"',
                    $source));
            }
            $loader->addFixture(new $source());
        } else {
            throw new \Exception(sprintf('"%s" is not a valid fixtures reference', $source));
        }
        $purger = new ORMPurger();
        $executor = new ORMExecutor($this->doctrineEntityManager, $purger);
        $executor->execute($loader->getFixtures());
    }


    protected function loadFlowBootstrap()
    {
        if (!$this->flowBootstrapLoaded) {
            $context = 'Development/Behat';

            $_SERVER['FLOW_ROOTPATH'] = dirname(__FILE__) . '/../../../../../';

            if (DIRECTORY_SEPARATOR === '/') {
                // Fixes an issue with the autoloader, see FLOW-183
                shell_exec('cd ' . escapeshellarg($_SERVER['FLOW_ROOTPATH']) . ' && FLOW_CONTEXT="' . $context . '" ./flow flow:cache:warmup');
            }

            require_once($_SERVER['FLOW_ROOTPATH'] . 'Packages/Framework/TYPO3.Flow/Classes/TYPO3/Flow/Core/Bootstrap.php');
            $bootstrap = new \TYPO3\Flow\Core\Bootstrap($context);
            $bootstrap->run();
            $this->doctrineEntityManager =  $bootstrap->getObjectManager()->get('\Doctrine\Common\Persistence\ObjectManager');
        }
        $this->flowBootstrapLoaded = true;
    }
}