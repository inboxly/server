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
    public function can_get_list_of_categories(): void
    {
        // Setup
        $defaultCategory = $this->user->defaultCategory;
        $categories = Category::factory(2)->create(['user_id' => $this->user->getKey()]);
        $alienCategories = Category::factory(2)->create(['user_id' => User::factory()->create()->getKey()]);

        // Run
        $response = $this->asUser()->getJson('api/categories');

        // Asserts
        $response->assertOk();

        $response->assertJsonCount(3, 'data');

        $response->assertJson(['data' => [
            ['id' => $defaultCategory->getKey()],
            ['id' => $categories->first()->getKey()],
            ['id' => $categories->last()->getKey()],
        ]]);

        $response->assertJsonMissing(['data' => [
            ['id' => $alienCategories->first()->getKey()],
            ['id' => $alienCategories->last()->getKey()],
        ]]);

        $response->assertJsonStructure([
            'data' => [
                [
                    'id',
                    'title',
                ]
            ],
        ]);
    }

    /**
     * @test
     * @see \App\Http\Controllers\CategoriesController::store()
     */
    public function can_create_new_category(): void
    {
        // Run
        $response = $this->asUser()->postJson('api/categories', [
            'title' => 'new_category',
        ]);

        // Asserts
        $response->assertCreated();
        $response->assertJson([
            'data' => [
                'title' => 'new_category',
            ]
        ]);

        $response->assertJsonStructure(['data' => [
            'id',
            'title',
        ]]);

    }

    /**
     * @test
     * @see \App\Http\Controllers\CategoriesController::update()
     */
    public function can_rename_one_category(): void
    {
        // Setup
        $category = Category::factory()->create([
            'user_id' => $this->user->getKey(),
            'title' => 'title',
        ]);

        // Run
        $response = $this->asUser()->putJson("api/categories/{$category->getKey()}", [
            'title' => 'renamed',
        ]);

        // Asserts
        $response->assertNoContent();
        $this->assertDatabaseHas(Category::newModelInstance()->getTable(), [
            'id' => $category->getKey(),
            'title' => 'renamed',
        ]);
    }

    /**
     * @test
     * @see \App\Http\Controllers\CategoriesController::update()
     */
    public function cannot_rename_not_its_category(): void
    {
        // Setup
        $category = Category::factory()->create([
            'title' => 'title',
        ]);

        // Run
        $response = $this->asUser()->putJson("api/categories/{$category->getKey()}", [
            'title' => 'renamed',
        ]);

        // Asserts
        $response->assertForbidden();
        $this->assertDatabaseHas(Category::newModelInstance()->getTable(), [
            'id' => $category->getKey(),
            'title' => 'title',
        ]);
    }

    /**
     * @test
     * @see \App\Http\Controllers\CategoriesController::update()
     */
    public function can_mark_default_one_category(): void
    {
        // Setup
        $initialDefaultCategory = $this->user->defaultCategory;

        $category = Category::factory()->create([
            'user_id' => $this->user->getKey(),
            'title' => 'title',
        ]);

        // Run
        $response = $this->asUser()->putJson("api/categories/{$category->getKey()}", [
            'is_default' => true,
        ]);

        // Asserts
        $response->assertNoContent();
        $this->assertDatabaseHas(Category::newModelInstance()->getTable(), [
            'id' => $category->getKey(),
            'is_default' => true,
        ]);
        $this->assertFalse($initialDefaultCategory->fresh()->is_default);
    }

    /**
     * @test
     * @see \App\Http\Controllers\CategoriesController::update()
     */
    public function cannot_mark_default_not_its_category(): void
    {
        // Setup
        $initialDefaultCategory = $this->user->defaultCategory;

        $category = Category::factory()->create([
            'title' => 'title',
        ]);

        // Run
        $response = $this->asUser()->putJson("api/categories/{$category->getKey()}", [
            'is_default' => true,
        ]);

        // Asserts
        $response->assertForbidden();
        $this->assertDatabaseHas(Category::newModelInstance()->getTable(), [
            'id' => $category->getKey(),
            'is_default' => false,
        ]);
        $this->assertTrue($initialDefaultCategory->fresh()->is_default);
    }

    /**
     * @test
     * @see \App\Http\Controllers\CategoriesController::destroy()
     */
    public function can_delete_one_category(): void
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
    public function cannot_delete_not_its_category(): void
    {
        // Setup
        $category = Category::factory()->create();

        // Run
        $response = $this->asUser()->deleteJson("api/categories/{$category->getKey()}");

        // Asserts
        $response->assertForbidden();
        $this->assertDatabaseHas($category->getTable(), ['id' => $category->getKey()]);
    }
}
