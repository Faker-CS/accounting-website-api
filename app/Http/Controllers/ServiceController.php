<?php
namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\Form;
use App\Models\Service;
use App\Models\Company;
use App\Models\CompanyService;
use App\Models\UserDocuments;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ServiceController extends Controller 
{
    public function index()
    {
        $services = Service::orderBy('name')->get();
        return response()->json($services);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'period_type' => 'required|in:mensuelle,trimestrielle,annuelle',
            'is_default' => 'boolean',
            'price' => 'nullable|numeric|min:0',
            'requirements' => 'nullable|string',
        ]);

        $service = Service::create($request->all());
        return response()->json($service, 201);
    }

    public function show($id)
    {
        $service = Service::findOrFail($id);
        return response()->json($service);
    }

    public function update(Request $request, $id)
    {
        $service = Service::findOrFail($id);
        
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'period_type' => 'required|in:mensuelle,trimestrielle,annuelle',
            'is_default' => 'boolean',
            'price' => 'nullable|numeric|min:0',
            'requirements' => 'nullable|string',
        ]);

        $service->update($request->all());
        return response()->json($service);
    }

    public function destroy($id)
    {
        $service = Service::findOrFail($id);
        
        // Check if service is being used by any company
        $usedByCompanies = CompanyService::where('service_id', $id)->exists();
        
        if ($usedByCompanies) {
            return response()->json([
                'message' => 'Cannot delete service as it is currently assigned to companies'
            ], 400);
        }

        $service->delete();
        return response()->json(['message' => 'Service deleted successfully']);
    }

    // Get all services with company assignment status
    public function getServicesWithCompanyStatus($companyId)
    {
        $company = Company::findOrFail($companyId);
        $allServices = Service::orderBy('name')->get();
        
        $assignedServices = $company->companyServices()
            ->with('service')
            ->get()
            ->keyBy('service_id');

        $servicesWithStatus = $allServices->map(function ($service) use ($assignedServices) {
            $assigned = $assignedServices->get($service->id);
            
            return [
                'id' => $service->id,
                'name' => $service->name,
                'description' => $service->description,
                'period_type' => $service->period_type,
                'is_default' => $service->is_default,
                'price' => $service->price,
                'requirements' => $service->requirements,
                'is_assigned' => $assigned ? true : false,
                'assignment' => $assigned ? [
                    'frequency' => $assigned->frequency,
                    'status' => $assigned->status,
                    'declaration_date' => $assigned->declaration_date,
                    'added_by' => $assigned->added_by,
                    'notes' => $assigned->notes,
                ] : null,
            ];
        });

        return response()->json($servicesWithStatus);
    }

    // Assign service to company
    public function assignServiceToCompany(Request $request, $companyId)
    {
        $request->validate([
            'service_id' => 'required|exists:services,id',
            'frequency' => 'required|in:mensuelle,trimestrielle,annuelle',
            'declaration_date' => 'nullable|date',
            'notes' => 'nullable|string',
        ]);

        $company = Company::findOrFail($companyId);
        
        // Check if service is already assigned
        $existingAssignment = CompanyService::where('company_id', $companyId)
            ->where('service_id', $request->service_id)
            ->first();

        if ($existingAssignment) {
            return response()->json([
                'message' => 'Service is already assigned to this company'
            ], 400);
        }

        $companyService = CompanyService::create([
            'company_id' => $companyId,
            'service_id' => $request->service_id,
            'frequency' => $request->frequency,
            'status' => 'actif',
            'declaration_date' => $request->declaration_date,
            'added_by' => Auth::user()->role === 'comptable' ? 'comptable' : 'entreprise',
            'notes' => $request->notes,
        ]);

        return response()->json($companyService->load('service'), 201);
    }

    // Update company service assignment
    public function updateCompanyService(Request $request, $companyId, $serviceId)
    {
        $request->validate([
            'frequency' => 'required|in:mensuelle,trimestrielle,annuelle',
            'status' => 'required|in:actif,inactif,annulé',
            'declaration_date' => 'nullable|date',
            'notes' => 'nullable|string',
        ]);

        $companyService = CompanyService::where('company_id', $companyId)
            ->where('service_id', $serviceId)
            ->firstOrFail();

        $companyService->update($request->all());
        
        return response()->json($companyService->load('service'));
    }

    // Remove service from company
    public function removeServiceFromCompany($companyId, $serviceId)
    {
        $companyService = CompanyService::where('company_id', $companyId)
            ->where('service_id', $serviceId)
            ->firstOrFail();

        $companyService->delete();
        
        return response()->json(['message' => 'Service removed from company successfully']);
    }

    // Get company's active services
    public function getCompanyServices($companyId)
    {
        $company = Company::findOrFail($companyId);
        $services = $company->companyServices()
            ->with('service')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($services);
    }

    // Assign default services to new company
    public function assignDefaultServicesToCompany($companyId)
    {
        $company = Company::findOrFail($companyId);
        $defaultServices = Service::where('is_default', true)->get();

        $assignedServices = [];
        
        foreach ($defaultServices as $service) {
            $companyService = CompanyService::create([
                'company_id' => $companyId,
                'service_id' => $service->id,
                'frequency' => $service->period_type,
                'status' => 'actif',
                'added_by' => 'admin',
            ]);
            
            $assignedServices[] = $companyService->load('service');
        }

        return response()->json([
            'message' => 'Default services assigned successfully',
            'services' => $assignedServices
        ]);
    }
}