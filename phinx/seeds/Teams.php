<?php
declare(strict_types=1);

use Phinx\Seed\AbstractSeed;
use Intra\Service\User\Organization;

class Teams extends AbstractSeed
{
    const TEAM_LIST = [
        ['name' => 'CO', 'alias' => Organization::ALIAS_CO],
        ['name' => 'FINANCE', 'alias' => Organization::ALIAS_FINANCE],
        ['name' => 'DEVICE', 'alias' => Organization::ALIAS_DEVICE],
        ['name' => 'PQ', 'alias' => Organization::ALIAS_CCPQ],
        ['name' => 'STORY', 'alias' => Organization::ALIAS_STORY_OP],
        ['name' => 'ROMANCE_BL', 'alias' => Organization::ALIAS_ROMANCE_BL],
    ];

    public function run()
    {
        $this->table('teams')
            ->insert(self::TEAM_LIST)
            ->save();
    }
}
