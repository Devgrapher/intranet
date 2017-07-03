<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

class UseFileModel extends AbstractMigration
{
    public function change()
    {
        $users = $this->table('files');
        $users->addColumn('del_date', 'datetime', [
            'comment' => '삭제 시간'])
            ->save();

        $this->execute('UPDATE files SET del_date = CURRENT_TIME() WHERE is_delete = 1');
    }
}
