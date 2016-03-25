<?php

/**
 * @copyright Copyright (c) 2016 Code-Source
 */

namespace CDSRC\Flow\Behat\DataFixtures\Purger;

use Doctrine\Common\DataFixtures\Purger\PHPCRPurger as PHPCRPurgerBase;
use Doctrine\ODM\PHPCR\DocumentManager;

class PHPCRPurger extends PHPCRPurgerBase implements PurgerInterface
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

    /**
     * {@inheritdoc}
     */
    public function setDocumentManager(DocumentManager $dm)
    {
        $this->dm = $dm;
    }

    /**
     * {@inheritdoc}
     */
    public function purge()
    {
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