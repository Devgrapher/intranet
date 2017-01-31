<?php
/** @var $this Intra\Core\Control */
use Intra\Service\Menu\MenuService;
use Intra\Service\User\UserPolicy;

$request = $this->getRequest();

$response = UserPolicy::assertRestrictedPath($request);
if ($response) {
    return $response;
}

list($left_menu_list, $right_menu_list) = MenuService::getMenuLinkList();

$response = $this->getResponse();
$response->add(
    [
        'globalDomain' => $_ENV['domain'],
        'leftMenuList' => $left_menu_list,
        'rightMenuList' => $right_menu_list,
        'sentryPublicKey' => $_ENV['sentry_public_key'],
    ]
);
