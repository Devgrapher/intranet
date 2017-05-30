<?php

namespace Intra\Service\User;

class Organization
{
    const MAX_TEAM_ID = 50;

    // Predefined aliases
    const ALIAS_HUMAN_MANAGE = 'human_manage';
    const ALIAS_CASH_FLOW = 'cash_flow';
    const ALIAS_DEVICE = 'device';
    const ALIAS_CCPQ = 'ccpq';

    public static function readTeamNames()
    {
        $team_names = [];
        foreach (range(1, self::MAX_TEAM_ID) as $id) {
            if (!empty($_ENV["teams.$id"])) {
                array_push($team_names, $_ENV["teams.$id"]);
            }
        }
        return $team_names;
    }

    public static function getTeamName($alias)
    {
        if (!empty($_ENV["teams.aliases.$alias"])) {
            $id = $_ENV["teams.aliases.$alias"];
            return $_ENV["teams.$id"];
        } else {
            return "";
        }
    }
}
