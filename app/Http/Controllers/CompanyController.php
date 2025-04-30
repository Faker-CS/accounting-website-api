<?php

namespace App\Http\Controllers;

use App\Models\company;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class CompanyController extends Controller implements HasMiddleware
{
    public static function middleware()
    {
        // return
        //     [
        //         new Middleware('auth:sanctum', except: ['index', 'show']),
        //     ];

    }

    public function __construct()
    {

        //$this->middleware('auth:sanctum')->except(['index', 'show']);
        //$this->middleware('role:comptable')->only(['store', 'update', 'destroy']);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $query = Company::with(['industries', 'activities', 'user'])
                ->when($request->search, fn($q) => $q->where('company_name', 'LIKE', '%' . $request->search . '%'))
                ->when($request->status, fn($q) => $q->where('status', $request->status))
                ->when($request->industry_id, fn($q) => $q->whereHas('industries', fn($q) => $q->where('id', $request->industry_id)));

            return response()->json($query->get()); // Return the data directly without wrapping it in a 'data' key
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'company_name' => 'required|string|max:255',
            'description' => 'required|string',
            'logo' => 'image|max:3072',
            'founded' => 'required|date',
            'raison_sociale' => 'required|string|max:255',
            'capital_social' => 'nullable|numeric',
            'numero_tva' => 'nullable|string|max:255',
            'numero_siren' => 'nullable|string|max:255',
            'numero_siret' => 'required|string|max:14',
            'forme_juridique' => 'required|in:EIRL,SARL,EURL,SAS,SASU,SA',
            'code_company_type' => 'required|in:APE,NEF',
            'code_company_value' => 'required|string|max:255',
            'adresse_siege_social' => 'required|string|max:255',
            'code_postale' => 'required|string|max:10',
            'ville' => 'required|string|max:255',
            'convention_collective' => 'nullable|string|max:255',
            'chiffre_affaire' => 'nullable|numeric',
            'tranche_a' => 'nullable|numeric',
            'tranche_b' => 'nullable|numeric',
            'nombre_salaries' => 'nullable|integer',
            'moyenne_age' => 'nullable|integer',
            'nombre_salaries_cadres' => 'nullable|integer',
            'moyenne_age_cadres' => 'nullable|integer',
            'nombre_salaries_non_cadres' => 'nullable|integer',
            'moyenne_age_non_cadres' => 'nullable|integer',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        try {
            $data = $validator->validated();

            // Handle logo upload
            $data['logo'] = $request->file('logo')->store('company-logos', 'public');

            $company = Company::create($data);

            // Attach relationships
            $company->industries()->attach($request->industries);
            $company->activities()->attach($request->activities);

            return response()->json([
                'data' => $company->load(['industries', 'activities']),
                'message' => 'Company created successfully'
            ], 201);

        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show( $company)
    {
        
        $data = company::with(['industries', 'activities', 'user'])
            ->where('id', $company->id)
            ->first();
        if (!$data) {
            return response()->json(['message'=> ''],404);
        }
        return response()->json([
            'data' => $company->load(['industries', 'activities', 'user']),
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, company $company)
    {
        $validator = Validator::make($request->all(), [
            'company_name' => 'required|string|max:255',
            'description' => 'required|string',
            'logo' => 'image|max:3072',
            'founded' => 'required|date',
            'raison_sociale' => 'required|string|max:255',
            'capital_social' => 'nullable|numeric',
            'numero_tva' => 'nullable|string|max:255',
            'numero_siren' => 'nullable|string|max:255',
            'numero_siret' => 'required|string|max:14',
            'forme_juridique' => 'required|in:EIRL,SARL,EURL,SAS,SASU,SA',
            'code_company_type' => 'required|in:APE,NEF',
            'code_company_value' => 'required|string|max:255',
            'adresse_siege_social' => 'required|string|max:255',
            'code_postale' => 'required|string|max:10',
            'ville' => 'required|string|max:255',
            'convention_collective' => 'nullable|string|max:255',
            'chiffre_affaire' => 'nullable|numeric',
            'tranche_a' => 'nullable|numeric',
            'tranche_b' => 'nullable|numeric',
            'nombre_salaries' => 'nullable|integer',
            'moyenne_age' => 'nullable|integer',
            'nombre_salaries_cadres' => 'nullable|integer',
            'moyenne_age_cadres' => 'nullable|integer',
            'nombre_salaries_non_cadres' => 'nullable|integer',
            'moyenne_age_non_cadres' => 'nullable|integer',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        try {
            $data = $validator->validated();

            // Handle logo update
            if ($request->hasFile('logo')) {
                Storage::delete($company->logo);
                $data['logo'] = $request->file('logo')->store('company-logos', 'public');
            }

            $company->update($data);

            // Sync relationships
            if ($request->has('industries')) {
                $company->industries()->sync($request->industries);
            }

            if ($request->has('activities')) {
                $company->activities()->sync($request->activities);
            }

            return response()->json([
                'data' => $company->fresh(['industries', 'activities']),
                'message' => 'Company updated successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    { 
         $company = company::findOrFail($id);
        if (!$company) {
            return response()->json(['message' => 'Company not found'], 404);
        }
        // Storage::delete($company->logo);
        $company->delete();
        return response()->json([ "message" => 'Company deleted successfully'], 200);

    }
}
