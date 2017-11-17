<?php
declare(strict_types=1);

use Phinx\Seed\AbstractSeed;

class Users extends AbstractSeed
{
    public function run()
    {
        $this->table('users')
            ->insert([
                'id' => 'admin',
                'pass' => '',
                'name' => '관리자',
                'email' => 'admin@ridi.com',
                'team' => '',
                'on_date' => '2000-01-01',
                'off_date' => '9999-01-01',
                'is_admin' => true,
            ])
            ->save();
    }
}
