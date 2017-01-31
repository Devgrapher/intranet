<?php
/** @var $this Intra\Core\Control */
use Illuminate\Database\Capsule\Manager as Capsule;
use Intra\Model\PostModel;
use Intra\Service\User\UserPolicy;
use Intra\Service\User\UserSession;

$response = $this->getResponse();
$response->add(
    ['isPostAdmin' => UserPolicy::isPostAdmin(UserSession::getSelfDto())]
);

$schema = Capsule::schema();
if (!$schema->hasTable('posts')) {
    PostModel::init();
}
