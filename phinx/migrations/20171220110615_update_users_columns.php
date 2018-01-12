<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

class UpdateUsersColumns extends AbstractMigration
{
    public function change()
    {
        $this->table('users')
            ->removeColumn('team_detail')
            ->removeColumn('outer_call')
            ->addColumn('name_en', 'string', [
                'null' => true,
                'comment' => '영문이름'
            ])
            ->addColumn('trainee_off_date', 'date', [
                'null' => true,
                'comment' => '수습종료일'
            ])
            ->addColumn('military_service', 'enum', [
                'null' => true,
                'comment' => '병역사항',
                'values' => [null, '병역필', '산업기능요원' , '전문연구요원'],
            ])
            ->save();
    }
}
