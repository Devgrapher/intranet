<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

class AddSupportDepotCommentColumn extends AbstractMigration
{
    public function change()
    {
        $this->table('support_depot')
            ->addColumn('comment', 'string', ['length' => 512,
                'comment' => 'co팀 의견'])
            ->save();
    }
}
