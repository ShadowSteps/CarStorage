<?php
/**
 * Created by PhpStorm.
 * User: John
 * Date: 30.12.2016 г.
 * Time: 18:34
 */

namespace Shadows\CarStorage\NLP\NLP\Morphology\Exception;


class UnrecognizedWordTypeException extends \Exception
{

    /**
     * UnrecognizedWordTypeException constructor.
     */
    public function __construct($name, \Throwable $prev = null)
    {
        parent::__construct("Unrecognized word type provided: $name!", 0, $prev);
    }
}