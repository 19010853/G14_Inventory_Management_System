<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReturnPurchase extends Model
{
    //
    protected $guarded = [];

    public function purchaseItems(): HasMany {
        return $this->hasMany(ReturnPurchaseItem::class, 'return_purchase_id');
    }

    public function warehouse(): BelongsTo {
        return $this->belongsTo(Warehouse::class, 'warehouse_id');
    }

    public function supplier(): BelongsTo {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }
}
