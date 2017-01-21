<?php
/**
 * Created by PhpStorm.
 * User: John
 * Date: 21.1.2017 г.
 * Time: 11:05
 */

namespace Shadows\CarStorage\NLP\NLP\Syntax\Group;


class SyntaxGroupType
{
    const NounPhrase = "NP";
    const VerbPhrase = "VP";
    const Sentence = "S";

    public static function isSyntaxGroup(string $group): bool {
        $oClass = new \ReflectionClass(__CLASS__);
        $types = $oClass->getConstants();
        return in_array($group, $types);
    }
}