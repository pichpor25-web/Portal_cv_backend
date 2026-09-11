<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ExperienceController extends Controller
{
    /**
     * Display a listing of experience records (Filterable by cv_id).
     */
    public function index(Request $request)
    {
        $query = DB::table('experiences');

        if ($request->filled('cv_id')) {
            $query->where('cv_id', $request->query('cv_id'));
        }

        $experiences = $query->orderBy('start_date', 'desc')->get();

        return response()->json($experiences, 200);
    }

    /**
     * Store a new experience record in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'cv_id' => 'required|integer|exists:cvs,id',
            'company_name' => 'required|string|max:255',
            'position' => 'required|string|max:255',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'description' => 'nullable|string',
        ]);

        $now = now();

        $experienceId = DB::table('experiences')->insertGetId([
            'cv_id' => $validated['cv_id'],
            'company_name' => $validated['company_name'],
            'position' => $validated['position'],
            'start_date' => $validated['start_date'] ?? null,
            'end_date' => $validated['end_date'] ?? null,
            'description' => $validated['description'] ?? null,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $experience = DB::table('experiences')->where('id', $experienceId)->first();

        return response()->json([
            'message' => 'Experience record created successfully',
            'data' => $experience
        ], 201);
    }

    /**
     * Display the specified experience record.
     */
    public function show(string $id)
    {
        $experience = DB::table('experiences')->where('id', $id)->first();

        if (!$experience) {
            return response()->json(['message' => 'Experience record not found'], 404);
        }

        return response()->json($experience, 200);
    }

    /**
     * Update the specified experience record in storage.
     */
    public function update(Request $request, string $id)
    {
        $experience = DB::table('experiences')->where('id', $id)->first();

        if (!$experience) {
            return response()->json(['message' => 'Experience record not found'], 404);
        }

        $validated = $request->validate([
            'cv_id' => 'sometimes|required|integer|exists:cvs,id',
            'company_name' => 'sometimes|required|string|max:255',
            'position' => 'sometimes|required|string|max:255',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'description' => 'nullable|string',
        ]);

        $updateData = [];

        foreach (['cv_id', 'company_name', 'position', 'start_date', 'end_date', 'description'] as $field) {
            if ($request->has($field)) {
                $updateData[$field] = $validated[$field] ?? null;
            }
        }

        $updateData['updated_at'] = now();

        DB::table('experiences')->where('id', $id)->update($updateData);

        $updatedExperience = DB::table('experiences')->where('id', $id)->first();

        return response()->json([
            'message' => 'Experience record updated successfully',
            'data' => $updatedExperience
        ], 200);
    }

    /**
     * Remove the specified experience record from storage.
     */
    public function destroy(string $id)
    {
        $deleted = DB::table('experiences')->where('id', $id)->delete();

        if (!$deleted) {
            return response()->json(['message' => 'Experience record not found'], 404);
        }

        return response()->json(['message' => 'Experience record deleted successfully'], 200);
    }
}
