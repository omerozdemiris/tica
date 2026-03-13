<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ExampleCategoriesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            'Kadın Giyim',
            'Çocuk Giyim',
            'Günlük',
            'Spor',
            'Ayakkabı',
        ];

        foreach ($categories as $name) {
            $slug = Str::slug($name);
            $category = Category::where('slug', $slug)->first();

            if ($category) {
                if ($category->name !== $name) {
                    $category->name = $name;
                    $category->save();
                }
                continue;
            }

            Category::create([
                'name' => $name,
                'slug' => $slug,
            ]);
        }
    }
}

