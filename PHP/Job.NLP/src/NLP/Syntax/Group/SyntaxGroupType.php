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
    const NounPhraseC = "NPC";
    const NounPhraseA = "NPA";
    const AdjectivePhraseC = "APC";
    const AdjectivePhraseA = "APA";
    const AdverbPhraseC = "AdvPC";
    const AdverbPhraseA = "AdvPA";
    const VerbPhrase = "VP";
    const VerbPhraseC = "VPC";
    const VerbPhraseS = "VPS";
    const VerbPhraseA = "VPA";
    const VerbPhraseF = "VPF";
    const ComplexVerb = "VC";
    const ComplexParticle = "PARTICLE";
    const ComplexAdjective = "ADJ";
    const ComplexNumeral = "M";
    const ComplexAdverb = "ADVERB";
    const PronounGroup = "PRON";
    const PrepositionPhrase = "PP";
    const Sentence = "S";

    public static function isSyntaxGroup(string $group): bool {
        $oClass = new \ReflectionClass(__CLASS__);
        $types = $oClass->getConstants();
        return in_array($group, $types);
    }
}