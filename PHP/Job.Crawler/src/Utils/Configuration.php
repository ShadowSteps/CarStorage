<?php
/**
 * Created by PhpStorm.
 * User: John
 * Date: 27.12.2016 г.
 * Time: 19:54
 */

namespace Shadows\CarStorage\Crawler\Utils;


class Configuration
{
    public static function ControlApiUrl(): string{
        return ConstConfigHelper::GetStringParameter("CONTROL_API_URL");
    }

    public static function SolrApiUrl(): string{
        return ConstConfigHelper::GetStringParameter("SOLR_API_URL");
    }

    public static function AvailablePlugins() : array{
        return ConstConfigHelper::GetArrayParameter("AVAILABLE_PLUGINS");
    }
}