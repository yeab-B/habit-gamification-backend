<?php

namespace App\Services;

use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class CategoryService
{
    public function getCategories(User $user): Collection
    {
        return Category::query()
            ->where('user_id', $user->id)
            ->latest()
            ->get();
    }

    public function createCategory(User $user, array $data): Category
    {
        return Category::query()->create([
            'user_id' => $user->id,
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'icon' => $data['icon'] ?? null,
            'color' => $data['color'] ?? null,
        ]);
    }

    public function updateCategory(Category $category, array $data): Category
    {
        $category->fill($data);
        $category->save();

        return $category->fresh();
    }

    public function deleteCategory(Category $category): void
    {
        DB::transaction(function () use ($category): void {
            $category->tasks()->delete();
            $category->delete();
        });
    }
}
