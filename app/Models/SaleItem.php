<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SaleItem extends Model
{
    //
    protected $guarded = [];

    public function product(): BelongsTo{
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function sale(): BelongsTo{
        return $this->belongsTo(Sale::class, 'sale_id');
    }
}
