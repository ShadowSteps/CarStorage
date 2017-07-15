<?php
/**
 * Created by PhpStorm.
 * User: John
 * Date: 27.12.2016 г.
 * Time: 15:43
 */

namespace Shadows\CarStorage\Core\Utils;


class HashHelper
{
    public static function SHA256(string $string): string {
        return hash('sha256', $string);
    }
}