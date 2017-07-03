<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

class AddTeams extends AbstractMigration
{
    public function change()
    {
        $this->table('teams')
            ->addColumn('name', 'string', ['limit' => 255,
                'comment' => '팀 이름'])
            ->addColumn('alias', 'string', ['limit' => 32,
                'comment' => '약자'])
            ->addTimestamps()
            ->create();
    }
}
