<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    //
    protected $guarded = [];

    // Accessor for product_qty (maps to product_quantity column)
    public function getProductQtyAttribute()
    {
        return $this->getAttribute('product_quantity') ?? 0;
    }

    // Mutator for product_qty (maps to product_quantity column)
    public function setProductQtyAttribute($value)
    {
        $this->attributes['product_quantity'] = $value ?? 0;
    }

    public function images(){
        return $this->hasMany(ProductImage::class);
    }

    public function warehouse(){
        return $this->belongsTo(Warehouse::class, 'warehouse_id', 'id');
    }

    public function brand(){
        return $this->belongsTo(Brand::class, 'brand_id','id');
    }

    public function category(){
        return $this->belongsTo(ProductCategory::class, 'category_id','id');
    }

    public function supplier(){
        return $this->belongsTo(Supplier::class, 'supplier_id','id');
    }
}
