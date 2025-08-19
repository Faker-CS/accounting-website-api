<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use App\Models\company;
use App\Mail\SendCompanyFileMail;

class CompanyFileController extends Controller
{
    public function index($companyId)
    {
        $company = company::findOrFail($companyId);
        $files = Storage::disk('public')->files("company-files/{$company->id}");

        $fileUrls = array_map(function ($file) {
            $size = Storage::disk('public')->size($file);
            $mimeType = mime_content_type( Storage::disk('public')->path($file));
            $lastModified = Storage::disk('public')->lastModified($file);

            return [
                'url' => Storage::url($file),
                'name' => basename($file),
                'size' => $size,
                'type' => $mimeType,
                'lastModified' => $lastModified
            ];
        }, $files);

        return response()->json(['files' => $fileUrls]);
    }

    public function store(Request $request, $companyId)
    {
        $company = company::findOrFail($companyId);
        $request->validate([
            'file' => 'required|file|max:10240', // 10MB max
        ]);

        if (!$request->hasFile('file')) {
            return response()->json(['message' => 'No file uploaded'], 400);
        }

        $file = $request->file('file');

        // Get file information
        $originalName = $file->getClientOriginalName();
        $mimeType = $file->getMimeType();
        $fileSize = $file->getSize();

        // Generate a unique filename to prevent overwriting
        $extension = $file->getClientOriginalExtension();
        $filename = uniqid() . '_' . $originalName;

        // Store the file
        $path = $file->storeAs(
            "company-files/{$company->id}",
            $filename,
            'public'
        );

        if (!$path) {
            return response()->json(['message' => 'Failed to store file'], 500);
        }

        return response()->json([
            'url' => Storage::url($path),
            'name' => $originalName,
            'size' => $fileSize,
            'type' => $mimeType
        ]);
    }

    public function destroy(Request $request, $companyId)
    {
        $company = company::findOrFail($companyId);

        $request->validate([
            'filePath' => 'required|string'
        ]);

        $filePath = $request->filePath;

        // Extract just the relative path after /storage/
        $relativePath = preg_replace('/^\/storage\//', '', $filePath);

        // Ensure the path is within the company's directory
        if (!str_starts_with($relativePath, "company-files/{$company->id}/")) {
            return response()->json(['message' => 'Invalid file path'], 403);
        }

        // Check if file exists
        if (!Storage::disk('public')->exists($relativePath)) {
            return response()->json(['message' => 'File not found'], 404);
        }

        // Delete the file
        Storage::disk('public')->delete($relativePath);

        return response()->json(['message' => 'File deleted successfully']);
    }

    public function sendFileByEmail(Request $request, $companyId)
    {
        $company = company::findOrFail($companyId);
        $request->validate([
            'email' => 'required|email',
            'fileName' => 'required|string',
        ]);
        $fileName = $request->fileName;
        $email = $request->email;
        $filePath = "company-files/{$company->id}/{$fileName}";
        if (!\Storage::disk('public')->exists($filePath)) {
            return response()->json(['message' => 'File not found'], 404);
        }
        try {
            Mail::to($email)->send(new SendCompanyFileMail(
                $filePath,
                $fileName,
                'Livraison de document - ATFcompta+',
                ['recipient_email' => $email]
            ));

            Log::info('Company file sent successfully', [
                'company_id' => $company->id,
                'file_name' => $fileName,
                'recipient_email' => $email
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send company file', [
                'company_id' => $company->id,
                'file_name' => $fileName,
                'recipient_email' => $email,
                'error' => $e->getMessage()
            ]);
            return response()->json(['message' => 'Failed to send email: ' . $e->getMessage()], 500);
        }
        return response()->json(['message' => 'Email sent successfully']);
    }
}