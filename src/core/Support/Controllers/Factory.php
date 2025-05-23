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

            $mainViews = BASE_PATH . '/resources';
            $monitoringView = BASE_PATH . '/core/Panel/resources';
            $cache = BASE_PATH . '/storage/framework/views';

            if (!is_dir($cache)) {
                mkdir($cache, 0777, true);
            }

            self::$blade = new Blade([$mainViews, $monitoringView], $cache);
        }
    }

    private static function injectMonitoringPanel(string $content): string
    {
        $monitoringData = Monitoring::check();
        $panelHtml = self::$blade->render('views/panel', ['monitoring' => $monitoringData]);

        return preg_replace('/<body[^>]*>/', '$0' . $panelHtml, $content, 1) ?: $panelHtml . $content;
    }


    public static function renderView($view, $data = [])
    {
        self::initBlade();

        $pathView = 'views/' . $view;

        try {
            $content = self::$blade->render($pathView, $data);

            if (EnvValidator::isDebugEnabled()) {
                $content = self::injectMonitoringPanel($content);
            }

            return $content;
        } catch (\InvalidArgumentException $e) {
            throw new \Exception("La vista '{$view}' no fue encontrada.");
        }
    }
}
