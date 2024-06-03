<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'category_id',
        'transaction_name',
        'amount',
        'type',
        'description',
        'transaction_date'
    ];

    // relational db - 1 transaction hanya memiliki satu user 
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // relatioanl db -> transaction hanya memiliki satu category 
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function scopeSearch($query, $keyword)
    {
        return $query->when($keyword, function ($query, $keyword) {
            return $query->where('t.description', 'LIKE', '%' . $keyword . '%')
                ->orWhere('c.name', 'LIKE', '%' . $keyword . '%');
        });
    }
}
