<?php

declare(strict_types=1);

namespace Tests\Feature\Controllers;

use App\Models\Category;
use App\Models\User;
use Tests\TestCase;

/**
 * @see \App\Http\Controllers\CategoriesController
 */
class CategoriesControllerTest extends TestCase
{
    /**
     * @test
     * @see \App\Http\Controllers\CategoriesController::index()
     */
    public function user_can_get_list_of_categories(): void
    {
        // Setup
        $mainCategory = $this->user->mainCategory;
        $categories = Category::factory(2)->create(['user_id' => $this->user->getKey()]);
        $alienCategories = Category::factory(2)->create(['user_id' => User::factory()->create()->getKey()]);

        // Run
        $response = $this->asUser()->getJson('api/categories');

        // Asserts
        $response->assertOk();

        $response->assertJsonCount(3, 'data');

        $response->assertJson(['data' => [
            ['id' => $mainCategory->getKey()],
            ['id' => $categories->first()->getKey()],
            ['id' => $categories->last()->getKey()],
        ]]);

        $response->assertJsonMissing(['data' => [
            ['id' => $alienCategories->first()->getKey()],
            ['id' => $alienCategories->last()->getKey()],
        ]]);

        $response->assertJsonStructure([
            'data' => [
                $this->categoryStructure(),
            ],
        ]);
    }

    /**
     * @test
     * @see \App\Http\Controllers\CategoriesController::store()
     */
    public function user_can_create_new_category(): void
    {
        // Run
        $response = $this->asUser()->postJson('api/categories', [
            'name' => 'new_category',
        ]);

        // Asserts
        $response->assertCreated();
        $response->assertJson([
            'data' => [
                'name' => 'new_category',
            ]
        ]);

        $response->assertJsonStructure([
            'data' => $this->categoryStructure()
        ]);
    }

    /**
     * @test
     * @see \App\Http\Controllers\CategoriesController::update()
     */
    public function user_can_rename_own_custom_category(): void
    {
        // Setup
        $category = Category::factory()->create([
            'user_id' => $this->user->getKey(),
            'name' => 'name',
        ]);

        // Run
        $response = $this->asUser()->putJson("api/categories/{$category->getKey()}", [
            'name' => 'renamed',
        ]);

        // Asserts
        $response->assertNoContent();
        $this->assertDatabaseHas(Category::newModelInstance()->getTable(), [
            'id' => $category->getKey(),
            'name' => 'renamed',
        ]);
    }

    /**
     * @test
     * @see \App\Http\Controllers\CategoriesController::update()
     */
    public function user_cannot_rename_own_main_category(): void
    {
        // Setup
        $category = $this->user->mainCategory;

        // Run
        $response = $this->asUser()->putJson("api/categories/{$category->getKey()}", [
            'name' => 'renamed',
        ]);

        // Asserts
        $response->assertForbidden();
        $this->assertDatabaseHas(Category::newModelInstance()->getTable(), [
            'id' => $category->getKey(),
            'name' => 'Main',
        ]);
    }

    /**
     * @test
     * @see \App\Http\Controllers\CategoriesController::update()
     */
    public function user_cannot_rename_not_its_custom_category(): void
    {
        // Setup
        $category = Category::factory()->create([
            'name' => 'name',
        ]);

        // Run
        $response = $this->asUser()->putJson("api/categories/{$category->getKey()}", [
            'name' => 'renamed',
        ]);

        // Asserts
        $response->assertForbidden();
        $this->assertDatabaseHas(Category::newModelInstance()->getTable(), [
            'id' => $category->getKey(),
            'name' => 'name',
        ]);
    }

    /**
     * @test
     * @see \App\Http\Controllers\CategoriesController::destroy()
     */
    public function user_can_delete_own_custom_category(): void
    {
        // Setup
        $category = Category::factory()->create([
            'user_id' => $this->user->getKey(),
        ]);

        // Run
        $response = $this->asUser()->deleteJson("api/categories/{$category->getKey()}");

        // Asserts
        $response->assertNoContent();
        $this->assertDeleted($category);
    }

    /**
     * @test
     * @see \App\Http\Controllers\CategoriesController::destroy()
     */
    public function user_cannot_delete_own_main_category(): void
    {
        // Setup
        $category = $this->user->mainCategory;

        // Run
        $response = $this->asUser()->deleteJson("api/categories/{$category->getKey()}");

        // Asserts
        $response->assertForbidden();
        $this->assertDatabaseHas($category->getTable(), ['id' => $category->getKey()]);
    }

    /**
     * @test
     * @see \App\Http\Controllers\CategoriesController::destroy()
     */
    public function user_cannot_delete_not_its_custom_category(): void
    {
        // Setup
        $category = Category::factory()->create();

        // Run
        $response = $this->asUser()->deleteJson("api/categories/{$category->getKey()}");

        // Asserts
        $response->assertForbidden();
        $this->assertDatabaseHas($category->getTable(), ['id' => $category->getKey()]);
    }

    /**
     * @return string[]
     */
    private function categoryStructure(): array
    {
        return [
            'id',
            'name',
            'is_customizable',
        ];
    }
}
