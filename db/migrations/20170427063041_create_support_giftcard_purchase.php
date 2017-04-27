<?php

use Phinx\Db\Adapter\MysqlAdapter;
use Phinx\Migration\AbstractMigration;

class CreateSupportGiftcardPurchase extends AbstractMigration
{
    /**
     * Change Method.
     *
     * Write your reversible migrations using this method.
     *
     * More information on writing migrations is available here:
     * http://docs.phinx.org/en/latest/migrations.html#the-abstractmigration-class
     *
     * The following commands can be used in this method and Phinx will
     * automatically reverse them when rolling back:
     *
     *    createTable
     *    renameTable
     *    addColumn
     *    renameColumn
     *    addIndex
     *    addForeignKey
     *
     * Remember to call "create()" or "update()" and NOT "save()" when working
     * with the Table class.
     */
    public function change()
    {
        $this->table('support_gift_card_purchase')
            ->addColumn('uuid', 'string', ['limit' => 32])
            ->addColumn('reg_date', 'datetime', ['default' => 'CURRENT_TIMESTAMP'])
            ->addColumn('uid', 'integer', ['signed' => false])
            ->addColumn('team', 'string', ['limit' => 255])
            ->addColumn('deposit_name', 'string', ['limit' => 32])
            ->addColumn('deposit_date', 'datetime')
            ->addColumn('is_completed_by_cf', 'integer', ['limit' => MysqlAdapter::INT_TINY])
            ->addColumn('completed_by_cf_uid', 'integer', ['signed' => false])
            ->addColumn('completed_by_cf_datetime', 'datetime')
            ->addColumn('is_deposited', 'string', ['limit' => 1])
            ->addColumn('is_completed_by_hr', 'integer', ['limit' => MysqlAdapter::INT_TINY])
            ->addColumn('completed_by_hr_uid', 'integer', ['signed' => false])
            ->addColumn('completed_by_hr_datetime', 'datetime')
            ->addColumn('cash_category', 'integer', ['signed' => false])
            ->addColumn('req_count', 'integer', ['signed' => false])
            ->addColumn('req_sum', 'integer', ['signed' => false])
            ->addColumn('deposit_duedate', 'datetime')
            ->addColumn('purpose', 'string', ['limit' => 255])
            ->addColumn('is_deleted', 'integer', ['limit' => MysqlAdapter::INT_TINY])
            ->addColumn('envelops', 'integer', ['signed' => false])
            ->create();
    }
}
