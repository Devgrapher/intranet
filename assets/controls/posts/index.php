<?php
/** @var $this Intra\Core\Control */
use Intra\Service\Post\PostListDto;

$request = $this->getRequest();
$group = $request->get('group');

$post_list_view = PostListDto::import($group);
return $post_list_view->exportAsArrayForListView();
