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
    private $ruleElementPointer = -1;
    private $type;
    private $elementCount;

    /**
     * SyntaxRule constructor.
     * @param $rule
     * @param $type
     */
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

    /**
     * @return int
     */
    public function getElementCount(): int
    {
        return $this->elementCount;
    }

    public function getCurrentElement(): string {
        if ($this->elementCount < $this->ruleElementPointer)
            throw new NoMoreElementsInRuleException();
        return $this->ruleElements[$this->ruleElementPointer];
    }

    public function moveToNextElement() {
        if ($this->elementCount <= $this->ruleElementPointer + 1)
            throw new NoMoreElementsInRuleException();
        $this->ruleElementPointer++;
    }

    public function resetPosition(){
        $this->ruleElementPointer = -1;
    }

    /**
     * @return int
     */
    public function getRuleElementPointer(): int
    {
        return $this->ruleElementPointer;
    }

    public function isFinished(): bool {
        return ($this->elementCount == $this->ruleElementPointer + 1);
    }

    public function __toString(): string
    {
        $string = "[{$this->getType()} ";
        foreach ($this->ruleElements as $child) {
            $string .= $child."+";
        }
        $string = rtrim($string, "+") .  "]";
        return $string;
    }

}