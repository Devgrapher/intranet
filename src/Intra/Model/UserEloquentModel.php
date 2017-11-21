<?php

namespace Intra\Model;

use Illuminate\Database\Eloquent\Model as Eloquent;

class UserEloquentModel extends Eloquent
{
    protected $table = 'users';
    protected $primaryKey = 'uid';

    protected $fillable = [
        'id',
        'name',
        'email',
        'team',
        'team_detail',
        'position',
        'outer_call',
        'inner_call',
        'mobile',
        'birth',
        'image',
        'on_date',
        'off_date',
        'extra',
        'personcode',
        'ridibooks_id',
        'is_admin',
        'comment',
    ];

    protected $hidden = [
        'pivot',
    ];

    public function policies()
    {
        return $this->belongsToMany(
            PolicyModel::class,
            'policy_user',
            'user_id',
            'policy_id'
        );
    }

    public function recipients()
    {
        return $this->belongsToMany(
            RecipientModel::class,
            'recipient_user',
            'user_id',
            'recipient_id'
        );
    }
}
