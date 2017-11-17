<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

class CreateHolidayAdjusts extends AbstractMigration
{
    public function change()
    {
        $this->table('holiday_adjusts')
            ->addColumn('uid', 'integer', ['signed' => false])
            ->addColumn('manager_uid', 'integer', ['signed' => false])
            ->addColumn('reason', 'string', ['length' => 100])
            ->addColumn('diff_year', 'integer', ['signed' => false])
            ->addColumn('diff', 'integer')
            ->addTimestamps()
            ->addColumn('deleted_at', 'timestamp', ['null' => true])
            ->addForeignKey('uid', 'users', 'uid')
            ->addForeignKey('manager_uid', 'users', 'uid')
            ->create();
    }
}
