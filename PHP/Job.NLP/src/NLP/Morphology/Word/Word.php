<?php
/**
 * Created by PhpStorm.
 * User: John
 * Date: 30.12.2016 г.
 * Time: 18:07
 */

namespace Shadows\CarStorage\NLP\NLP\Morphology\Word;


use Shadows\CarStorage\NLP\NLP\Morphology\Exception\UnrecognizedWordTypeException;

class Word
{
    private $rawForm;
    private $basicForm;
    private $wordType;

    /**
     * Word constructor.
     * @param $rawForm
     * @param $basicForm
     * @param $wordType
     */
    public function __construct(string $rawForm, string $basicForm, string $wordType)
    {
        if (!WordType::isValidType($wordType))
            throw new UnrecognizedWordTypeException($wordType);
        $this->rawForm = $rawForm;
        $this->basicForm = $basicForm;
        $this->wordType = $wordType;
    }

    /**
     * @return string
     */
    public function getRawForm(): string
    {
        return $this->rawForm;
    }

    /**
     * @return string
     */
    public function getBasicForm(): string
    {
        return $this->basicForm;
    }

    /**
     * @return string
     */
    public function getWordType(): string
    {
        return $this->wordType;
    }

    public function getWordTypeString(): string {
        return "{SE:".$this->getWordType()."}";
    }
}