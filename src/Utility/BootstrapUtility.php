<?php
/**
 * @copyright Copyright (c) 2016 Code-Source
 */

namespace CDSRC\Flow\Behat\Utility;

use TYPO3\Flow\Core\Booting\Scripts;
use TYPO3\Flow\Core\Bootstrap as FlowBootstrap;
use TYPO3\Flow\Exception;

class BootstrapUtility
{
    protected static $context = 'Development/Behat';

    protected static $rootPath = __DIR__ . '/../../../../../../';

    /**
     * A simple class loader that deals with the Framework classes and is intended
     * for use with unit tests executed by PHPUnit.
     *
     * @param string $className
     * @return void
     */
    public static function loadClassForTesting($className) {
        $classNameParts = explode('\\', $className);
        if (!is_array($classNameParts)) {
            return;
        }

        foreach (new \DirectoryIterator(self::$rootPath . 'Packages/') as $fileInfo) {
            if (!$fileInfo->isDir() || $fileInfo->isDot() || $fileInfo->getFilename() === 'Libraries') continue;

            $classFilePathAndName = $fileInfo->getPathname() . '/';
            foreach ($classNameParts as $index => $classNamePart) {
                $classFilePathAndName .= $classNamePart;
                if (file_exists($classFilePathAndName . '/Classes')) {
                    $packageKeyParts = array_slice($classNameParts, 0, $index + 1);
                    $classesOrTests = ($classNameParts[$index + 1] === 'Tests' && isset($classNameParts[$index + 2]) && $classNameParts[$index + 2] === 'Behat') ? '/' : '/Classes/' . implode('/', $packageKeyParts) . '/';
                    $classesFilePathAndName = $classFilePathAndName . $classesOrTests . implode('/', array_slice($classNameParts, $index + 1)) . '.php';
                    if (is_file($classesFilePathAndName)) {
                        require($classesFilePathAndName);
                        break;
                    }
                }
                $classFilePathAndName .= '.';
            }
        }
    }

    /**
     * Get flow bootstrap
     *
     * @return FlowBootstrap
     */
    public static function getBootstrap(){
        if (!isset($GLOBALS['FlowBootstrap'])) {
            spl_autoload_register(array(self::class, 'loadClassForTesting'));

            $_SERVER['FLOW_ROOTPATH'] = self::$rootPath;
            $_SERVER['FLOW_WEBPATH'] = self::$rootPath . 'Web/';

            $bootstrap = new FlowBootstrap(self::$context);
            Scripts::initializeClassLoader($bootstrap);
            Scripts::initializeSignalSlot($bootstrap);
            Scripts::initializePackageManagement($bootstrap);
            $sequence = $bootstrap->buildRuntimeSequence();
            $sequence->invoke($bootstrap);
            $GLOBALS['FlowBootstrap'] = $bootstrap;
        }
        return $GLOBALS['FlowBootstrap'];
    }

    /**
     * Get flow object manager
     *
     * @return \TYPO3\Flow\Object\ObjectManagerInterface
     * @throws Exception
     */
    public static function getObjectManager(){
        return self::getBootstrap()->getObjectManager();
    }
}