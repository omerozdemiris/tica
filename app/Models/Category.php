<?php



namespace App\Models;



use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;

use Illuminate\Database\Eloquent\Relations\HasMany;



class Category extends Model

{

    protected $fillable = ['category_id', 'name', 'slug', 'description', 'click_count', 'photo', 'meta_title', 'meta_description'];



    public function products_count()
    {
        return $this->products()->count();
    }

    public function parent(): BelongsTo

    {

        return $this->belongsTo(Category::class, 'category_id');
    }



    public function children(): HasMany

    {

        return $this->hasMany(Category::class, 'category_id');
    }



    public function products(): BelongsToMany

    {

        return $this->belongsToMany(Product::class)->withTimestamps();
    }
}
