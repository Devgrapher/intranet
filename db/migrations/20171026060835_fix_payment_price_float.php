<?php

use Phinx\Migration\AbstractMigration;

class FixPaymentPriceFloat extends AbstractMigration
{
    public function change()
    {
        $this->table('payments')
            ->changeColumn('price', 'float')
            ->save();
    }
}
