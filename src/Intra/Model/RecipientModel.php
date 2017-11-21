<?php

namespace Intra\Model;

use Illuminate\Database\Eloquent\Model as Eloquent;

class RecipientModel extends Eloquent
{
    protected $table = 'recipients';

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
            'recipients_user',
            'recipient_id',
            'user_id'
        );
    }
}
