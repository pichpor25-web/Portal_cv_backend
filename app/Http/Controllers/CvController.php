<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class CvController extends Controller
{
    /**
     * Display a listing of all CVs (Filterable by user_id).
     */
    public function index(Request $request)
    {
        $query = DB::table('cvs')
            ->join('users', 'cvs.user_id', '=', 'users.id')
            ->select('cvs.*', 'users.name as owner_name', 'users.email as owner_email');

        if ($request->has('user_id')) {
            $query->where('cvs.user_id', $request->user_id);
        }

        $cvs = $query->orderBy('cvs.created_at', 'desc')->get();

        return response()->json($cvs, 200);
    }



    /**
     * Display the specified CV with all related sections.
     */
    public function show(string $id)
    {
        $cv = DB::table('cvs')->where('id', $id)->first();

        if (!$cv) {
            return response()->json(['message' => 'CV not found'], 404);
        }

        // Fetch all related items via Query Builder
        $educations = DB::table('educations')->where('cv_id', $id)->get();
        $experiences = DB::table('experiences')->where('cv_id', $id)->get();
        $skills = DB::table('skills')->where('cv_id', $id)->get();
        $projects = DB::table('projects')->where('cv_id', $id)->get();
        $languages = DB::table('languages')->where('cv_id', $id)->get();

        return response()->json([
            'cv' => $cv,
            'educations' => $educations,
            'experiences' => $experiences,
            'skills' => $skills,
            'projects' => $projects,
            'languages' => $languages,
        ], 200);
    }

    /**
     * Store a new CV with photo upload.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|integer|exists:users,id',
            'full_name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:50',
            'address' => 'nullable|string',
            'summary' => 'nullable|string',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048', // Max 2MB
        ]);

        $photoPath = null;

        // Handle file upload
        if ($request->hasFile('photo')) {
            // Saves to: storage/app/public/photos/filename.jpg
            $photoPath = $request->file('photo')->store('photos', 'public');
        }

        $now = now();

        $cvId = DB::table('cvs')->insertGetId([
            'user_id' => $validated['user_id'],
            'full_name' => $validated['full_name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'address' => $validated['address'] ?? null,
            'summary' => $validated['summary'] ?? null,
            'photo' => $photoPath ? asset('storage/' . $photoPath) : null, // Full URL path
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $cv = DB::table('cvs')->where('id', $cvId)->first();

        return response()->json([
            'message' => 'CV created successfully with photo',
            'data' => $cv
        ], 201);
    }

    /**
     * Update CV with photo upload/replacement.
     */
    public function update(Request $request, string $id)
    {
        $cv = DB::table('cvs')->where('id', $id)->first();

        if (!$cv) {
            return response()->json(['message' => 'CV not found'], 404);
        }

        $validated = $request->validate([
            'full_name' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|email|max:255',
            'phone' => 'nullable|string|max:50',
            'address' => 'nullable|string',
            'summary' => 'nullable|string',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        $updateData = [];

        foreach (['full_name', 'email', 'phone', 'address', 'summary'] as $field) {
            if (array_key_exists($field, $validated)) {
                $updateData[$field] = $validated[$field];
            }
        }

        // Handle photo update & remove old photo if exists
        if ($request->hasFile('photo')) {
            // Delete old file if present
            if ($cv->photo) {
                $oldPath = str_replace(asset('storage/'), '', $cv->photo);
                Storage::disk('public')->delete($oldPath);
            }

            $photoPath = $request->file('photo')->store('photos', 'public');
            $updateData['photo'] = asset('storage/' . $photoPath);
        }

        $updateData['updated_at'] = now();

        DB::table('cvs')->where('id', $id)->update($updateData);

        $updatedCv = DB::table('cvs')->where('id', $id)->first();

        return response()->json([
            'message' => 'CV updated successfully',
            'data' => $updatedCv
        ], 200);
    }



    /**
     * Remove the specified CV from storage.
     */
    public function destroy(string $id)
    {
        $deleted = DB::table('cvs')->where('id', $id)->delete();

        if (!$deleted) {
            return response()->json(['message' => 'CV not found'], 404);
        }

        return response()->json(['message' => 'CV deleted successfully'], 200);
    }
}
