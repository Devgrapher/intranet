<?php
/** @var $this Intra\Core\Control */

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Schema\Blueprint;
use Intra\Service\UserSession;

$response = $this->getResponse();
$response->add(
	array('isSuperAdmin' => UserSession::getSelf()->isSuperAdmin())
);

$schema = Capsule::schema();
if (!$schema->hasTable('posts')) {
	Capsule::schema()->create(
		'posts',
		function (Blueprint $table) {
			$table->increments('id');
			$table->string('group', 20);
			$table->string('title', 200);
			$table->integer('uid');
			$table->boolean('is_sent');
			$table->text('content_html');
			$table->timestamps();
			$table->softDeletes();
		}
	);
}