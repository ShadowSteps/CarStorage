<?php
/**
 * Created by PhpStorm.
 * User: John
 * Date: 27.12.2016 г.
 * Time: 19:54
 */

namespace CarStorage\Crawler\Utils;


class Configuration
{
    public static function ControlApiUrl(): string{
        return ConstConfigHelper::GetStringParameter("CONTROL_API_URL");
    }

    public static function FeaturesCacheFile(): string{
        return ConstConfigHelper::GetStringParameter("ML_FEATURES_CACHE");
    }

    public static function ClusterCentroidsCacheFile(): string{
        return ConstConfigHelper::GetStringParameter("ML_CENTROIDS_CACHE");
    }

    public static function AvailablePlugins() : array{
        return ConstConfigHelper::GetArrayParameter("AVAILABLE_PLUGINS");
    }

    public static function AuthenticationToken() : string{
        return ConstConfigHelper::GetStringParameter("AUTH_TOKEN");
    }
}