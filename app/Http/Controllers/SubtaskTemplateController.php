<?php

namespace App\Http\Controllers;

use App\Models\SubtaskTemplate;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SubtaskTemplateController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $serviceId = $request->query('service_id');
        
        if ($serviceId) {
            $templates = SubtaskTemplate::where('service_id', $serviceId)
                ->orderBy('order')
                ->get();
        } else {
            $templates = SubtaskTemplate::with('service')
                ->orderBy('service_id')
                ->orderBy('order')
                ->get();
        }

        return response()->json($templates);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'service_id' => 'required|exists:services,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'order' => 'nullable|integer|min:0'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // Set default order if not provided
        if (!$request->has('order')) {
            $maxOrder = SubtaskTemplate::where('service_id', $request->service_id)
                ->max('order');
            $request->merge(['order' => ($maxOrder ?? -1) + 1]);
        }

        $template = SubtaskTemplate::create($request->all());
        $template->load('service');

        return response()->json($template, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(SubtaskTemplate $subtaskTemplate)
    {
        $subtaskTemplate->load('service');
        return response()->json($subtaskTemplate);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, SubtaskTemplate $subtaskTemplate)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'order' => 'sometimes|integer|min:0'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $subtaskTemplate->update($request->all());
        $subtaskTemplate->load('service');

        return response()->json($subtaskTemplate);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(SubtaskTemplate $subtaskTemplate)
    {
        $subtaskTemplate->delete();
        return response()->json(['message' => 'Subtask template deleted successfully']);
    }

    /**
     * Reorder subtask templates for a service
     */
    public function reorder(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'service_id' => 'required|exists:services,id',
            'template_ids' => 'required|array',
            'template_ids.*' => 'exists:subtask_templates,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        foreach ($request->template_ids as $index => $templateId) {
            SubtaskTemplate::where('id', $templateId)
                ->where('service_id', $request->service_id)
                ->update(['order' => $index]);
        }

        return response()->json(['message' => 'Templates reordered successfully']);
    }
}
