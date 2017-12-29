<?php
declare(strict_types=1);

use Intra\Core\WebpackManifestVersionStrategy;
use Intra\Service\Menu\MenuService;
use Silex\Application;
use Silex\Provider\TwigServiceProvider;
use Symfony\Component\Asset\PathPackage;

$app = new Application([
    'debug' => $_ENV['INTRA_DEBUG'] ?? false,
    'asset_manifest_path' => __DIR__ . '/../web/static/dist/manifest.json',
    'asset_public_path' => '/static/dist',
]);

$app->register(new TwigServiceProvider(), [
    'twig.options' => [
        'cache' => __DIR__ . '/../var/cache',
        'auto_reload' => true,
    ],
    'twig.path' => [
        __DIR__ . '/../assets/views',
    ],
]);

$app->extend('twig', function (Twig_Environment $twig) use ($app) {
    $version_strategy = new WebpackManifestVersionStrategy($app['asset_manifest_path']);
    $asset_package = new PathPackage($app['asset_public_path'], $version_strategy);
    $twig->addFunction(new Twig_SimpleFunction('asset', function ($asset_name) use ($asset_package) {
        return $asset_package->getUrl($asset_name);
    }));

    $menu_list = MenuService::getMenuLinkList();
    $twig->addGlobal('leftMenuList', $menu_list['left']);
    $twig->addGlobal('rightMenuList', $menu_list['right']);
    $twig->addGlobal('globalDomain', $_ENV['INTRA_DOMAIN']);
    $twig->addGlobal('sentryPublicKey', $_ENV['SENTRY_PUBLIC_KEY']);

    $twig->addFunction(new Twig_SimpleFunction('printMenuList', function ($menu_list) {
        $html = '';
        if (is_array($menu_list)) {
            foreach ($menu_list as $menu) {
                $html .= $menu->getHtml();
            }
        } else {
            $html = $menu_list->getHtml();
        }

        return new Twig_Markup($html, 'UTF-8');
    }));

    return $twig;
});

return $app;
