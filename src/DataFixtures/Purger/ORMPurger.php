<?php
/**
 * @copyright Copyright (c) 2016 Code-Source
 */

namespace CDSRC\Flow\Behat\DataFixtures\Purger;


use CDSRC\Flow\Behat\DataFixtures\FixtureInterface;
use Doctrine\Common\DataFixtures\Purger\ORMPurger as ORMPurgerBase;
use Doctrine\ORM\EntityManagerInterface;

class ORMPurger extends ORMPurgerBase implements PurgerInterface
{
    /**
     * @var array
     */
    protected $fixtures = array();

    /**
     * @var EntityManagerInterface
     */
    protected $em;

    /**
     * {@inheritdoc}
     */
    public function __construct(EntityManagerInterface $em = null)
    {
        parent::__construct($em);
        $this->em = $em;
    }

    public function setEntityManager(EntityManagerInterface $em)
    {
        parent::setEntityManager($em);
        $this->em = $em;
    }

    /**
     * {@inheritdoc}
     */
    public function purge()
    {
        foreach ($this->fixtures as $fixture) {
            $fixture->down($this->em);
        }
        parent::purge();
        foreach ($this->fixtures as $fixture) {
            $fixture->up($this->em);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function setFixtures(array $fixtures)
    {
        $this->fixtures = array();
        foreach ($fixtures as $fixture) {
            if ($fixture instanceof FixtureInterface) {
                $this->fixtures[] = $fixture;
            }
        }

        return $this;
    }
}