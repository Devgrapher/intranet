<?php
declare(strict_types=1);

namespace Intra\Model;

use Illuminate\Database\Eloquent\Model as Eloquent;
use Illuminate\Database\Eloquent\SoftDeletes;

class FileModel extends Eloquent
{
    use SoftDeletes;

    protected $table = 'files';

    public $primaryKey = 'id';
    public $incrementing = true;

    public $timestamps = ["created_at"];
    const CREATED_AT = 'reg_date';
    const UPDATED_AT = null;
    const DELETED_AT = 'del_date';

    public $dateFormat = 'Y-m-d H:i:s';
    protected $dates = ['reg_date', 'del_date'];

    protected $fillable = [
        'uid',
        'group',
        'key',
        'original_filename',
        'location',
    ];
}
