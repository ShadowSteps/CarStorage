<?php
/**
 * Created by PhpStorm.
 * User: kiril
 * Date: 5/2/2017
 * Time: 9:23 PM
 */

namespace Shadows\CarStorage\Crawler\Utils;


use Shadows\CarStorage\NLP\NLP\Keywords\Extractor;
use Shadows\CarStorage\NLP\NLP\Syntax\Group\SyntaxGroupType;
use Shadows\CarStorage\NLP\NLP\Syntax\SyntaxRule;

class NLPHelper
{
    /**
     * @var Extractor
     */
    private $keywordsExtractor;
    private $schemes;
    public function __construct()
    {
        $rules = [
            new SyntaxRule("{SE:PRO}", SyntaxGroupType::PronounGroup),
            new SyntaxRule("{SE:PC}", SyntaxGroupType::ComplexParticle),
            new SyntaxRule("{SG:PARTICLE}+{SG:PARTICLE}", SyntaxGroupType::ComplexParticle),
            new SyntaxRule("{SE:V}", SyntaxGroupType::ComplexVerb),
            new SyntaxRule("{SE:V}+{SG:PRON}", SyntaxGroupType::ComplexVerb),
            new SyntaxRule("{SG:VC}+{SE:CONJ}+{SG:VC}", SyntaxGroupType::ComplexVerb),
            new SyntaxRule("{SG:ADVERB}+{SG:VC}", SyntaxGroupType::ComplexVerb),
            new SyntaxRule("{SG:VC}+{SG:PP}", SyntaxGroupType::ComplexVerb),
            new SyntaxRule("{SG:PRON}+{SG:VC}", SyntaxGroupType::ComplexVerb),
            new SyntaxRule("{SG:VC}+{SG:PARTICLE}", SyntaxGroupType::ComplexVerb),
            new SyntaxRule("{SG:PARTICLE}+{SG:VC}", SyntaxGroupType::ComplexVerb),
            new SyntaxRule("{SE:N}", SyntaxGroupType::NounPhrase),
            new SyntaxRule("{SG:NP}+{SE:CONJ}+{SG:NP}", SyntaxGroupType::NounPhrase),
            new SyntaxRule("{SG:NP}+{SG:PRON}", SyntaxGroupType::NounPhrase),
            new SyntaxRule("{SG:M}+{SG:NP}", SyntaxGroupType::NounPhrase),
            new SyntaxRule("{SG:PRON}+{SG:NP}", SyntaxGroupType::NounPhrase),
            new SyntaxRule("{SG:ADJ}+{SG:NP}", SyntaxGroupType::NounPhrase),
            new SyntaxRule("{SE:V}+{SG:NP}", SyntaxGroupType::NounPhrase),
            new SyntaxRule("{SG:NP}+{SG:ADJ}", SyntaxGroupType::NounPhrase),
            new SyntaxRule("{SG:PARTICLE}+{SG:NP}", SyntaxGroupType::NounPhrase),
            new SyntaxRule("{SG:NP}+{SG:PARTICLE}", SyntaxGroupType::NounPhrase),
            new SyntaxRule("{SE:A}", SyntaxGroupType::ComplexAdjective),
            new SyntaxRule("{SG:ADJ}+{SG:ADJ}", SyntaxGroupType::ComplexAdjective),
            new SyntaxRule("{SG:ADJ}+{SE:CONJ}+{SG:ADJ}", SyntaxGroupType::ComplexAdjective),
            new SyntaxRule("{SG:ADJ}+{SG:PARTICLE}", SyntaxGroupType::ComplexAdjective),
            new SyntaxRule("{SG:PARTICLE}+{SG:ADJ}", SyntaxGroupType::ComplexAdjective),
            new SyntaxRule("{SE:NU}", SyntaxGroupType::ComplexNumeral),
            new SyntaxRule("{SG:M}+{SG:M}", SyntaxGroupType::ComplexNumeral),
            new SyntaxRule("{SG:M}+{SG:PARTICLE}", SyntaxGroupType::ComplexNumeral),
            new SyntaxRule("{SG:PARTICLE}+{SG:M}", SyntaxGroupType::ComplexNumeral),
            new SyntaxRule("{SE:ADV}", SyntaxGroupType::ComplexAdverb),
            new SyntaxRule("{SG:ADVERB}+{SE:CONJ}+{SG:ADVERB}", SyntaxGroupType::ComplexAdverb),
            new SyntaxRule("{SG:PARTICLE}+{SG:ADVERB}", SyntaxGroupType::ComplexAdverb),
            new SyntaxRule("{SG:ADVERB}+{SG:PARTICLE}", SyntaxGroupType::ComplexAdverb),
            new SyntaxRule("{SE:PREP}", SyntaxGroupType::PrepositionPhrase),
            new SyntaxRule("{SG:PP}+{SG:PP}", SyntaxGroupType::PrepositionPhrase),
            new SyntaxRule("{SG:PP}+{SG:PRON}", SyntaxGroupType::PrepositionPhrase),
            new SyntaxRule("{SG:PP}+{SG:NP}", SyntaxGroupType::PrepositionPhrase),
            new SyntaxRule("{SG:PP}+{SG:ADJ}", SyntaxGroupType::PrepositionPhrase),
            new SyntaxRule("{SG:PP}+{SG:ADVERB}", SyntaxGroupType::PrepositionPhrase),
            new SyntaxRule("{SG:VC}+{SG:NP}", SyntaxGroupType::Sentence),
            new SyntaxRule("{SG:NP}+{SG:VC}", SyntaxGroupType::Sentence),
            new SyntaxRule("{SG:VC}+{SG:PP}", SyntaxGroupType::Sentence),
            new SyntaxRule("{SG:PP}+{SG:VC}", SyntaxGroupType::Sentence),
            new SyntaxRule("{SG:VC}", SyntaxGroupType::Sentence),
            new SyntaxRule("{SG:NP}", SyntaxGroupType::Sentence)
        ];
        $this->schemes = [
            '/SG[@type="S"]/SG[@type="VC"]/SE[@type="V" and text()="има " or 
                text()="притежава "]/../../SG[@type="PP" or @type="NP"]',
            '/SG[@type="S" and count(*[self::*]) = 1]/SG[@type="NP"]',
            '/SG[@type="S"]//SG[@type="VC"]/SE[@type="V" and text()="е " or text()="има " or 
                text()="притежава "]/../../SG[@type="PP" or @type="NP"]'
        ];
        $this->keywordsExtractor = new Extractor($rules);
    }

    public function ExtractKeywordsFromDescription(string $description): array {
        return $this->keywordsExtractor->getKeywordsForString($description, $this->schemes);
    }
}