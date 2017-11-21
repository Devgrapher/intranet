<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

class AddPolicyAndRecipients extends AbstractMigration
{
    public function change()
    {
        $this->table('policy')
            ->addColumn('keyword', 'string', ['length' => 80])
            ->addColumn('name', 'string', ['length' => 80])
            ->create();

        $this->table('policy_user')
            ->addColumn('policy_id', 'integer', ['signed' => true])
            ->addColumn('user_id', 'integer', ['signed' => false])
            ->addForeignKey('policy_id', 'policy', 'id')
            ->addForeignKey('user_id', 'users', 'uid')
            ->create();

        $this->table('recipients')
            ->addColumn('keyword', 'string', ['length' => 80])
            ->addColumn('name', 'string', ['length' => 80])
            ->create();

        $this->table('recipients_user')
            ->addColumn('recipient_id', 'integer', ['signed' => true])
            ->addColumn('user_id', 'integer', ['signed' => false])
            ->addForeignKey('recipient_id', 'recipients', 'id')
            ->addForeignKey('user_id', 'users', 'uid')
            ->create();
    }
}
