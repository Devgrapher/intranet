<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

class AddDinnerOrders extends AbstractMigration
{
    public function change()
    {
        $this->table('dinner_orders')
            ->addColumn('day', 'integer', ['signed' => false, 'null' => false,
                'comment' => '요일'])
            ->addColumn('order_url', 'string', ['limit' => 255,
                'comment' => '주문 링크'])
            ->create();
    }
}
