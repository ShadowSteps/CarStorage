<?php
/**
 * Created by PhpStorm.
 * User: John
 * Date: 21.1.2017 г.
 * Time: 13:09
 */

namespace Shadows\CarStorage\NLP\NLP\Syntax;


use Shadows\CarStorage\NLP\NLP\Morphology\Exception\UnrecognizedWordTypeException;
use Shadows\CarStorage\NLP\NLP\Morphology\Word\WordType;
use Shadows\CarStorage\NLP\NLP\Syntax\Exception\InvalidSyntaxPathException;
use Shadows\CarStorage\NLP\NLP\Syntax\Exception\NoMoreElementsInRuleException;
use Shadows\CarStorage\NLP\NLP\Syntax\Exception\UnrecognizedSyntaxGroupException;
use Shadows\CarStorage\NLP\NLP\Syntax\Group\SyntaxGroupType;

class SyntaxRule
{
    private $ruleElements;
    private $type;
    private $elementCount;

    public function __construct(string $rule, string $type)
    {
        if (!SyntaxGroupType::isSyntaxGroup($type))
            throw new UnrecognizedSyntaxGroupException($type);
        try {
            $elements = explode("+",$rule);
            $this->elementCount = count($elements);
            foreach ($elements as $element){
                $match = [];
                if (preg_match("/\\{SE:([A-Z]+)\\}/", $element, $match)&&!WordType::isValidType($match[1]))
                    throw new UnrecognizedWordTypeException($element);
                else if (preg_match("/\\{SG:([A-Z]+)\\}/", $element, $match)&&!SyntaxGroupType::isSyntaxGroup($match[1]))
                    throw new UnrecognizedSyntaxGroupException($element);
                $this->ruleElements[] = $element;
            }
        } catch (\Exception $exp) {
            throw new InvalidSyntaxPathException("Invalid syntax group path!", 0, $exp);
        }
        $this->type = $type;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    public function getTypeString(): string {
        return "{SG:".$this->getType()."}";
    }

    public function isAtPosition(string $type, int $position){
        if ($this->elementCount < $position)
            throw new \OutOfBoundsException($position);
        return strcmp($type, $this->ruleElements[$position]) == 0;
    }

    public function getAtPosition($position) {
        if ($this->elementCount <= $position)
            throw new \OutOfBoundsException($position);
        return $this->ruleElements[$position];
    }

    /**
     * @return int
     */
    public function getElementCount(): int
    {
        return $this->elementCount;
    }
}