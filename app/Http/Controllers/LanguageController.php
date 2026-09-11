<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LanguageController extends Controller
{
    /**
     * Display a listing of language records (Filterable by cv_id).
     */
    public function index(Request $request)
    {
        $query = DB::table('languages');

        if ($request->filled('cv_id')) {
            $query->where('cv_id', $request->query('cv_id'));
        }

        $languages = $query->orderBy('name', 'asc')->get();

        return response()->json($languages, 200);
    }

    /**
     * Store a new language record in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'cv_id' => 'required|integer|exists:cvs,id',
            'name' => 'required|string|max:255',
            'level' => 'nullable|string|max:255',
        ]);

        $now = now();

        $languageId = DB::table('languages')->insertGetId([
            'cv_id' => $validated['cv_id'],
            'name' => $validated['name'],
            'level' => $validated['level'] ?? null,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $language = DB::table('languages')->where('id', $languageId)->first();

        return response()->json([
            'message' => 'Language record created successfully',
            'data' => $language
        ], 201);
    }

    /**
     * Display the specified language record.
     */
    public function show(string $id)
    {
        $language = DB::table('languages')->where('id', $id)->first();

        if (!$language) {
            return response()->json(['message' => 'Language record not found'], 404);
        }

        return response()->json($language, 200);
    }

    /**
     * Update the specified language record in storage.
     */
    public function update(Request $request, string $id)
    {
        $language = DB::table('languages')->where('id', $id)->first();

        if (!$language) {
            return response()->json(['message' => 'Language record not found'], 404);
        }

        $validated = $request->validate([
            'cv_id' => 'sometimes|required|integer|exists:cvs,id',
            'name' => 'sometimes|required|string|max:255',
            'level' => 'nullable|string|max:255',
        ]);

        $updateData = [];

        foreach (['cv_id', 'name', 'level'] as $field) {
            if ($request->has($field)) {
                $updateData[$field] = $validated[$field] ?? null;
            }
        }

        $updateData['updated_at'] = now();

        DB::table('languages')->where('id', $id)->update($updateData);

        $updatedLanguage = DB::table('languages')->where('id', $id)->first();

        return response()->json([
            'message' => 'Language record updated successfully',
            'data' => $updatedLanguage
        ], 200);
    }

    /**
     * Remove the specified language record from storage.
     */
    public function destroy(string $id)
    {
        $deleted = DB::table('languages')->where('id', $id)->delete();

        if (!$deleted) {
            return response()->json(['message' => 'Language record not found'], 404);
        }

        return response()->json(['message' => 'Language record deleted successfully'], 200);
    }
}
