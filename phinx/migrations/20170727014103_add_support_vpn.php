<?php
declare(strict_types=1);

use Phinx\Db\Adapter\MysqlAdapter;
use Phinx\Migration\AbstractMigration;

class AddSupportVpn extends AbstractMigration
{
    public function change()
    {
        $this->table('support_vpn')
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
            ->addColumn('is_completed', 'integer', ['limit' => MysqlAdapter::INT_TINY,
                'comment' => '인사팀 승인여부'])
            ->addColumn('completed_uid', 'integer', ['signed' => false,
                'comment' => '인사팀 승인 user id'])
            ->addColumn('completed_datetime', 'datetime', [
                'comment' => '인사팀 승인 시각'])
            ->addColumn('vpn_usage_type', 'string', ['limit' => 32,
                'comment' => 'VPN 사용 기간 종류'])
            ->addColumn('vpn_start_date', 'datetime', [
                'comment' => 'VPN 사용 시작일'])
            ->addColumn('vpn_end_date', 'datetime', [
                'comment' => 'VPN 사용 종료일'])
            ->addColumn('is_deleted', 'integer', ['limit' => MysqlAdapter::INT_TINY,
                'comment' => '레코드 삭제 여부'])
            ->create();
    }
}
