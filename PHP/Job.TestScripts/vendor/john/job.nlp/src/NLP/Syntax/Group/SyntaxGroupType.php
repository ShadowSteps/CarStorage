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
    const NounPhraseA = "NPA";
    const AdjectivePhrase = "APA";
    const AdverbPhrase = "AdvPA";
    const VerbPhrase = "VP";
    const ComplexVerb = "VC";
    const ComplexParticle = "PARTICLE";
    const ComplexAdjective = "ADJ";
    const ComplexNumeral = "M";
    const ComplexAdverb = "ADVERB";
    const PronounGroup = "PRON";
    const PrepositionPhrase = "PP";
    const Sentence = "S";

    private static $consts = [];
    public static function isSyntaxGroup(string $group): bool {
        if (count(self::$consts) <= 0) {
            $oClass = new \ReflectionClass(__CLASS__);
            self::$consts = $oClass->getConstants();
        }
        return in_array($group, self::$consts);
    }
}