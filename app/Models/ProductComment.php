<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductComment extends Model
{
    use HasFactory;
    protected $table = 'product_comments';
    protected $fillable = [
        'comment',
        'rating',
        'user_id',
        'product_id',
        'status',
    ];
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
    public function pendingComments()
    {
        return $this->where('status', 0)->get();
    }
    public function approvedComments()
    {
        return $this->where('status', 1)->get();
    }
    public function rejectedComments()
    {
        return $this->where('status', 2)->get();
    }
}
