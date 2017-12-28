<?php

namespace Intra\Controller;

use Intra\Core\JsonDto;
use Intra\Model\PostModel;
use Intra\Service\Post\Post;
use Intra\Service\Post\PostDetailDto;
use Intra\Service\Post\PostListDto;
use Intra\Service\User\UserPolicy;
use Intra\Service\User\UserSession;
use Silex\Api\ControllerProviderInterface;
use Silex\Application;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class PostsController implements ControllerProviderInterface
{
    public function connect(Application $app)
    {
        $controller_collection = $app['controllers_factory'];
        $controller_collection->get('/{group}', [$this, 'index']);
        $controller_collection->get('/{group}/write', [$this, 'write']);
        $controller_collection->post('/{group}/write', [$this, 'writeAjax']);
        $controller_collection->get('/{group}/sendAll', [$this, 'sendAll']);
        $controller_collection->get('/{group}/{id}/modify', [$this, 'modify']);
        $controller_collection->post('/{group}/{id}/modify', [$this, 'modifyAjax']);
        $controller_collection->delete('/{group}/{id}', [$this, 'delete']);
        $controller_collection->get('/{group}/{id}', [$this, 'view']);

        return $controller_collection;
    }

    public function index(Request $request, Application $app)
    {
        $group = $request->get('group');

        $post_list_view = PostListDto::import($group);
        $listViewParam = $post_list_view->exportAsArrayForListView();
        $listViewParam['isPostAdmin'] = UserPolicy::isPostAdmin(UserSession::getSelfDto());

        return $app['twig']->render('posts/index.twig', $listViewParam);
    }

    public function write(Request $request, Application $app)
    {
        $group = $request->get('group');

        return $app['twig']->render('posts/write.twig', ['group' => $group]);
    }

    public function writeAjax(Request $request, Application $app)
    {
        $jsonDto = new JsonDto();
        try {
            $post = new Post();
            $post->add($request);
            $jsonDto->setMsg('등록되었습니다.');
        } catch (Exception $e) {
            $jsonDto->setException($e);
        }

        return json_encode((array)$jsonDto);
    }

    public function sendAll(Request $request, Application $app)
    {
        $group = $request->get('group');

        $post = new Post();
        if ($post->sendAll($group)) {
            return Response::create('발송되었습니다', Response::HTTP_OK);
        }

        return Response::create('발송실패', Response::HTTP_OK);
    }

    public function modify(Request $request, Application $app)
    {
        $group = $request->get('group');
        $id = $request->get('id');

        $post_list_view = PostDetailDto::importFromModel(PostModel::on()->find($id));
        $modifyViewParam = $post_list_view->exportAsArrayForModify();

        return $app['twig']->render('posts/modify.twig', $modifyViewParam);
    }

    public function modifyAjax(Request $request, Application $app)
    {
        $jsonDto = new JsonDto();
        try {
            $post = new Post();
            $post->modify($request);
            $jsonDto->setMsg('수정되었습니다.');
        } catch (Exception $e) {
            $jsonDto->setException($e);
        }

        return JsonResponse::create($jsonDto);
    }

    public function delete(Request $request, Application $app)
    {
        $jsonDto = new JsonDto();
        try {
            $post = new Post();
            if ($post->del($request)) {
                $jsonDto->setMsg('삭제되었습니다.');
            } else {
                $jsonDto->success = 0;
                $jsonDto->setMsg('삭제가 되지 않았습니다. 플랫폼팀에 문의해주세요');
            }
        } catch (Exception $e) {
            $jsonDto->setException($e);
        }

        return JsonResponse::create($jsonDto);
    }

    public function view(Request $request, Application $app)
    {
        $group = $request->get('group');
        $id = $request->get('id');

        $post_list_view = PostDetailDto::importFromModel(PostModel::on()->find($id));
        $detailViewParam = $post_list_view->exportAsArrayForDetailView();
        $detailViewParam['isPostAdmin'] = UserPolicy::isPostAdmin(UserSession::getSelfDto());

        return $app['twig']->render('posts/view.twig', $detailViewParam);
    }
}
