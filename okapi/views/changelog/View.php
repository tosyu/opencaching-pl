<?php

namespace okapi\views\changelog;

use okapi\core\Okapi;
use okapi\core\Response\OkapiHttpResponse;
use okapi\Settings;
use okapi\views\menu\OkapiMenu;

class View
{
    public static function call()
    {
        $changelog = new ChangelogHelper();

        $vars = array(
            'menu' => OkapiMenu::get_menu_html("changelog.html"),
            'okapi_base_url' => Settings::get('SITE_URL')."okapi/",
            'site_url' => Settings::get('SITE_URL'),
            'installations' => OkapiMenu::get_installations(),
            'okapi_rev' => Okapi::getVersionNumber(),
            'site_name' => Okapi::get_normalized_site_name(),
            'changes' => array(
                'unavailable' => $changelog->unavailable_changes,
                'available' => $changelog->available_changes
            ),
        );

        $response = new OkapiHttpResponse();
        $response->content_type = "text/html; charset=utf-8";
        ob_start();
        require_once __DIR__ . '/changelog.tpl.php';
        $response->body = ob_get_clean();
        return $response;
    }
}
