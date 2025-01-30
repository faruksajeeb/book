<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductAttribute extends Model
{
    use HasFactory;
    protected $fillable = ['name'];

    public function options()
    {
        return $this->hasMany(ProductAttributeOption::class,'attribute_id');
    }
}
