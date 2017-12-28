<?php
namespace Intra\Service\Post;

use Illuminate\Database\Capsule\Manager as Capsule;
use Intra\Core\MsgException;
use Intra\Model\PostModel;
use Intra\Service\User\UserPolicy;
use Intra\Service\User\UserSession;
use Mailgun\Mailgun;
use Symfony\Component\HttpFoundation\Request;

class Post
{
    public function add($request)
    {
        $postDto = PostDetailDto::importFromWriteRequest($request);
        $this->assertAdd($postDto);
        /**
         * @var PostModel
         */
        $post = $postDto->exportAsModelForInsertDb();
        $post->save();
    }

    private function assertAdd($post_list_view)
    {
        if (!UserPolicy::isPostAdmin(UserSession::getSelfDto())) {
            throw new MsgException('권한이 없습니다');
        }
    }

    public function modify($request)
    {
        $postDto = PostDetailDto::importFromWriteRequest($request);
        $this->assertModify();

        $post = PostModel::on()->find($postDto->id);
        $post->update($postDto->exportAsArrayForModify());
        $post->save();
    }

    private function assertModify()
    {
        if (!UserPolicy::isPostAdmin(UserSession::getSelfDto())) {
            throw new MsgException('권한이 없습니다');
        }
    }

    public function sendAll($group)
    {
        $result = false;
        Capsule::connection()->transaction(
            function () use (&$result, $group) {
                $posts = PostModel::on()->where('group', $group)->where('is_sent', 0)->get();
                $mail_title = '[공지] ' . date('Y/m/d') . '의 공지사항입니다.';
                $mail_bodys = [];
                foreach ($posts as $post) {
                    $mail_body = "<fieldset style='margin: 20px'><legend>{$post->title}</legend>" . $post->content_html . "</fieldset>";
                    $mail_bodys[] = $mail_body;
                }
                $mail_bodys = implode("", $mail_bodys);

                $receivers = [];
                $receivers[] = 'everyone@' . $_ENV['INTRA_DOMAIN'];

                $mg = new Mailgun($_ENV['MAILGUN_API_KEY']);
                $domain = "ridi.com";
                $result = $mg->sendMessage(
                    $domain,
                    [
                        'from' => $_ENV['MAILGUN_FROM'],
                        'to' => implode(', ', $receivers),
                        'subject' => $mail_title,
                        'text' => strip_tags($mail_bodys),
                        'html' => nl2br($mail_bodys),
                    ]
                );
                foreach ($posts as $post) {
                    $post->is_sent = true;
                    $post->save();
                }
                $result = true;
            }
        );

        return $result;
    }

    /**
     * @param $request Request
     * @return bool|mixed|null
     * @throws MsgException
     */
    public function del($request)
    {
        $id = $request->get('id');
        $this->assertModify();

        $post = PostModel::on()->find($id);

        return $post->delete();
    }
}
