<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use Illuminate\Http\Request;

class EmployeeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Employee::with('company');




        if ($request->has('company_id')) {
            $query->where('company_id', $request->company_id);
        }

        return response()->json($query->get(), 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'company_id' => 'required|exists:companies,id',
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'cin' => 'required|string|max:8|unique:employees,cin',
            'hiring_date' => 'required|date',
            'contract_end_date' => 'nullable|date|after_or_equal:hiring_date',
            'contract_type' => 'required|string',
            'salary' => 'required|numeric|min:0',
            'status' => 'required|string|in:working,not_working',
        ]);

        $employee = Employee::create($validated);

        return response()->json($employee, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Employee $employee)
    {
        return response()->json($employee, 200);
    }

    /**
     * Get employees by company ID.
     */
    public function getByCompany($companyId)
    {
        $employees = Employee::where('company_id', $companyId)->get();
        return response()->json($employees);
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Employee $employee)
    {
        $validated = $request->validate([
            'company_id' => 'sometimes|required|exists:companies,id',
            'first_name' => 'sometimes|required|string|max:255',
            'last_name' => 'sometimes|required|string|max:255',
            'cin' => 'sometimes|required|string|max:8|unique:employees,cin,' . $employee->id,
            'hiring_date' => 'sometimes|required|date',
            'contract_end_date' => 'nullable|date|after_or_equal:hiring_date',
            'contract_type' => 'sometimes|required|string',
            'salary' => 'sometimes|required|numeric|min:0',
            'status' => 'sometimes|required|string|in:working,not_working',
        ]);

        $employee->update($validated);

        return response()->json($employee, 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Employee $employee)
    {
        $employee->delete();

        return response()->json(null, 204);
    }
}
