<?php

namespace Core\Support\Controllers;

use Core\Panel\Monitoring;
use Jenssegers\Blade\Blade;
use Core\Boot\Enviroment\EnvValidator;

class Factory
{
    private static $blade = null;

    private static function initBlade()
    {
        if (is_null(self::$blade)) {

            // Folder views
            $mainViews = BASE_PATH . '/resources';
            $monitoringView = BASE_PATH . '/vendor/syverum/framework/src/core/Panel/resources';
            $cache = BASE_PATH . '/storage/framework/views';

            if (!is_dir($cache)) {
                mkdir($cache, 0777, true);
            }

            // Blade will look for views in these two paths
            self::$blade = new Blade([$mainViews, $monitoringView], $cache);
        }
    }

    private static function injectMonitoringPanel(string $content): string
    {
        $monitoringData = Monitoring::check();

        // Render panel view
        $panelHtml = self::$blade->render('views.panel', ['monitoring' => $monitoringData]);

        // Inject Panel after the body
        return preg_replace('/<body[^>]*>/', '$0' . $panelHtml, $content, 1) ?: $panelHtml . $content;
    }

    public static function renderView($view, $data = [])
    {
        self::initBlade();

        try {
            // Render requested view
            $content = self::$blade->render('views/'.$view, $data);

            // Inject panel if APP_DEBUG=true
            if (EnvValidator::isDebugEnabled() === true) {
                $content = self::injectMonitoringPanel($content);
            }

            return $content;

        } catch (\InvalidArgumentException $e) {
            throw new \Exception("La vista '{$view}' no fue encontrada.", 0, $e);
        }
    }
}
