<?php

namespace Intra\Service\User;

use Intra\Repository\TeamRepository;

class Organization
{
    const DEFAULT_MAX_TEAM_ID = 50;

    // Predefined aliases
    const ALIAS_CO = 'co';
    const ALIAS_FINANCE = 'finance';
    const ALIAS_DEVICE = 'device';
    const ALIAS_CCPQ = 'ccpq';
    const ALIAS_STORY_OP = 'story_op';

    public static function readTeamNames()
    {
        $team_repo = new TeamRepository();
        $names = $team_repo->all(['name'], 'id', 'asc')->pluck('name')->toArray();
        return $names;
    }

    public static function getTeamName($alias)
    {
        $team_repo = new TeamRepository();
        $team = $team_repo->first(['alias' => $alias])->toArray();
        return $team['name'];
    }
}
