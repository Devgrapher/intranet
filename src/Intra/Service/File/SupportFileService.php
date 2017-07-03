<?php
declare(strict_types=1);

namespace Intra\Service\File;

class SupportFileService extends FileService
{
    private $category;
    private $type;

    public function __construct(string $category, string $type)
    {
        $this->category = $category;
        $this->type = $type;
    }

    protected function getGroupName(): string
    {
        return 'support' . '.' . $this->category . '.' . $this->type;
    }
}
