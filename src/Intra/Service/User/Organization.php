<?php

namespace Intra\Service\User;

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
        $max_team_id = $_ENV["teams_max_team_id"] ?? self::DEFAULT_MAX_TEAM_ID;
        $team_names = [];
        foreach (range(1, $max_team_id) as $id) {
            if (!empty($_ENV["teams_$id"])) {
                array_push($team_names, $_ENV["teams_$id"]);
            }
        }
        return $team_names;
    }

    public static function getTeamName($alias)
    {
        if (!empty($_ENV["teams_aliases_$alias"])) {
            $id = $_ENV["teams_aliases_$alias"];
            return $_ENV["teams_$id"];
        } else {
            return "";
        }
    }
}
