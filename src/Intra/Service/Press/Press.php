<?php
namespace Intra\Service\Press;

use Intra\Core\JsonDto;
use Intra\Repository\PressRepository as PressRepository;
use Intra\Service\User\UserDto;
use Intra\Service\User\UserSession;

class Press
{
    private $user;
    private $press;

    /**
     * @param $user UserDto
     */
    public function __construct($user, $press = null)
    {
        if ($press == null) {
            $press = new PressRepository();
        }
        $this->user = $user;
        $this->press = $press;
    }

    public function index()
    {
        $press = $this->getAll();

        return $this->makeJsonResponse($press);
    }

    public function add($date, $media, $title, $link_url, $note)
    {
        try {
            $data = [
                'date' => $date,
                'media' => $media,
                'title' => $title,
                'link_url' => $link_url,
                'note' => $note
            ];

            $this->press->create($data);
        } catch (\Exception $e) {
            return '자료를 추가할 수 없습니다. 다시 확인해 주세요';
        }

        return true;
    }

    public function del($id)
    {
        try {
            $this->press->delete($id);
        } catch (\Exception $e) {
            return '삭제가 실패했습니다!';
        }

        return true;
    }

    public function edit($id, $key, $value)
    {
        try {
            $this->press->update([$key => $value], $id);
            return $value;
        } catch (\Exception $e) {
            return '수정을 실패했습니다!';
        }
    }

    public function getAll()
    {
        try {
            return $this->press->all();
        } catch (\Exception $e) {
            return '데이터 불러오기를 실패했습니다!';
        }
    }

    public function getAllPress()
    {
        $press = $this->getAll();

        return $this->makeJsonResponse($press);
    }

    public function getPressByPage($page, $take)
    {
        $skip = ($page - 1) * $take;

        $press = $this->press->paginate($take, $skip);

        $count = $this->press->count();

        return $this->makeJsonResponse($press, $count);
    }

    private function makeJsonResponse($press, $count = null)
    {
        $json_dto = new JsonDto();

        $json_dto->data = [
            'user' => $this->user,
            'press' => $press,
            'manager' => UserSession::isPressManager()
        ];

        if (!is_null($count)) {
            $json_dto->data['count'] = $count;
        }

        return json_encode(
            (array)$json_dto
        );
    }
}
