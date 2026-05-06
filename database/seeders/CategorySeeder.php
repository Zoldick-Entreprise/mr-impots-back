<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

final class CategorySeeder extends Seeder
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
            'parent_id' => null,
        ]);

        // sous-catégories pour droit
        Category::create([
            'name' => ['fr' => 'Droit Privé', 'en' => 'Private Law'],
            'slug' => 'droit-prive',
            'parent_id' => $droit->id,
        ]);

        Category::create([
            'name' => ['fr' => 'Droit Civil', 'en' => 'Civil Law'],
            'slug' => 'droit-civil',
            'parent_id' => $droit->id,
        ]);
    }
}
