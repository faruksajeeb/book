<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Book extends Model
{
    use HasFactory,SoftDeletes; 
    protected $table = 'books';
    protected $primaryKey = 'id';
    protected $fillable = [
        'title',
        'isbn',
        // 'author_id',
        'category_id',
        'sub_category_id',
        'genre',
        'price',
        'buying_discount_percentage',
        'selling_discount_percentage',
        'buying_vat_percentage',
        'selling_vat_percentage',
        'photo',
        'stock_quantity',
        'publication_year',
        'publisher_id',
        'status',
        'created_at',
        'updated_at',
        'deleted_at',
        'created_by',
        'updated_by',
        'deleted_by',
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
            $q->where('title', 'LIKE', $term);
            $q->orWhere('isbn', 'LIKE', $term);
            $q->orWhere('genre', 'LIKE', $term);
            $q->orWhereHas('publisher',function($q) use($term){
                $q->where('publisher_name','LIKE',$term);
            });
            $q->orWhereHas('authors', function ($authorQuery) use ($term) { // Note 'authors' relation name
                $authorQuery->where('author_name', 'LIKE', $term);
            });
        });
    }

    public function authors()
    {
        return $this->belongsToMany(Author::class, 'book_author', 'book_id', 'author_id');
    }

    public function publisher() : BelongsTo
    {
        return $this->belongsTo(Publisher::class)->withTrashed()->withDefault(['value'=>'']);
    }
    // public function author() : BelongsTo
    // {
    //     return $this->belongsTo(Author::class)->withTrashed()->withDefault(['value'=>'']);
    // }
    
    public function category() : BelongsTo
    {
        return $this->belongsTo(Category::class)->withTrashed()->withDefault(['value'=>'']);
    }
    public function sub_category() : BelongsTo
    {
        return $this->belongsTo(SubCategory::class)->withTrashed()->withDefault(['value'=>'']);
    }
}
