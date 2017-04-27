<?php

use Phinx\Db\Adapter\MysqlAdapter;
use Phinx\Migration\AbstractMigration;

class CreateSupportGiftcardPurchase extends AbstractMigration
{
    public function change()
    {
        $this->table('support_gift_card_purchase')
            ->addColumn('uuid', 'string', ['limit' => 32])
            ->addColumn('reg_date', 'datetime', ['default' => 'CURRENT_TIMESTAMP'])
            ->addColumn('uid', 'integer', ['signed' => false, 'null' => false])
            ->addColumn('is_completed_by_cf', 'integer', ['limit' => MysqlAdapter::INT_TINY])
            ->addColumn('completed_by_cf_uid', 'integer', ['signed' => false])
            ->addColumn('completed_by_cf_datetime', 'datetime')
            ->addColumn('is_deposited', 'string', ['limit' => 1])
            ->addColumn('is_completed_by_hr', 'integer', ['limit' => MysqlAdapter::INT_TINY])
            ->addColumn('completed_by_hr_uid', 'integer', ['signed' => false])
            ->addColumn('completed_by_hr_datetime', 'datetime')
            ->addColumn('cash_category', 'string', ['limit' => 32])
            ->addColumn('req_count', 'integer', ['signed' => false])
            ->addColumn('req_sum', 'integer', ['signed' => false])
            ->addColumn('deposit_name', 'string', ['limit' => 32])
            ->addColumn('deposit_date', 'datetime')
            ->addColumn('purpose', 'string', ['limit' => 255])
            ->addColumn('is_deleted', 'integer', ['limit' => MysqlAdapter::INT_TINY])
            ->addColumn('envelops', 'integer', ['signed' => false])
            ->create();
    }
}
