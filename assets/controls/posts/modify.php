<?php
/** @var $this Intra\Core\Control */
use Intra\Model\PostModel;
use Intra\Service\Post\PostDetailDto;

$request = $this->getRequest();
$group = $request->get('group');
$id = $request->get('id');

$post_list_view = PostDetailDto::importFromModel(PostModel::on()->find($id));
return $post_list_view->exportAsArrayForModify();
