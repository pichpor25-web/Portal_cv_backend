<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EducationController extends Controller
{
    /**
     * Display a listing of education records (Filterable by cv_id).
     */
    public function index(Request $request)
    {
        $query = DB::table('educations');

        // Check URL Query string parameter (e.g., /api/educations?cv_id=3)
        if ($request->filled('cv_id')) {
            $query->where('cv_id', $request->query('cv_id'));
        }

        $educations = $query->orderBy('start_year', 'desc')->get();

        return response()->json($educations, 200);
    }

    /**
     * Store a new education record in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'cv_id' => 'required|integer|exists:cvs,id',
            'school' => 'required|string|max:255',
            'degree' => 'nullable|string|max:255',
            'field_of_study' => 'nullable|string|max:255',
            'start_year' => 'nullable|integer|digits:4',
            'end_year' => 'nullable|integer|digits:4',
            'description' => 'nullable|string',
        ]);

        $now = now();

        $educationId = DB::table('educations')->insertGetId([
            'cv_id' => $validated['cv_id'],
            'school' => $validated['school'],
            'degree' => $validated['degree'] ?? null,
            'field_of_study' => $validated['field_of_study'] ?? null,
            'start_year' => $validated['start_year'] ?? null,
            'end_year' => $validated['end_year'] ?? null,
            'description' => $validated['description'] ?? null,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $education = DB::table('educations')->where('id', $educationId)->first();

        return response()->json([
            'message' => 'Education record created successfully',
            'data' => $education
        ], 201);
    }

    /**
     * Display the specified education record.
     */
    public function show(string $id)
    {
        $education = DB::table('educations')->where('id', $id)->first();

        if (!$education) {
            return response()->json(['message' => 'Education record not found'], 404);
        }

        return response()->json($education, 200);
    }

    /**
     * Update the specified education record in storage.
     */
    public function update(Request $request, string $id)
    {
        $education = DB::table('educations')->where('id', $id)->first();

        if (!$education) {
            return response()->json(['message' => 'Education record not found'], 404);
        }

        $validated = $request->validate([
            'cv_id' => 'sometimes|required|integer|exists:cvs,id',
            'school' => 'sometimes|required|string|max:255',
            'degree' => 'nullable|string|max:255',
            'field_of_study' => 'nullable|string|max:255',
            'start_year' => 'nullable|integer|digits:4',
            'end_year' => 'nullable|integer|digits:4',
            'description' => 'nullable|string',
        ]);

        $updateData = [];

        foreach (['cv_id', 'school', 'degree', 'field_of_study', 'start_year', 'end_year', 'description'] as $field) {
            if ($request->has($field)) {
                $updateData[$field] = $validated[$field] ?? null;
            }
        }

        $updateData['updated_at'] = now();

        DB::table('educations')->where('id', $id)->update($updateData);

        $updatedEducation = DB::table('educations')->where('id', $id)->first();

        return response()->json([
            'message' => 'Education record updated successfully',
            'data' => $updatedEducation
        ], 200);
    }

    /**
     * Remove the specified education record from storage.
     */
    public function destroy(string $id)
    {
        $deleted = DB::table('educations')->where('id', $id)->delete();

        if (!$deleted) {
            return response()->json(['message' => 'Education record not found'], 404);
        }

        return response()->json(['message' => 'Education record deleted successfully'], 200);
    }
}
