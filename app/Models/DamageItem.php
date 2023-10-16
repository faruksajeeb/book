<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class DamageItem extends Model
{
    use HasFactory,SoftDeletes;
    public $fillable = [
        'book_id',
        'damage_date',
        'description',
        'quantity',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected $dates = ['deleted_at'];
    
    public function scopeSearch($query, $term)
    {
        $term = "%$term%";
        $query->where(function ($q) use ($term) {
            // $q->where('id','LIKE',$term);
            $q->whereHas('book',function($q) use($term){
                $q->where('title','LIKE',$term);
            });
        });
    }
    public function book() : BelongsTo
    {
        return $this->belongsTo(Book::class)->withTrashed()->withDefault(['value'=>'']);
    }
}
