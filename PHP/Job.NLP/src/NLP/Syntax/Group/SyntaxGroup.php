<?php
/**
 * Created by PhpStorm.
 * User: John
 * Date: 21.1.2017 г.
 * Time: 11:46
 */

namespace Shadows\CarStorage\NLP\NLP\Syntax\Group;


use Shadows\CarStorage\NLP\NLP\Morphology\Word\Word;
use Shadows\CarStorage\NLP\NLP\Syntax\Exception\UnrecognizedSyntaxGroupException;

class SyntaxGroup
{
    private $type;
    private $children = [];
    /**
     * SyntaxGroup constructor.
     */
    public function __construct(string $type)
    {
        if (!SyntaxGroupType::isSyntaxGroup($type))
            throw new UnrecognizedSyntaxGroupException($type);
        $this->type = $type;
    }

    public function addChild($atom) {
        if ($atom instanceof SyntaxGroup||$atom instanceof Word){
            $this->children[] = $atom;
        }
    }

    /**
     * @return array
     */
    public function getChildren(): array
    {
        return $this->children;
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
    public function getChildCount(): int
    {
        $count = 0;
        foreach ($this->children as $child) {
            if ($child instanceof Word)
                $count++;
            else if ($child instanceof SyntaxGroup)
                $count += $child->getChildCount();
        }
        return $count;
    }

    public function toString($level = 0): string
    {
        $string = $level."[{$this->getType()}";
        foreach ($this->children as $child) {
            if ($child instanceof Word)
                $string .= " ".($level+1)."[{$child->getWordType()} ".$child->getRawForm()."]".($level+1);
            else if ($child instanceof SyntaxGroup)
                $string .= " ".$child->toString($level+1);
        }
        $string .=  "]".$level;
        return $string;
    }

    public function cloneGroup() {
        $group = new SyntaxGroup($this->getType());
        foreach ($this->children as $child)
        {
            if ($child instanceof SyntaxGroup)
                $group->addChild($child->cloneGroup());
            else
                $group->addChild($child);
        }
        return $group;
    }

}