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
    const ALIAS_ROMANCE_BL = 'romance_bl';

    public static function readTeamNames()
    {
        $team_repo = new TeamRepository();
        $names = $team_repo->all(['name'], 'name', 'asc')->pluck('name')->toArray();

        return $names;
    }

    public static function readTeamDetailNames()
    {
        return [
            '없음',
            'BL',
            '로맨스',
        ];
    }

    public static function getTeamName($alias)
    {
        $team_repo = new TeamRepository();
        $team_with_alias = $team_repo->first(['alias' => $alias]);
        if (!$team_with_alias) {
            return $alias;
        }

        $team = $team_with_alias->toArray();

        return $team['name'];
    }

    public static function getShortTeamName($alias)
    {
        $divisions = explode('/', self::getTeamName($alias));

        return end($divisions);
    }

    public static function getHRTeamName()
    {
        return self::getShortTeamName(self::ALIAS_CO);
    }
}
