<?php
declare(strict_types=1);

namespace Intra\Repository;

class TeamRepository extends Repository
{
    public function model()
    {
        return 'Intra\Model\TeamModel';
    }
}
