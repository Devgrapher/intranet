<?php

use Phinx\Migration\AbstractMigration;

class ChangePaymentNoteColumnType extends AbstractMigration
{
    public function change()
    {
        $this->table('payments')
            ->changeColumn("note", "text")
            ->save();
    }
}
