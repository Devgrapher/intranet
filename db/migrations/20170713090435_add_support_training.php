<?php

use Phinx\Db\Adapter\MysqlAdapter;
use Phinx\Migration\AbstractMigration;

class AddSupportTraining extends AbstractMigration
{
    public function change()
    {
        $this->table('support_training')
            ->addColumn('uuid', 'string', ['limit' => 32,
                'comment' => '일련번호'])
            ->addColumn('reg_date', 'datetime', ['default' => 'CURRENT_TIMESTAMP',
                'comment' => '등록일'])
            ->addColumn('uid', 'integer', ['signed' => false, 'null' => false,
                'comment' => 'user id'])
            ->addColumn('is_accepted', 'integer', ['limit' => MysqlAdapter::INT_TINY,
                'comment' => '승인여부'])
            ->addColumn('accept_uid', 'integer', ['signed' => false,
                'comment' => '승인자'])
            ->addColumn('accepted_datetime', 'datetime', [
                'comment' => '승인 시각'])
            ->addColumn('support_rate', 'string', ['limit' => 8,
                'comment' => '승인지원율'])
            ->addColumn('cost', 'integer', ['signed' => false,
                'comment' => '수강료'])
            ->addColumn('provider', 'string', ['limit' => 255,
                'comment' => '기관'])
            ->addColumn('training_name', 'string', ['limit' => 255,
                'comment' => '강의명'])
            ->addColumn('training_date', 'string', ['limit' => 255,
                'comment' => '일시'])
            ->addColumn('purpose', 'string', ['limit' => 255,
                'comment' => '수강목적'])
            ->addColumn('link', 'string', ['limit' => 65536,
                'comment' => '링크'])
            ->addColumn('date', 'datetime', [
                'comment' => '예정일'])
            ->addColumn('is_deleted', 'integer', ['limit' => MysqlAdapter::INT_TINY,
                'comment' => '레코드 삭제 여부'])
            ->create();
    }
}
