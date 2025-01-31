<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductAttributeOption extends Model
{
    use HasFactory;
    protected $fillable = ['attribute_id', 'value'];

    public function attribute()
    {
        return $this->belongsTo(ProductAttribute::class);
    }

    // 📌 Relationship with ProductVariants via Pivot Table
    public function productVariants()
    {
        return $this->belongsToMany(
            ProductVariant::class,
            'product_variant_attribute_options',
            'product_attribute_option_id',
            'product_variant_id'
        );
    }
}
