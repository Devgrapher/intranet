<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

class AddPaymentTeamDetailColumn extends AbstractMigration
{
    public function change()
    {
        $this->table('payments')
            ->addColumn(
                'team_detail',
                'string',
                ['length' => 200, 'comment' => '팀 세부 분류', 'default' => ''])
            ->save();
    }
}
