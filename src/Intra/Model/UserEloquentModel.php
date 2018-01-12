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
        'name_en',
        'personcode',
        'birth',
        'position',
        'inner_call',
        'mobile',
        'image',
        'on_date',
        'email',
        'trainee_off_date',
        'off_date',
        'ridibooks_id',
        'military_service',
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
