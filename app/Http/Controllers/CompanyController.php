<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class CompanyController extends Controller
{
    /**
     * Display a listing of companies (Filterable by user_id).
     */
    public function index(Request $request)
    {
        $query = DB::table('companies')
            ->join('users', 'companies.user_id', '=', 'users.id')
            ->select('companies.*', 'users.name as owner_name', 'users.email as owner_email');

        if ($request->filled('user_id')) {
            $query->where('companies.user_id', $request->query('user_id'));
        }

        $companies = $query->orderBy('companies.created_at', 'desc')->get();

        return response()->json($companies, 200);
    }

    /**
     * Store a new company with logo upload.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|integer|exists:users,id',
            'company_name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'website' => 'nullable|url|max:255',
            'phone' => 'nullable|string|max:50',
            'address' => 'nullable|string',
        ]);

        $logoPath = null;

        if ($request->hasFile('logo')) {
            $logoPath = $request->file('logo')->store('logos', 'public');
        }

        $now = now();

        $companyId = DB::table('companies')->insertGetId([
            'user_id' => $validated['user_id'],
            'company_name' => $validated['company_name'],
            'description' => $validated['description'] ?? null,
            'logo' => $logoPath ? asset('storage/' . $logoPath) : null,
            'website' => $validated['website'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'address' => $validated['address'] ?? null,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $company = DB::table('companies')->where('id', $companyId)->first();

        return response()->json([
            'message' => 'Company created successfully',
            'data' => $company
        ], 201);
    }

    /**
     * Display a company with all its posted jobs.
     */
    public function show(string $id)
    {
        $company = DB::table('companies')->where('id', $id)->first();

        if (!$company) {
            return response()->json(['message' => 'Company not found'], 404);
        }

        $jobs = DB::table('jobs')->where('company_id', $id)->get();

        return response()->json([
            'company' => $company,
            'jobs' => $jobs
        ], 200);
    }

    /**
     * Update company details and replace logo.
     */
    public function update(Request $request, string $id)
    {
        $company = DB::table('companies')->where('id', $id)->first();

        if (!$company) {
            return response()->json(['message' => 'Company not found'], 404);
        }

        $validated = $request->validate([
            'user_id' => 'sometimes|required|integer|exists:users,id',
            'company_name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'website' => 'nullable|url|max:255',
            'phone' => 'nullable|string|max:50',
            'address' => 'nullable|string',
        ]);

        $updateData = [];

        foreach (['user_id', 'company_name', 'description', 'website', 'phone', 'address'] as $field) {
            if ($request->has($field)) {
                $updateData[$field] = $validated[$field] ?? null;
            }
        }

        if ($request->hasFile('logo')) {
            if ($company->logo) {
                $oldPath = str_replace(asset('storage/'), '', $company->logo);
                Storage::disk('public')->delete($oldPath);
            }

            $logoPath = $request->file('logo')->store('logos', 'public');
            $updateData['logo'] = asset('storage/' . $logoPath);
        }

        $updateData['updated_at'] = now();

        DB::table('companies')->where('id', $id)->update($updateData);

        $updatedCompany = DB::table('companies')->where('id', $id)->first();

        return response()->json([
            'message' => 'Company updated successfully',
            'data' => $updatedCompany
        ], 200);
    }

    /**
     * Delete a company and remove its stored logo.
     */
    public function destroy(string $id)
    {
        $company = DB::table('companies')->where('id', $id)->first();

        if (!$company) {
            return response()->json(['message' => 'Company not found'], 404);
        }

        if ($company->logo) {
            $path = str_replace(asset('storage/'), '', $company->logo);
            Storage::disk('public')->delete($path);
        }

        DB::table('companies')->where('id', $id)->delete();

        return response()->json(['message' => 'Company deleted successfully'], 200);
    }
}
