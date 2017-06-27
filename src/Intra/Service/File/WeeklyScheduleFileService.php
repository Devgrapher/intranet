<?php
declare(strict_types=1);

namespace Intra\Service\File;

class WeeklyScheduleFileService extends FileService
{
    protected function getGroupName(): string
    {
        return 'weekly_schedule';
    }
}
