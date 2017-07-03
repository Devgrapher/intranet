<?php
declare(strict_types=1);

namespace Intra\Service\File;

class PaymentFileService extends FileService
{
    protected function getGroupName(): string
    {
        return 'payment_files';
    }
}
