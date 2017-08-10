<?php
/**
 * Created by PhpStorm.
 * User: John
 * Date: 27.12.2016 г.
 * Time: 20:32
 */

namespace AdSearchEngine\Core\Crawler\Utils;


use AdSearchEngine\Core\Crawler\Exception\XPathElementNotFoundException;

class XPathHelper
{
    public static function FindChildElementList($tagName, $parentNode, \DOMXPath $path) {
        $list = $path->query('./' . $tagName, $parentNode);
        if ($list->length <= 0)
            throw new XPathElementNotFoundException("No elements found!");
        return $list;
    }

    public static function FindChildElement($tagName, $parentNode, \DOMXPath $path, $elementNumber = 0) {
        $list = self::FindChildElementList($tagName, $parentNode, $path);
        if ($list->length <= $elementNumber)
            throw new XPathElementNotFoundException("Element with index $elementNumber not found!");
        return $list->item($elementNumber);
    }

    public static function FindChildElementListByClass($element, $className, $parent, \DOMXPath $path)
    {
        $list = $path->query('./' . $element.'[contains(concat(\' \', normalize-space(@class), \' \'), \' '.$className.' \')]', $parent);
        if ($list->length <= 0)
            throw new XPathElementNotFoundException("No elements found!");
        return $list;
    }

    public static function FindChildElementByClass($element, $className, $parent, \DOMXPath $path, $elementNumber = 0)
    {
        $list = self::FindChildElementListByClass($element, $className, $parent, $path);
        if ($list->length <= $elementNumber)
            throw new XPathElementNotFoundException("Element with index $elementNumber not found!");
        return $list->item($elementNumber);
    }

    public static function FindElementList($tagName, $parentNode, \DOMXPath $path) {
        $list = $path->query('.//' . $tagName, $parentNode);
        if ($list->length <= 0)
            throw new XPathElementNotFoundException("No elements found!");
        return $list;
    }

    public static function FindElement($tagName, $parentNode, \DOMXPath $path, $elementNumber = 0) {
        $list = self::FindElementList($tagName, $parentNode, $path);
        if ($list->length <= $elementNumber)
            throw new XPathElementNotFoundException("Element with index $elementNumber not found!");
        return $list->item($elementNumber);
    }

    public static function FindElementListByAttributeValue($tagName, $attribute, $value, $parentNode, \DOMXPath $path) {
        $list = $path->query('.//' . $tagName . "[@$attribute='$value']", $parentNode);
        if ($list->length <= 0)
            throw new XPathElementNotFoundException("No elements found!");
        return $list;
    }

    public static function FindElementByAttributeValue($tagName, $attribute, $value, $parentNode, \DOMXPath $path, $elementNumber = 0) {
        $list = self::FindElementListByAttributeValue($tagName, $attribute, $value, $parentNode, $path);
        if ($list->length <= $elementNumber)
            throw new XPathElementNotFoundException("Element with index $elementNumber not found!");
        return $list->item($elementNumber);
    }

    public static function FindElementListByClass($element, $className, $parent, \DOMXPath $path)
    {
        $list = $path->query('.//' . $element.'[contains(concat(\' \', normalize-space(@class), \' \'), \' '.$className.' \')]', $parent);
        if ($list->length <= 0)
            throw new XPathElementNotFoundException("No elements found!");
        return $list;
    }

    public static function FindElementByClass($element, $className, $parent, \DOMXPath $path, $elementNumber = 0)
    {
        $list = self::FindElementListByClass($element, $className, $parent, $path);
        if ($list->length <= $elementNumber)
            throw new XPathElementNotFoundException("Element with index $elementNumber not found!");
        return $list->item($elementNumber);
    }

}