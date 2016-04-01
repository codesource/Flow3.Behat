<?php
/**
 * @copyright Copyright (c) 2016 Code-Source
 */

namespace CDSRC\Flow\Behat\DataFixtures\Purger;

use CDSRC\Flow\Behat\DataFixtures\FixtureInterface;
use Doctrine\Common\DataFixtures\Purger\MongoDBPurger as MongoDBPurgerBase;
use Doctrine\ODM\MongoDB\DocumentManager;


class MongoDBPurger extends MongoDBPurgerBase implements PurgerInterface
{
    /**
     * @var array
     */
    protected $fixtures = array();

    /**
     * @var DocumentManager
     */
    protected $dm;

    /**
     * {@inheritdoc}
     */
    public function __construct(DocumentManager $dm = null)
    {
        parent::__construct($dm);
        $this->dm = $dm;
    }

    public function setDocumentManager(DocumentManager $dm)
    {
        parent::setDocumentManager($dm);
        $this->dm = $dm;
    }

    /**
     * {@inheritdoc}
     */
    public function purge()
    {
        /** @var FixtureInterface $fixture */
        foreach ($this->fixtures as $fixture) {
            $fixture->down($this->dm);
        }
        parent::purge();
        foreach ($this->fixtures as $fixture) {
            $fixture->up($this->dm);
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