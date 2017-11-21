<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

class FixPaymentPriceDouble extends AbstractMigration
{
    public function change()
    {
        $this->execute('ALTER TABLE payments MODIFY price DOUBLE NOT NULL;');
    }
}
