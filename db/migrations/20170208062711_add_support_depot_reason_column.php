<?php

use Phinx\Migration\AbstractMigration;

class AddSupportDepotReasonColumn extends AbstractMigration
{
    public function change()
    {
        $this->table('support_depot')
            ->addColumn('reason', 'text', ['length' => 255]);
    }
}
