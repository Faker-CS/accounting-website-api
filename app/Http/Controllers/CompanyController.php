<?php

namespace App\Http\Controllers;

use App\Models\company;
use App\Models\user;
use App\Models\Service;
use App\Models\CompanyService;
use App\Mail\CompanyCreatedMail;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

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
            $query = Company::with(['user'])
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
            'logo' => 'nullable|image|max:3072',
            'founded' => 'required|date',
            'raison_sociale' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone_number' => 'required|string|max:20',
            'numero_siren' => 'nullable|string|max:255',
            'forme_juridique' => 'required|in:SARL-S,SARL,SUARL,SA,SNC',
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
            'Industrie' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        DB::beginTransaction();
        try {
            $data = $validator->validated();

            // Convert founded date to Y-m-d format for MySQL
            if (isset($data['founded'])) {
                $data['founded'] = date('Y-m-d', strtotime($data['founded']));
            }

            // Handle logo upload first
            $logoPath = null;
            if ($request->hasFile('logo')) {
                $logoPath = $request->file('logo')->store('company-logos', 'public');
                $data['logo'] = $logoPath;
            }

            // Create user account
            $password = \Str::random(8);
            $user = User::create([
                'name' => $data['name'] ?? $data['raison_sociale'],
                'email' => $data['email'],
                'password' => \Hash::make($password),
                'phoneNumber' => $data['phone_number'],
                'address' => $data['adresse_siege_social'],
                'zipCode' => $data['code_postale'],
                'city' => $data['ville'],
                'photo' => $logoPath, // set user photo to logo if uploaded
            ]);
            if (!$user) {
                DB::rollBack();
                return response()->json(['success' => false, 'message' => 'User not created'], 500);
            }
            $user->assignRole('entreprise');

            // Company logo: use uploaded logo or user's photo
            $companyLogo = $logoPath ?? $user->photo;

            // Create company
            $company = Company::create([
                'company_name' => $data['name'] ?? $data['raison_sociale'],
                'logo' => $companyLogo,
                'founded' => $data['founded'],
                'raison_sociale' => $data['raison_sociale'],
                'email' => $data['email'],
                'phone_number' => $data['phone_number'],
                'numero_siren' => $data['numero_siren'] ?? null,
                'forme_juridique' => $data['forme_juridique'],
                'code_company_type' => $data['code_company_type'],
                'code_company_value' => $data['code_company_value'],
                'adresse_siege_social' => $data['adresse_siege_social'],
                'code_postale' => $data['code_postale'],
                'ville' => $data['ville'],
                'chiffre_affaire' => $data['chiffre_affaire'] ?? null,
                'tranche_a' => $data['tranche_a'] ?? null,
                'tranche_b' => $data['tranche_b'] ?? null,
                'nombre_salaries' => $data['nombre_salaries'] ?? null,
                'moyenne_age' => $data['moyenne_age'] ?? null,
                'nombre_salaries_cadres' => $data['nombre_salaries_cadres'] ?? null,
                'moyenne_age_cadres' => $data['moyenne_age_cadres'] ?? null,
                'nombre_salaries_non_cadres' => $data['nombre_salaries_non_cadres'] ?? null,
                'moyenne_age_non_cadres' => $data['moyenne_age_non_cadres'] ?? null,
                'user_id' => $user->id,
                'status' => 'Pending',
                'industry' => $data['Industrie']
            ]);
            if (!$company) {
                DB::rollBack();
                return response()->json(['success' => false, 'message' => 'Company not created'], 500);
            }

            // Update user with company_id
            $user->company_id = $company->id;
            $user->save();

            // Assign default services to the new company
            $defaultServices = Service::where('is_default', true)->get();
            foreach ($defaultServices as $service) {
                CompanyService::create([
                    'company_id' => $company->id,
                    'service_id' => $service->id,
                    'frequency' => $service->period_type,
                    'status' => 'actif',
                    'added_by' => 'admin',
                ]);
            }

            // Send welcome email
            $emailData = [
                'view' => 'emails.companycreated',
                'subject' => 'Welcome to MoneyTeers - Your Company Account Is Ready!',
                'data' => [
                    'name' => $user->name,
                    'email' => $user->email,
                    'password' => $password,
                ],
            ];
            try {
            \Mail::to($user->email)->send(new CompanyCreatedMail(
                $emailData['view'],
                $emailData['subject'],
                $emailData['data'],
                null
            ));
            } catch (\Exception $e) {
                \Log::error('Failed to send welcome email: ' . $e->getMessage());
            }

            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Company created successfully',
                'data' => $company
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Company creation failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Company creation failed: ' . $e->getMessage()
            ], 500);
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
                'company_name' => $request->raisonSociale ?? $request->name,
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
