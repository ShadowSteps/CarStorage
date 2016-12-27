<?php
/**
 * Created by PhpStorm.
 * User: John
 * Date: 27.12.2016 г.
 * Time: 20:32
 */

namespace Shadows\CarStorage\Crawler\Utils;


class XPathHelper
{
    public static function FindElementListByClass($element, $className, $parent, \DOMXPath $path)
    {
        $list = $path->query('.//' . $element . '[@class="' . $className . '"]', $parent);
        return $list;
    }

    public static function FindElementByClass($element, $className, $parent, \DOMXPath $path)
    {
        $list = $path->query('.//' . $element . '[@class="' . $className . '"]', $parent);
        return $list->item(0);
    }

    public static function FindElementList($element, $parent, \DOMXPath $path)
    {
        $list = $path->query('.//' . $element, $parent);
        return $list;
    }

    public static function FindElement($element, $parent, \DOMXPath $path)
    {
        $list = $path->query('.//' . $element, $parent);
        return $list->item(0);
    }

}