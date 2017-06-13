<?php
/**
 * Created by PhpStorm.
 * User: kiril
 * Date: 5/3/2017
 * Time: 4:05 PM
 */

namespace Shadows\CarStorage\Core\ML\Feature;


abstract class Feature
{
    /**
     * @var string
     */
    private $name;

    /**
     * Feature constructor.
     * @param string $name
     */
    public function __construct(string $name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    public abstract function normalize($value): array;
}