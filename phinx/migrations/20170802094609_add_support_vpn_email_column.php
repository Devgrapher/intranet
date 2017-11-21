<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

class AddSupportVpnEmailColumn extends AbstractMigration
{
    public function change()
    {
        $this->table('support_vpn')
            ->addColumn('email', 'string', ['length' => 100, 'comment' => '신청자 email'])
            ->save();
    }
}
