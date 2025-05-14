<?php

namespace App\Http\Controllers;

use App\Models\company;
use App\Models\user;
use App\Mail\CompanyCreatedMail;
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
            'name' => 'nullable|string|max:255',
            'description' => 'required|string',
            'logo' => 'image|max:3072',
            'founded' => 'required|date',
            'raison_sociale' => 'required|string|max:255',
            
            'numero_tva' => 'nullable|string|max:255',
            'numero_siren' => 'nullable|string|max:255',
            
            'forme_juridique' => 'required|in:EIRL,SARL,EURL,SAS,SASU,SA',
            'code_company_type' => 'required|in:APE,NEF',
            'code_company_value' => 'required|string|max:255',
            'adresse_siege_social' => 'required|string|max:255',
            'code_postale' => 'required|string|max:10',
            'ville' => 'required|string|max:255',
            
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
            // creation user account
            $password = \Str::random(8); // Generate a random password
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => \Hash::make($password),
            ]);

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not created',
                ], 500);
            }
            // Assign role to user
            $user->assignRole('entreprise');
            // Attach user to company
            // Handle logo upload
            $data['logo'] = $request->file('logo')->store('company-logos', 'public');

            // send the user an email
            $table = [
                'view' => 'emails.companycreated',
                'subject' => 'welcome to our platform..you can now login',
                'data' => [
                    'name' => $user->name,
                    'email' => $user->email,
                    'password' => $password,
                ],
            ];
            //send mail to entreprise email
            \Mail::to($user->email)->send(new CompanyCreatedMail($table['view'], $table['subject'], $table['data'], null));

            $company = Company::create($data);
            $user->company_id = $company->id;
            $user->save();
            
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {

        $company = Company::findOrFail($id);
        if (!$company) {
            return response()->json(['message' => 'Company not found'], 404);
        }
        return $company;
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Company $company)
    {
        try {
            // Manual mapping from frontend keys to database columns
            $mappedData = [
                'company_name' => $request->raisonSociale,
                'industry' => $request->Industrie,
                'founded' => $request->date,
                'code_company_value' => $request->refCnss,
                'forme_juridique' => $request->formeJuridique,
                'code_company_type' => $request->activiteEntreprise,
                'adresse_siege_social' => $request->adresseSiegeSocial,
                'code_postale' => $request->zipCode,
                'ville' => $request->city,
                'email' => $request->email,
                'phone_number' => $request->phoneNumber,
                'numero_tva' => $request->matriculeFiscale,
                'numero_siren' => $request->siren,
                'status' => $request->status,
                'raison_sociale' => $request->raisonSociale,
                'chiffre_affaire' => $request->chiffreAffaire,
                'tranche_a' => $request->trancheA,
                'tranche_b' => $request->trancheB,
                'nombre_salaries' => $request->nombreSalaries,
                'moyenne_age' => $request->moyenneAge,
                'nombre_salaries_cadres' => $request->nombreSalariesCadres,
                'moyenne_age_cadres' => $request->moyenneAgeCadres,
                'nombre_salaries_non_cadres' => $request->nombreSalariesNonCadres,
                'moyenne_age_non_cadres' => $request->moyenneAgeNonCadres,
            ];

            // If logo is uploaded as a file (not just string name)
            if ($request->hasFile('logo')) {
                Storage::delete($company->logo);
                $mappedData['logo'] = $request->file('logo')->store('company-logos', 'public');
            }

            $company->fill($mappedData);
            $company->save();


            // Sync relationships if they exist
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
            \Log::error('Update company failed: ' . $e->getMessage());
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
        return response()->json(["message" => 'Company deleted successfully'], 200);

    }
}
