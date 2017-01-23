<?php

namespace Shadows\CarStorage\NLP\NLP\Syntax;

use Shadows\CarStorage\NLP\NLP\Morphology\Word\Word;
use Shadows\CarStorage\NLP\NLP\Morphology\Word\WordType;
use Shadows\CarStorage\NLP\NLP\Syntax\Exception\NoMoreElementsInRuleException;
use Shadows\CarStorage\NLP\NLP\Syntax\Group\SyntaxGroup;
use Shadows\CarStorage\NLP\NLP\Syntax\Group\SyntaxGroupType;

class SyntaxAnalyzer
{
    /**
     * @var SyntaxRule[]
     */
    private $grammarRules = [];
    /**
     * @var SyntaxRule[]
     */
    private $indexedRules = [];
    public function addRule(SyntaxRule $rule) {
        $this->grammarRules[] = $rule;
        $this->indexedRules[$rule->getType()][] = $rule;
    }

    private function matchFinishProcedure(SyntaxMatch $match, array $possibilities): array {
        $completeSets = [];
        foreach ($possibilities as $key => $possibility) {
            /**
             * @var $possibility SyntaxMatch
             */
            if ($possibility->isFinished())
                continue;
            $element = $possibility->getRule()->getCurrentElement();
            $matches = [];
            if (!(preg_match("/\\{SG:([A-Z]+)\\}/", $element, $matches)&&SyntaxGroupType::isSyntaxGroup($matches[1])))
                continue;
            if ($matches[1] != $match->getGroup()->getType())
                continue;
            if ($possibility->getPositionTo() != $match->getPositionFrom())
                continue;
            $possibility->getGroup()->addChild($match->getGroup());
            $completeSets[] = $match;
            try {
                $possibility->getRule()->moveToNextElement();
            }
            catch (NoMoreElementsInRuleException $exp) {
                $completeSets = $this->matchFinishProcedure($possibility, $possibilities);
            }
        }
        return array_merge($completeSets, [$match]);
    }

    /**
     * @param array $words
     * @param float $threshold
     * @return SyntaxGroup
     */
    public function analyze(array $words, float $threshold = 0.80): array {
        $possibilities = [];
        foreach ($words as $key => $word) {
            $completeSets = [];
            /**
             * @var $word Word[]
             */
            foreach ($possibilities as $pkey => $possibility) {
                /**
                 * @var $possibility SyntaxMatch
                 */
                if ($possibility->isFinished())
                    continue;
                if ($possibility->getPositionTo() != $key)
                    continue;
                $element = $possibility->getRule()->getCurrentElement();
                $match = [];
                if (!preg_match("/\\{SE:([A-Z]+)\\}/", $element, $match)||!WordType::isValidType($match[1]))
                    continue;
                try {
                    foreach ($word as $meaning) {
                        if ($match[1] != $meaning->getWordType() && $meaning->getWordType() != WordType::Unrecognized)
                            continue;
                        $possibility->getGroup()->addChild($meaning);
                        $possibility->getRule()->moveToNextElement();
                        break;
                    }
                }
                catch (NoMoreElementsInRuleException $exp){
                    if ($possibility->isFinished()) {
                        $completeSets = array_merge($completeSets, $this->matchFinishProcedure($possibility, $possibilities));
                    }
                }
            }
            foreach ($this->grammarRules as $schedulerRule) {
                /**
                 * @var $rule SyntaxRule
                 */
                $schedulerRule->resetPosition();
                $schedulerRule->moveToNextElement();
                $element = $schedulerRule->getCurrentElement();
                $match = [];
                if (preg_match("/\\{SE:([A-Za-z]+)\\}/", $element, $match)&&WordType::isValidType($match[1])){
                    foreach ($word as $meaning) {
                        if ($match[1] != $meaning->getWordType() && $meaning->getWordType() != WordType::Unrecognized)
                            continue;
                        $rule = clone $schedulerRule;
                        $group = new SyntaxGroup($rule->getType());
                        $group->addChild($meaning);
                        $possibility = new SyntaxMatch($key, $rule, $group);
                        if (!$possibility->isFinished())
                            $possibility->getRule()->moveToNextElement();
                        else
                            $completeSets = array_merge($completeSets, $this->matchFinishProcedure($possibility, $possibilities));
                        $possibilities[] = $possibility;
                    }
                }
            }
            foreach ($this->grammarRules as $schedulerRule) {
                /**
                 * @var $rule SyntaxRule
                 */
                $schedulerRule->resetPosition();
                $schedulerRule->moveToNextElement();
                $element = $schedulerRule->getCurrentElement();
                $match = [];
                if (preg_match("/\\{SG:([A-Z]+)\\}/", $element, $match)&&SyntaxGroupType::isSyntaxGroup($match[1])) {
                    foreach ($completeSets as $set) {
                        /**
                         * @var $set SyntaxMatch
                         */
                        if ($match[1] != $set->getGroup()->getType())
                            continue;
                        $rule = clone $schedulerRule;
                        $group = new SyntaxGroup($rule->getType());
                        $group->addChild($set->getGroup());
                        $possibility = new SyntaxMatch($set->getPositionFrom(), $rule, $group);
                        if (!$possibility->isFinished())
                            $possibility->getRule()->moveToNextElement();
                        else
                            $completeSets = array_merge($completeSets, $this->matchFinishProcedure($possibility, $possibilities));
                        $possibilities[] = $possibility;
                    }
                }
            }
        }
        $results = [];
        $length = count($words);
        foreach ($possibilities as $possibility) {
            $similarity = ($possibility->getGroup()->getChildCount() / $length);
            if ($similarity > $threshold&&$possibility->isFinished())
                $results[] = $possibility->getGroup();
        }
        return $results;
    }
}