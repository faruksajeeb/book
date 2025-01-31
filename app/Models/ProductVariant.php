<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductVariant extends Model
{
    use HasFactory;
    protected $fillable = ['book_id', 'price', 'stock_quantity', 'sku'];

    public function product()
    {
        return $this->belongsTo(Book::class);
    }

    public function attributeOptions()
    {
        return $this->belongsToMany(
            ProductAttributeOption::class, 
            'product_variant_attribute_options'
        );
    }
}
