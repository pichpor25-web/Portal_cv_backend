<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class CategoryController extends Controller
{
    /**
     * Display a listing of all categories with job counts.
     */
    public function index()
    {
        $categories = DB::table('categories')
            ->leftJoin('jobs', 'categories.id', '=', 'jobs.category_id')
            ->select('categories.id', 'categories.name', 'categories.slug', 'categories.created_at', 'categories.updated_at')
            ->selectRaw('COUNT(jobs.id) as jobs_count')
            ->groupBy('categories.id', 'categories.name', 'categories.slug', 'categories.created_at', 'categories.updated_at')
            ->get();

        return response()->json($categories, 200);
    }

    /**
     * Store a newly created category in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:categories,name',
            'slug' => 'nullable|string|max:255|unique:categories,slug',
        ]);

        $now = now();
        $slug = !empty($validated['slug']) ? Str::slug($validated['slug']) : Str::slug($validated['name']);

        $categoryId = DB::table('categories')->insertGetId([
            'name' => $validated['name'],
            'slug' => $slug,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $category = DB::table('categories')->where('id', $categoryId)->first();

        return response()->json([
            'message' => 'Category created successfully',
            'data' => $category
        ], 201);
    }

    /**
     * Display the specified category and its jobs.
     */
    public function show(string $id)
    {
        $category = DB::table('categories')->where('id', $id)->first();

        if (!$category) {
            return response()->json(['message' => 'Category not found'], 404);
        }

        $jobs = DB::table('jobs')
            ->where('category_id', $id)
            ->get();

        return response()->json([
            'category' => $category,
            'jobs' => $jobs
        ], 200);
    }

    /**
     * Update the specified category in storage.
     */
    public function update(Request $request, string $id)
    {
        $category = DB::table('categories')->where('id', $id)->first();

        if (!$category) {
            return response()->json(['message' => 'Category not found'], 404);
        }

        $validated = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:255', Rule::unique('categories')->ignore($id)],
            'slug' => ['nullable', 'string', 'max:255', Rule::unique('categories')->ignore($id)],
        ]);

        $updateData = [];

        if (isset($validated['name'])) {
            $updateData['name'] = $validated['name'];
            $updateData['slug'] = !empty($validated['slug']) ? Str::slug($validated['slug']) : Str::slug($validated['name']);
        } elseif (isset($validated['slug'])) {
            $updateData['slug'] = Str::slug($validated['slug']);
        }

        $updateData['updated_at'] = now();

        DB::table('categories')->where('id', $id)->update($updateData);

        $updatedCategory = DB::table('categories')->where('id', $id)->first();

        return response()->json([
            'message' => 'Category updated successfully',
            'data' => $updatedCategory
        ], 200);
    }

    /**
     * Remove the specified category from storage.
     */
    public function destroy(string $id)
    {
        $deleted = DB::table('categories')->where('id', $id)->delete();

        if (!$deleted) {
            return response()->json(['message' => 'Category not found'], 404);
        }

        return response()->json(['message' => 'Category deleted successfully'], 200);
    }
}