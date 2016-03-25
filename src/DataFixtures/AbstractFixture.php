<?php
/**
 * @copyright Copyright (c) 2016 Code-Source
 */

namespace CDSRC\Flow\Behat\DataFixtures;


use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityManagerInterface;

abstract class AbstractFixture implements FixtureInterface
{
    /**
     * @var array
     */
    protected $dependencies = array();

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        throw new \Exception('Must be implemented if called');
    }

    /**
     * {@inheritdoc}
     */
    public function up(EntityManagerInterface $manager)
    {
        foreach ($this->getDependencies() as $dependency) {
            $dependency->up($manager);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function down(EntityManagerInterface $manager)
    {
        foreach ($this->getDependencies() as $dependency) {
            $dependency->down($manager);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function addDependency(FixtureInterface $fixture)
    {
        $this->dependencies[] = $fixture;
    }

    /**
     * {@inheritdoc}
     */
    public function getDependencies()
    {
        return $this->dependencies;
    }
}