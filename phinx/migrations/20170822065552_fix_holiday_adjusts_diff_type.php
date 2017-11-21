<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

class FixHolidayAdjustsDiffType extends AbstractMigration
{
    public function change()
    {
        $this->table('holiday_adjusts')
            ->changeColumn('diff', 'float')
            ->save();
    }
}
