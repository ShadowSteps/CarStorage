<?php
/**
 * Created by PhpStorm.
 * User: kiril
 * Date: 5/3/2017
 * Time: 4:05 PM
 */

namespace Shadows\CarStorage\Core\ML\Feature;


class Feature
{
    /**
     * @var string
     */
    private $name;
    /**
     * @var int
     */
    private $type;

    /**
     * Feature constructor.
     * @param string $name
     * @param int $type
     */
    public function __construct(string $name, int $type)
    {
        $this->name = $name;
        $this->type = $type;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return int
     */
    public function getType(): int
    {
        return $this->type;
    }


}