<?php
namespace Intra\Service\User;

use Intra\Core\BaseDto;
use Symfony\Component\HttpFoundation\Request;

class UserDto extends BaseDto
{
    public $uid;
    public $id;
    public $name;
    public $email;
    public $team;
    public $team_detail;
    public $position;
    public $outer_call;
    public $inner_call;
    public $mobile;
    public $birth;
    public $image;
    public $on_date;
    public $off_date;
    public $personcode;
    public $ridibooks_id;
    public $is_admin;
    /**
     * @var []
     */
    public $extra;
    public $comment;

    public static function importFromDatabase(array $user_row)
    {
        $return = new self();
        if ($user_row) {
            $obj = json_decode($user_row['extra']);
            if (is_object($obj)) {
                $user_row['extra'] = @get_object_vars($obj);
            } else {
                $user_row['extra'] = [];
            }
        }
        $return->initFromArray($user_row);

        return $return;
    }

    /**
     * @param $request Request
     * @return UserDto
     */
    public static function importFromJoinRequest($request)
    {
        $return = new self();
        $keys = ['name', 'email', 'mobile', 'birth'];
        foreach ($keys as $key) {
            $return->$key = $request->get($key);
        }
        $return->id = preg_replace('/@.+/', '', $return->email);

        return $return;
    }

    public function exportExtraForDatabase()
    {
        return ['extra' => json_encode($this->extra)];
    }

    public function exportDatabaseForJoin()
    {
        return $this->exportAsArrayExceptNull();
    }

    public function exportForDatabaseOnlyKeys($keys)
    {
        $return = [];
        foreach ($keys as $key) {
            $return[$key] = $this->$key;
        }

        return $return;
    }
}
