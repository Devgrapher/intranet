<?php

namespace Intra\Model;

use Illuminate\Database\Eloquent\Model as Eloquent;

class PolicyModel extends Eloquent
{
    protected $table = 'policy';

    protected $fillable = [
        'keyword',
        'name',
    ];

    protected $hidden = [
        'pivot',
    ];

    public function users()
    {
        return $this->belongsToMany(
            UserEloquentModel::class,
            'policy_user',
            'policy_id',
            'user_id'
        );
    }
}
