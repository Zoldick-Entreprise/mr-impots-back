<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Category;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // principal categories
        $droit = Category::create([
            'name' => ['fr' => 'Droit', 'en' => 'Law'],
            'slug' => 'droit',
            'icon' => 'fa-gavel',
            'parent_id' => null,
            'sort_order' => 1,
        ]);

        // sous-catégories pour informatique
        Category::create([
            'name' => ['fr' => 'Droit Privé', 'en' => 'Private Law'],
            'slug' => 'droit-prive',
            'parent_id' => $droit->id,
            'sort_order' => 1,
        ]);

        Category::create([
            'name' => ['fr' => 'Droit Civil', 'en' => 'Civil Law'],
            'slug' => 'droit-civil',
            'parent_id' => $droit->id,
            'sort_order' => 2,
        ]);
    }
}