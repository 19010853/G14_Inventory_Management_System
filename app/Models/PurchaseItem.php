<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseItem extends Model
{
    //
    protected $guarded = [];

    public function purchase(): BelongsTo{
        return $this->belongsTo(Purchase::class, 'purchase_id');
    }

    public function product(): BelongsTo{
        return $this->belongsTo(Product::class, 'product_id');
    }
}
