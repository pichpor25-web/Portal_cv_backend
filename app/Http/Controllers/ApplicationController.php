<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ApplicationController extends Controller
{
    /**
     * Display a listing of applications (Filterable by user_id or job_id).
     */
    public function index(Request $request)
    {
        $query = DB::table('applications')
            ->join('jobs', 'applications.job_id', '=', 'jobs.id')
            ->join('users', 'applications.user_id', '=', 'users.id')
            ->leftJoin('cvs', 'applications.cv_id', '=', 'cvs.id')
            ->select(
                'applications.*',
                'jobs.title as job_title',
                'users.name as applicant_name',
                'users.email as applicant_email',
                'cvs.full_name as cv_applicant_name'
            );

        // Filter by user ID (my applications)
        if ($request->has('user_id')) {
            $query->where('applications.user_id', $request->user_id);
        }

        // Filter by job ID (applications for a job)
        if ($request->has('job_id')) {
            $query->where('applications.job_id', $request->job_id);
        }

        // Filter by application status
        if ($request->has('status')) {
            $query->where('applications.status', $request->status);
        }

        $applications = $query->orderBy('applications.created_at', 'desc')->get();

        return response()->json($applications, 200);
    }

    /**
     * Submit a new job application.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'job_id' => 'required|integer|exists:jobs,id',
            'user_id' => 'required|integer|exists:users,id',
            'cv_id' => 'nullable|integer|exists:cvs,id',
            'cover_letter' => 'nullable|string',
            'status' => 'nullable|string|in:pending,reviewed,accepted,rejected',
        ]);

        // Prevent duplicate applications for the same job by the same user
        $existing = DB::table('applications')
            ->where('job_id', $validated['job_id'])
            ->where('user_id', $validated['user_id'])
            ->exists();

        if ($existing) {
            return response()->json(['message' => 'You have already applied for this job.'], 409);
        }

        $now = now();

        $applicationId = DB::table('applications')->insertGetId([
            'job_id' => $validated['job_id'],
            'user_id' => $validated['user_id'],
            'cv_id' => $validated['cv_id'] ?? null,
            'cover_letter' => $validated['cover_letter'] ?? null,
            'status' => $validated['status'] ?? 'pending',
            'applied_at' => $now,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $application = DB::table('applications')->where('id', $applicationId)->first();

        return response()->json([
            'message' => 'Application submitted successfully',
            'data' => $application
        ], 201);
    }

    /**
     * Display a specific application with details.
     */
    public function show(string $id)
    {
        $application = DB::table('applications')
            ->join('jobs', 'applications.job_id', '=', 'jobs.id')
            ->join('users', 'applications.user_id', '=', 'users.id')
            ->leftJoin('cvs', 'applications.cv_id', '=', 'cvs.id')
            ->select(
                'applications.*',
                'jobs.title as job_title',
                'users.name as applicant_name',
                'users.email as applicant_email',
                'cvs.summary as cv_summary'
            )
            ->where('applications.id', $id)
            ->first();

        if (!$application) {
            return response()->json(['message' => 'Application not found'], 404);
        }

        return response()->json($application, 200);
    }

    /**
     * Update application status or details (e.g., pending -> accepted/rejected).
     */
    public function update(Request $request, string $id)
    {
        $application = DB::table('applications')->where('id', $id)->first();

        if (!$application) {
            return response()->json(['message' => 'Application not found'], 404);
        }

        $validated = $request->validate([
            'status' => 'sometimes|required|string|in:pending,reviewed,accepted,rejected',
            'cv_id' => 'nullable|integer|exists:cvs,id',
            'cover_letter' => 'nullable|string',
        ]);

        $updateData = [];

        if (isset($validated['status'])) {
            $updateData['status'] = $validated['status'];
        }

        if (array_key_exists('cv_id', $validated)) {
            $updateData['cv_id'] = $validated['cv_id'];
        }

        if (array_key_exists('cover_letter', $validated)) {
            $updateData['cover_letter'] = $validated['cover_letter'];
        }

        $updateData['updated_at'] = now();

        DB::table('applications')->where('id', $id)->update($updateData);

        $updatedApplication = DB::table('applications')->where('id', $id)->first();

        return response()->json([
            'message' => 'Application updated successfully',
            'data' => $updatedApplication
        ], 200);
    }

    /**
     * Remove an application.
     */
    public function destroy(string $id)
    {
        $deleted = DB::table('applications')->where('id', $id)->delete();

        if (!$deleted) {
            return response()->json(['message' => 'Application not found'], 404);
        }

        return response()->json(['message' => 'Application withdrawn successfully'], 200);
    }
}