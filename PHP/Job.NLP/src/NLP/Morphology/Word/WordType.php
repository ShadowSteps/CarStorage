<?php
/**
 * Created by PhpStorm.
 * User: John
 * Date: 30.12.2016 г.
 * Time: 18:42
 */

namespace Shadows\CarStorage\NLP\NLP\Morphology\Word;


class WordType
{
    const Noun = "N"; //Съществително
    const Adjective = "A"; //Прилагателно
    const Adverb = "ADV"; //Наречие
    const Verb = "V"; //Глагол
    const Pronoun = "PRO"; //Местоимение
    const Numeral = "NU"; //Числително
    const Preposition = "PREP"; //Предлог
    const Conjunction = "CONJ"; //Съюз
    const Particle = "PC"; //Частица
    const Interjection = "INTJ"; //Междуметие
    const Unrecognized = "U"; //Междуметие

    private static $constArray = [];

    private static function init() {
        $reflection = new \ReflectionClass(get_called_class());
        self::$constArray = $reflection->getConstants();
    }

    public static function isValidType(string $type): bool {
        if (!count(self::$constArray))
            self::init();
        return in_array($type, self::$constArray);
    }
}