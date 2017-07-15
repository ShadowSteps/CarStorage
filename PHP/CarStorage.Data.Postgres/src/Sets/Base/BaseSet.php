<?php

namespace CarStorage\Data\Postgres\Sets\Base;


use Doctrine\ORM\EntityManager;

class BaseSet
{
    /**
     * @var EntityManager
     */
    private $manager;

    /**
     * BaseSet constructor.
     * @param EntityManager $manager
     */
    public function __construct(EntityManager $manager)
    {
        $this->manager = $manager;
    }

    /**
     * @return EntityManager
     */
    public function getManager(): EntityManager
    {
        return $this->manager;
    }

}