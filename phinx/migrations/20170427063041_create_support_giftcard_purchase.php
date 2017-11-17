<?php
declare(strict_types=1);

use Phinx\Db\Adapter\MysqlAdapter;
use Phinx\Migration\AbstractMigration;

class CreateSupportGiftcardPurchase extends AbstractMigration
{
    public function change()
    {
        $this->table('support_gift_card_purchase')
            ->addColumn('uuid', 'string', ['limit' => 32,
                'comment' => '일련번호'])
            ->addColumn('reg_date', 'datetime', ['default' => 'CURRENT_TIMESTAMP',
                'comment' => '등록일'])
            ->addColumn('uid', 'integer', ['signed' => false, 'null' => false,
                'comment' => 'user id'])
            ->addColumn('is_approved_by_cashflow', 'integer', ['limit' => MysqlAdapter::INT_TINY,
                'comment' => '재무팀 승인여부'])
            ->addColumn('approved_by_cashflow_uid', 'integer', ['signed' => false,
                'comment' => '재무팀 승인 user id'])
            ->addColumn('approved_by_cashflow_datetime', 'datetime', [
                'comment' => '재무팀 승인 시각'])
            ->addColumn('is_deposited', 'string', ['limit' => 1,
                'comment' => '입금여부'])
            ->addColumn('is_approved_by_hr', 'integer', ['limit' => MysqlAdapter::INT_TINY,
                'comment' => '인사팀 승인여부'])
            ->addColumn('approved_by_hr_uid', 'integer', ['signed' => false,
                'comment' => '인사팀 승인 user id'])
            ->addColumn('approved_by_hr_datetime', 'datetime', [
                'comment' => '인사팀 승인 시각'])
            ->addColumn('giftcard_category', 'string', ['limit' => 32,
                'comment' => '기프트카드 종류'])
            ->addColumn('req_count', 'integer', ['signed' => false,
                'comment' => '요청 수량'])
            ->addColumn('req_sum', 'integer', ['signed' => false,
                'comment' => '요청금액 합계'])
            ->addColumn('deposit_name', 'string', ['limit' => 32,
                'comment' => '입금자 이름'])
            ->addColumn('deposit_date', 'datetime', [
                'comment' => '입금 시간'])
            ->addColumn('purpose', 'string', ['limit' => 255,
                'comment' => '사용 용도'])
            ->addColumn('is_deleted', 'integer', ['limit' => MysqlAdapter::INT_TINY,
                'comment' => '레코드 삭제 여부'])
            ->addColumn('num_envelops', 'integer', ['signed' => false,
                'comment' => '봉투 수량'])
            ->create();
    }
}
