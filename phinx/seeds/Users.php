<?php
declare(strict_types=1);

use Phinx\Seed\AbstractSeed;

class Users extends AbstractSeed
{
    const USER_NUM_PER_TEAM = 5;

    public function run()
    {
        $users = [
            [
                'id' => 'admin',
                'pass' => '',
                'name' => '관리자',
                'email' => 'admin@ridi.com',
                'team' => '',
                'on_date' => '2000-01-01',
                'off_date' => '9999-01-01',
                'is_admin' => true,
            ],
        ];

        $faker = Faker\Factory::create('ko_KR');
        foreach (Teams::TEAM_LIST as $team) {
            $users = array_merge($users, $this->createTeamLeaders($faker, $team['name']));
            $users = array_merge($users, $this->createNormalUsers($faker, self::USER_NUM_PER_TEAM, $team['name']));
        }

        $this->table('users')
            ->insert($users)
            ->save();
    }

    public function createNormalUsers($faker, $num, $team)
    {
        $users = [];
        for ($i = 0; $i < $num; $i++) {
            $users[] = [
                'id' => $faker->userName,
                'pass' => '',
                'name' => $faker->name,
                'email' => $faker->email,
                'team' => $team,
                'on_date' => '2000-01-01',
                'off_date' => '9999-01-01',
                'is_admin' => 0,
            ];
        }
        return $users;
    }

    public function createTeamLeaders($faker, $team)
    {
        return [
            [
                'id' => $faker->userName,
                'pass' => '',
                'name' => $faker->name,
                'email' => $faker->email,
                'team' => $team,
                'position' => '팀장',
                'on_date' => '2000-01-01',
                'off_date' => '9999-01-01',
                'is_admin' => 0,
            ]
        ];
    }
}
