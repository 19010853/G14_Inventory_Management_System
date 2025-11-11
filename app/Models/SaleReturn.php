<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SaleReturn extends Model
{
    //
    protected $guarded = [];

    public function customer(): BelongsTo {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function warehouse(): BelongsTo {
        return $this->belongsTo(Warehouse::class, 'warehouse_id');
    }

    public function saleReturnItems(): HasMany{
        return $this->hasMany(SaleReturnItem::class, 'sale_return_id');
    }
}
