<?php
declare(strict_types=1);

use Phinx\Seed\AbstractSeed;

class Posts extends AbstractSeed
{
    const POST_NUM = 20;

    public function run()
    {
        $posts = [];

        $faker = Faker\Factory::create('ko_KR');
        for ($i = 0; $i < self::POST_NUM; $i++) {
            $created = $faker->dateTimeBetween('-1 Years', 'now')->format('Y-m-d H:i:s');
            $posts[] = [
                'group' => 'notice',
                'title' => $faker->sentence(),
                'uid' => 1,
                'is_sent' => 1,
                'content_html' => $faker->text(),
                'created_at' => $created,
                'updated_at' => $created,
            ];
        }

        usort($posts, function ($a, $b) {
            return $a['created_at'] > $b['created_at'];
        });

        $posts[self::POST_NUM-1]['created_at'] = date('Y-m-d H:i:s');

        $this->table('posts')
            ->insert($posts)
            ->save();
    }
}
