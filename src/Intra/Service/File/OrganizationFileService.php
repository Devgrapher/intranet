<?php
declare(strict_types=1);

namespace Intra\Service\File;

class OrganizationFileService extends FileService
{
    protected function getGroupName(): string
    {
        return 'organization';
    }
}
