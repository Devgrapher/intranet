<?php
declare(strict_types=1);

use Phinx\Seed\AbstractSeed;

class Rooms extends AbstractSeed
{
    public function run()
    {
        $room_names = [
            '10-1(4명)',
            '10-2(8명)',
            '10-3(8명)',
            '10-4(4명)',
            '11-1(4명)',
            '11-2(4명)',
            '11-3(4명)',
            '11-4(4명)',
            '11-5(10명)'
        ];

        $data = [];
        for ($i = 0; $i < count($room_names); ++$i) {
            $data[] = [
                'type' => 'default',
                'name' => $room_names[$i],
                'is_visible' => true,
            ];
        }

        $this->table('rooms')
            ->insert($data)
            ->save();
    }
}
