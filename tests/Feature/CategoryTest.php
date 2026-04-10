<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Category;

class CategoryTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic feature test example.
     */
    public function test_an_category_can_be_added(): void
    {
        $token = "2|MLLNqvZGZ00ZX4e43B84nW6pW3nOZXQq3uNY9ipF1c0fd17f";
        
        $response = $this->withHeaders([
            'Accept' => 'application/json',
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/admin/categories', [
            "name" => ['fr' => 'Justice', 'en' => 'Justice'],
            "slug" => 'justice',
            "icon" => 'fa-balance-scale',
            "parent_id" => null,
            "sort_order" => 3,
        ]);

        $response->assertCreated();
    }

}
