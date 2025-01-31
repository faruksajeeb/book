<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class PurchaseDetail extends Model
{
    use HasFactory,SoftDeletes;
    public $fillable = [
        'purchase_id',
        'book_id',
        'quantity',
        'price',
        'sub_total',
        'discount_percentage',
        'discount_amount',
        'vat_percentage',
        'vat_amount',
        'net_sub_total',
        'flag',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected $dates = ['deleted_at'];
    
    public function book() : BelongsTo
    {
        return $this->belongsTo(Book::class)->withTrashed();
    }
    public function purchase() : BelongsTo
    {
        return $this->belongsTo(Purchase::class)->withTrashed()->withDefault(['value'=>'']);
    }
    // 📌 Relationship with ProductVariant
    public function variant()
    {
        return $this->belongsTo(ProductVariant::class, 'variant_id');
    }
    // 📌 Relationship with Variant Options via ProductVariant
    public function variantOptions() 
    {
        return $this->hasManyThrough(
            ProductAttributeOption::class,
            ProductVariantAttributeOption::class,
            'product_variant_id',
            'id',
            'variant_id',
            'product_attribute_option_id'
        )->with('attribute'); // Load the related attribute
    }
  
}
