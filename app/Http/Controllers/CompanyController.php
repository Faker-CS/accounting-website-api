<?php

namespace App\Http\Controllers;

use App\Models\company;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Gate;

class CompanyController extends Controller implements HasMiddleware
{
    public static function middleware()
    {
        return
        [
            new Middleware('auth:sanctum', except : ['index', 'show']),
        ];
        
    }


    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return company::all();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'raison_sociale' => 'required',
            'description' => 'required ',
            'address' => 'required|string',
            'founded' => 'required|date',
            'forme_juridique' => 'required|in:EIRL,SARL,EURL,SAS,SASU,SA',
            'code_company_type' => 'required|in:APE,NEF',
            'numero_siret' => 'required|string|max:14',
            'capital_social' => 'nullable|numeric',
            'numero_tva' => 'nullable|string',
            'numero_siren' => 'nullable|string',
            'logo' => 'nullable|string',
            'company_name' => 'required|string',
            
            
            'code_company_value' => 'required|string',
            'status_id' => 'required|exists:statuses,id',
        ]);

        $company = $request->user()->companies()->create($validatedData);

        return response()->json($company, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(company $company)
    {
        return $company;
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, company $company)
    {
        Gate::authorize('modify', $company);

        $validatedData = $request->validate([
            'raison_sociale' => 'required',
            'description' => 'required ',
            'address' => 'required|string',
            'founded' => 'required|date',
            'forme_juridique' => 'required|in:EIRL,SARL,EURL,SAS,SASU,SA',
            'code_company_type' => 'required|in:APE,NEF',
            'numero_siret' => 'required|string|max:14',
            'capital_social' => 'nullable|numeric',
            'numero_tva' => 'nullable|string',
            'numero_siren' => 'nullable|string',
            'logo' => 'nullable|string',
            
            
            'status_id' => 'required|exists:statuses,id',
        ]);

        $company->update($validatedData);

        return response()->json($company, 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(company $company)
    {
        $company->delete();

        return response()->json(null, 204);
    }
}
