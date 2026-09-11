<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProjectController extends Controller
{
    /**
     * Display a listing of projects (Filterable by cv_id).
     */
    public function index(Request $request)
    {
        $query = DB::table('projects');

        if ($request->filled('cv_id')) {
            $query->where('cv_id', $request->query('cv_id'));
        }

        $projects = $query->orderBy('created_at', 'desc')->get();

        return response()->json($projects, 200);
    }

    /**
     * Store a new project record in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'cv_id' => 'required|integer|exists:cvs,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'technologies' => 'nullable|string|max:255',
            'project_url' => 'nullable|url|max:255',
        ]);

        $now = now();

        $projectId = DB::table('projects')->insertGetId([
            'cv_id' => $validated['cv_id'],
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'technologies' => $validated['technologies'] ?? null,
            'project_url' => $validated['project_url'] ?? null,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $project = DB::table('projects')->where('id', $projectId)->first();

        return response()->json([
            'message' => 'Project record created successfully',
            'data' => $project
        ], 201);
    }

    /**
     * Display the specified project record.
     */
    public function show(string $id)
    {
        $project = DB::table('projects')->where('id', $id)->first();

        if (!$project) {
            return response()->json(['message' => 'Project record not found'], 404);
        }

        return response()->json($project, 200);
    }

    /**
     * Update the specified project record in storage.
     */
    public function update(Request $request, string $id)
    {
        $project = DB::table('projects')->where('id', $id)->first();

        if (!$project) {
            return response()->json(['message' => 'Project record not found'], 404);
        }

        $validated = $request->validate([
            'cv_id' => 'sometimes|required|integer|exists:cvs,id',
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'technologies' => 'nullable|string|max:255',
            'project_url' => 'nullable|url|max:255',
        ]);

        $updateData = [];

        foreach (['cv_id', 'name', 'description', 'technologies', 'project_url'] as $field) {
            if ($request->has($field)) {
                $updateData[$field] = $validated[$field] ?? null;
            }
        }

        $updateData['updated_at'] = now();

        DB::table('projects')->where('id', $id)->update($updateData);

        $updatedProject = DB::table('projects')->where('id', $id)->first();

        return response()->json([
            'message' => 'Project record updated successfully',
            'data' => $updatedProject
        ], 200);
    }

    /**
     * Remove the specified project record from storage.
     */
    public function destroy(string $id)
    {
        $deleted = DB::table('projects')->where('id', $id)->delete();

        if (!$deleted) {
            return response()->json(['message' => 'Project record not found'], 404);
        }

        return response()->json(['message' => 'Project record deleted successfully'], 200);
    }
}
