<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReturnPurchaseItem extends Model
{
    //
    protected $guarded = [];

    public function returnPurchase(): BelongsTo {
        return $this->belongsTo(ReturnPurchase::class, 'return_purchase_id');
    }

    public function product(): BelongsTo {
        return $this->belongsTo(Product::class, 'product_id');
    }
}
