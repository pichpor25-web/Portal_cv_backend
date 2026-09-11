<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SkillController extends Controller
{
    /**
     * Display a listing of skills (Filterable by cv_id).
     */
    public function index(Request $request)
    {
        $query = DB::table('skills');

        if ($request->filled('cv_id')) {
            $query->where('cv_id', $request->query('cv_id'));
        }

        $skills = $query->orderBy('name', 'asc')->get();

        return response()->json($skills, 200);
    }

    /**
     * Store a new skill record in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'cv_id' => 'required|integer|exists:cvs,id',
            'name' => 'required|string|max:255',
            'level' => 'nullable|string|max:255',
        ]);

        $now = now();

        $skillId = DB::table('skills')->insertGetId([
            'cv_id' => $validated['cv_id'],
            'name' => $validated['name'],
            'level' => $validated['level'] ?? null,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $skill = DB::table('skills')->where('id', $skillId)->first();

        return response()->json([
            'message' => 'Skill record created successfully',
            'data' => $skill
        ], 201);
    }

    /**
     * Display the specified skill record.
     */
    public function show(string $id)
    {
        $skill = DB::table('skills')->where('id', $id)->first();

        if (!$skill) {
            return response()->json(['message' => 'Skill record not found'], 404);
        }

        return response()->json($skill, 200);
    }

    /**
     * Update the specified skill record in storage.
     */
    public function update(Request $request, string $id)
    {
        $skill = DB::table('skills')->where('id', $id)->first();

        if (!$skill) {
            return response()->json(['message' => 'Skill record not found'], 404);
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

        DB::table('skills')->where('id', $id)->update($updateData);

        $updatedSkill = DB::table('skills')->where('id', $id)->first();

        return response()->json([
            'message' => 'Skill record updated successfully',
            'data' => $updatedSkill
        ], 200);
    }

    /**
     * Remove the specified skill record from storage.
     */
    public function destroy(string $id)
    {
        $deleted = DB::table('skills')->where('id', $id)->delete();

        if (!$deleted) {
            return response()->json(['message' => 'Skill record not found'], 404);
        }

        return response()->json(['message' => 'Skill record deleted successfully'], 200);
    }
}
