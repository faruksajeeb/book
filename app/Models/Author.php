<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
class Author extends Model
{
    use HasFactory,SoftDeletes; 
    protected $table = 'authors';
    protected $primaryKey = 'id';
    protected $fillable = [
        'author_name',
        'author_email',
        'author_phone',
        'author_address',
        'author_photo',
        'author_country',
        'created_by',
        'updated_at',
        'created_at',
        'deleted_at',
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
            $q->where('author_name', 'LIKE', $term);
            $q->orWhere('author_email', 'LIKE', $term);
            $q->orWhere('author_phone', 'LIKE', $term);
            $q->orWhere('author_address', 'LIKE', $term);
            // $q->orWhereHas('categories',function($q) use($term){
            //     $q->where('category_name','LIKE',$term);
            // });
        });
    }

    public function books()
    {
        return $this->belongsToMany(Book::class, 'book_author', 'author_id', 'book_id');
    }
}
