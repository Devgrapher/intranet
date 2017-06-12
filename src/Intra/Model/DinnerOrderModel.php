<?php
declare(strict_types=1);

namespace Intra\Model;

use Illuminate\Database\Eloquent\Model as Eloquent;

class DinnerOrderModel extends Eloquent
{
    protected $table = 'dinner_orders';
    public $primaryKey = 'day';
    public $incrementing = true;

    protected $fillable = [
        'day',
        'order_url',
    ];
}
