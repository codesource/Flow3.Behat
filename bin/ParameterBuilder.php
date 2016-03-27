<?php
/**
 * @copyright Copyright (c) 2016 Code-Source
 */
if (PHP_SAPI !== 'cli') {
    echo "Can only be run by CLI";
    exit;
}


class ParameterBuilder
{
    /**
     * @var string
     */
    protected static $base = '';
    /**
     * @var array
     */
    protected $files = array();
    /**
     * @var string
     */
    protected $configurationRegExp = '';

    /**
     * @var string
     */
    protected $testsRegExp = '';

    /**
     * ParameterBuilder constructor.
     *
     * @param array $files
     */
    public function __construct(array $files = array())
    {
        $this->files = $files;
        $this->configurationRegExp = '#' . self::getBase() . 'Packages/Application/.*/Tests/behat.yml#';
        $this->testsRegExp = '#' . self::getBase() . 'Packages/Application/.*/Tests/.*\.(feature(\d+(-\d+)?)?|scenarios)#';
    }

    /**
     * Render line by line arguments call for behat
     *
     * @return string
     */
    public function render()
    {
        $configurations = $this->parse();

        return implode("\n", $configurations);
    }

    /**
     * Get base path to flow directory
     *
     * @return string
     */
    public static function getBase()
    {
        if (!self::$base) {
            self::$base = rtrim(realpath(__DIR__ . "/../../../../.."), '/') . '/';
        }

        return self::$base;
    }

    /**
     * Retrieve configuration file for a feature
     *
     * @param $file
     *
     * @return bool|string
     */
    protected function getConfigurationForTestFile($file)
    {

        $directory = rtrim(dirname($file), '/');
        $configuration = $directory . '/behat.yml';
        if (strstr($configuration, self::getBase()) !== false) {
            if (preg_match($this->configurationRegExp, $configuration)) {
                return $configuration;
            } else {
                return $this->getConfigurationForTestFile($directory);
            }
        }

        return false;
    }

    /**
     * Parse files arguments
     *
     * @return array
     */
    protected function parse()
    {
        $configurations = array();
        if ($this->files) {
            foreach ($this->files as $file) {
                if (preg_match($this->configurationRegExp, $file) && !isset($configurations[$file])) {
                    $configurations[] = '--config=' . $file;
                }
                if (preg_match($this->testsRegExp, $file)) {
                    $configuration = $this->getConfigurationForTestFile($file);
                    if ($configuration !== false) {
                        $configurations[] = '--config=' . $configuration . ' ' . $file;
                    }
                }
            }
        } else {
            $files = glob(self::getBase() . 'Packages/Application/**/Tests/behat.yml');
            foreach ($files as $file) {
                $configurations[] = '--config=' . $file;
            }
        }

        return $configurations;
    }
}


$files = array();
$matches = array();
if (isset($argv[1]) && preg_match_all('/"(.*?)(:\d+(-\d+)?)?"/', $argv[1], $matches) > 0) {
    foreach ($matches[1] as $i => $file) {
        $file = strpos($file, '/') === 0 ? $file : ParameterBuilder::getBase() . $file;
        if (file_exists($file)) {
            $files[] = realpath($file) . (isset($matches[2][$i]) ? $matches[2][$i] : '');
        }
    }
}
$builder = new ParameterBuilder($files);
echo $builder->render();