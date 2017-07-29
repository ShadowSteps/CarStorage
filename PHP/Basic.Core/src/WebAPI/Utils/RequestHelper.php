<?php

namespace AdSearchEngine\Core\WebAPI\Utils;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class RequestHelper
{
    public static function GetJsonStdFromRequest(Request $request) : \stdClass {
        $content = $request->getContent();
        if (strlen($content) <= 0)
            throw new BadRequestHttpException("Empty request content!");
        $std = json_decode($content);
        if (!$std)
            throw new BadRequestHttpException("Request is not in json format!");
        return $std;
    }
}