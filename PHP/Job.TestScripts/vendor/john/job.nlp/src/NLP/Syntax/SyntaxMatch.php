<?php
/**
 * Created by PhpStorm.
 * User: John
 * Date: 21.1.2017 г.
 * Time: 18:22
 */

namespace Shadows\CarStorage\NLP\NLP\Syntax;


use Shadows\CarStorage\NLP\NLP\Syntax\Group\SyntaxGroup;

class SyntaxMatch
{
    private $positionFrom = 0;
    /**
     * @var SyntaxGroup
     */
    private $group;
    /**
     * @var SyntaxRule
     */
    private $rule;

    public function __construct(int $positionFrom, SyntaxRule $rule, SyntaxGroup $group)
    {
        $this->positionFrom = $positionFrom;
        $this->group = $group;
        $this->rule = $rule;
    }

    /**
     * @return int
     */
    public function getPositionFrom(): int
    {
        return $this->positionFrom;
    }

    /**
     * @return int
     */
    public function getPositionTo(): int
    {
        return $this->positionFrom + $this->group->getChildCount();
    }

    /**
     * @return SyntaxGroup
     */
    public function getGroup(): SyntaxGroup
    {
        return $this->group;
    }

    /**
     * @return SyntaxRule
     */
    public function getRule(): SyntaxRule
    {
        return $this->rule;
    }

    public function isFinished(): bool {
        return $this->getRule()->getElementCount() == count($this->getGroup()->getChildren());
    }

    public function getNextElement(): string {
        if ($this->isFinished())
            throw new \OutOfBoundsException();
        return $this->getRule()->getAtPosition(count($this->getGroup()->getChildren()));
    }

}