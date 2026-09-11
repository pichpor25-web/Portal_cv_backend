<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class JobController extends Controller
{
    /**
     * Display a listing of jobs (Filterable by company_id, category_id, status, or search).
     */
    public function index(Request $request)
    {
        $query = DB::table('jobs')
            ->join('companies', 'jobs.company_id', '=', 'companies.id')
            ->leftJoin('categories', 'jobs.category_id', '=', 'categories.id')
            ->select(
                'jobs.*',
                'companies.company_name',
                'companies.logo as company_logo',
                'categories.name as category_name'
            );

        if ($request->filled('company_id')) {
            $query->where('jobs.company_id', $request->query('company_id'));
        }

        if ($request->filled('category_id')) {
            $query->where('jobs.category_id', $request->query('category_id'));
        }

        if ($request->filled('status')) {
            $query->where('jobs.status', $request->query('status'));
        }

        if ($request->filled('search')) {
            $search = $request->query('search');
            $query->where(function ($q) use ($search) {
                $q->where('jobs.title', 'LIKE', "%{$search}%")
                    ->orWhere('jobs.description', 'LIKE', "%{$search}%");
            });
        }

        $jobs = $query->orderBy('jobs.created_at', 'desc')->get();

        return response()->json($jobs, 200);
    }

    /**
     * Store a new job posting.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'company_id' => 'required|integer|exists:companies,id',
            'category_id' => 'nullable|integer|exists:categories,id',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'location' => 'nullable|string|max:255',
            'salary_min' => 'nullable|numeric|min:0',
            'salary_max' => 'nullable|numeric|gte:salary_min',
            'employment_type' => 'nullable|string|max:255',
            'requirements' => 'nullable|string',
            'deadline' => 'nullable|date',
            'status' => 'nullable|string|in:active,inactive,closed',
        ]);

        $now = now();

        $jobId = DB::table('jobs')->insertGetId([
            'company_id' => $validated['company_id'],
            'category_id' => $validated['category_id'] ?? null,
            'title' => $validated['title'],
            'description' => $validated['description'],
            'location' => $validated['location'] ?? null,
            'salary_min' => $validated['salary_min'] ?? null,
            'salary_max' => $validated['salary_max'] ?? null,
            'employment_type' => $validated['employment_type'] ?? 'full-time',
            'requirements' => $validated['requirements'] ?? null,
            'deadline' => $validated['deadline'] ?? null,
            'status' => $validated['status'] ?? 'active',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $job = DB::table('jobs')->where('id', $jobId)->first();

        return response()->json([
            'message' => 'Job posted successfully',
            'data' => $job
        ], 201);
    }

    /**
     * Display a single job posting.
     */
    public function show(string $id)
    {
        $job = DB::table('jobs')
            ->join('companies', 'jobs.company_id', '=', 'companies.id')
            ->leftJoin('categories', 'jobs.category_id', '=', 'categories.id')
            ->select(
                'jobs.*',
                'companies.company_name',
                'companies.description as company_description',
                'companies.logo as company_logo',
                'companies.website as company_website',
                'categories.name as category_name'
            )
            ->where('jobs.id', $id)
            ->first();

        if (!$job) {
            return response()->json(['message' => 'Job not found'], 404);
        }

        return response()->json($job, 200);
    }

    /**
     * Update an existing job.
     */
    public function update(Request $request, string $id)
    {
        $job = DB::table('jobs')->where('id', $id)->first();

        if (!$job) {
            return response()->json(['message' => 'Job not found'], 404);
        }

        $validated = $request->validate([
            'company_id' => 'sometimes|required|integer|exists:companies,id',
            'category_id' => 'nullable|integer|exists:categories,id',
            'title' => 'sometimes|required|string|max:255',
            'description' => 'sometimes|required|string',
            'location' => 'nullable|string|max:255',
            'salary_min' => 'nullable|numeric|min:0',
            'salary_max' => 'nullable|numeric',
            'employment_type' => 'nullable|string|max:255',
            'requirements' => 'nullable|string',
            'deadline' => 'nullable|date',
            'status' => 'nullable|string|in:active,inactive,closed',
        ]);

        $updateData = [];

        $fields = ['company_id', 'category_id', 'title', 'description', 'location', 'salary_min', 'salary_max', 'employment_type', 'requirements', 'deadline', 'status'];

        foreach ($fields as $field) {
            if ($request->has($field)) {
                $updateData[$field] = $validated[$field] ?? null;
            }
        }

        $updateData['updated_at'] = now();

        DB::table('jobs')->where('id', $id)->update($updateData);

        $updatedJob = DB::table('jobs')->where('id', $id)->first();

        return response()->json([
            'message' => 'Job updated successfully',
            'data' => $updatedJob
        ], 200);
    }

    /**
     * Delete a job posting.
     */
    public function destroy(string $id)
    {
        $deleted = DB::table('jobs')->where('id', $id)->delete();

        if (!$deleted) {
            return response()->json(['message' => 'Job not found'], 404);
        }

        return response()->json(['message' => 'Job deleted successfully'], 200);
    }
}
