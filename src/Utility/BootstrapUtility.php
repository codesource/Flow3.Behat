<?php
/**
 * @copyright Copyright (c) 2016 Code-Source
 */

namespace CDSRC\Flow\Behat\Utility;

use Neos\Flow\Core\Booting\Scripts;
use Neos\Flow\Core\Bootstrap as FlowBootstrap;
use Neos\Flow\Exception;

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
     * @return \Neos\Flow\Core\Bootstrap
     *
     * @throws \Exception
     */
    public static function getBootstrap(){
        if (!isset($GLOBALS['FlowBootstrap'])) {
            $_SERVER['FLOW_ROOTPATH'] = self::$rootPath;
            $_SERVER['FLOW_WEBPATH'] = self::$rootPath . 'Web/';

            // Make sure that session path is set to a readable folder
            $sessionSavePath = ini_get('session.save_path');
            if(!is_dir($sessionSavePath) || !is_readable($sessionSavePath)){
                throw new \Exception('Session "'.$sessionSavePath.'"save path do not exist or is not readable.');
            }

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
     * @return \Neos\Flow\Object\ObjectManagerInterface
     * @throws Exception
     */
    public static function getObjectManager(){
        return self::getBootstrap()->getObjectManager();
    }

    /**
     * Register class loader for testing classes
     *
     * @return void
     */
    public static function registerClassAutoLoader(){
        foreach(spl_autoload_functions() as $registerFunction){
            if(count($registerFunction) >= 2) {
                list($class, $function) = $registerFunction;
                if ($class === self::class && $function === 'loadClassForTesting') {
                    return;
                }
            }
        }
        spl_autoload_register(array(self::class, 'loadClassForTesting'));
    }
}

