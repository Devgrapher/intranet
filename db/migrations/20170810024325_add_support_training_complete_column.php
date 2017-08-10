<?php

use Phinx\Db\Adapter\MysqlAdapter;
use Phinx\Migration\AbstractMigration;

class AddSupportTrainingCompleteColumn extends AbstractMigration
{
    public function change()
    {
        $this->table('support_training')
            ->addColumn('is_completed', 'integer', ['limit' => MysqlAdapter::INT_TINY,
                'comment' => 'CO팀 승인여부'])
            ->addColumn('completed_uid', 'integer', ['signed' => false,
                'comment' => 'CO팀 승인 user id'])
            ->addColumn('completed_datetime', 'datetime', [
                'comment' => 'CO팀 승인 시각'])
            ->save();
    }
}
