<?php
/**
 * Created by PhpStorm.
 * User: John
 * Date: 27.12.2016 г.
 * Time: 14:31
 */

namespace Shadows\CarStorage\Data\Postgres\Sets\Base;


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