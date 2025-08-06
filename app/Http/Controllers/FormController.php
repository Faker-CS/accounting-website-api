<?php
namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Document;
use App\Models\Form;
use App\Models\Notification;
use App\Models\UserDocuments;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
// use App\Mail\ChangeStatutsMail;
use App\Events\FormSubmitted;
use Illuminate\Support\Facades\Mail;
use App\Models\Task;

class FormController extends Controller
{
    public function submitForm(Request $request, $serviceId)
    {
        $user = Auth::user();

        $form = Form::where('user_id', $user->id)
            ->where('service_id', $serviceId)
            ->with('service') // Charger le nom du service
            ->first();

        if (!$form) {
            return response()->json(['status' => 'form_not_found']);
        }

        if ($form->status === "pending" || trim($form->status) === "") {
            $form->status = 'review';
            $form->save();

            // Update company status from Pending to Active if this is the first demand
            $company = $user->company;
            if ($company && $company->status === 'Pending') {
                $company->status = 'Active';
                $company->save();
            }

            // create a task for the form
            if($form->service->type !== 'Authorization Request'){
                Task::create([
                    'form_id' => $form->id,
                    'title' => $form->service->name,
                    'description' => "New demand for {$form->service->name}",
                    'reporter_id' => $user->id,
                    'assignee_id' => null,
                    'due_date' => now()->addDays(7),
                    'priority' => 'Medium',
                    'status' => 'To Do'
                ]);
            }

            // Notification pour l'utilisateur
            Notification::create([
                'user_id' => $user->id,
                'type' => 'form_submission',
                'title' => 'Your form has been submitted for review.',
                'serviceLink' => $serviceId,
                'isUnRead' => true,
            ]);
            broadcast(new FormSubmitted([
                'title' => 'Your form has been submitted for review.',
                'type' => 'form_submission',
                'link' => $serviceId
            ], $user->id));


            // Notification pour le comptable
            $comptable = User::role('comptable')->first();

            if ($comptable) {
                $notif = Notification::create([
                    'user_id' => $comptable->id,
                    'type' => 'form_submission',
                    'title' => "New form submission from {$user->name} for the service '{$form->service->name}'.",
                    'serviceLink' => "/dashboard/forms/{$form->id}",
                    'isUnRead' => true,
                ]);

                $event = new FormSubmitted([
                    'title' => $notif->title,
                    'type' => 'form_submission',
                    'link' => $notif->serviceLink
                ], $comptable->id);

                broadcast($event);
            }

            return response()->json(['status' => 'submitted_for_review']);
        }

        if ($form->status === "review") {
            return response()->json(['status' => 'form_in_review']);
        }

        if ($form->status === "accepted") {
            return response()->json(['status' => 'form_accepted']);
        }

        return response()->json(['status' => 'unknown_error']);
    }

    public function getForms()
    {
        $user = auth()->user();
        if ($user->hasRole('aide-comptable')) {
            $forms = Form::with(['user', 'service', 'helperForms.user'])
                ->whereHas('helperForms', function ($query) use ($user) {
                    $query->where('user_id', $user->id);
                })->get();
        } else {
            $forms = Form::with(['user', 'service', 'helperForms.user'])->get();
        }

        \Log::info('Forms retrieved', [
            'forms_count' => $forms->count(),
            'forms' => $forms,
        ]);

        return response()->json($forms);
    }
    public function destroy($id)
    {
        $form = Form::with(['user', 'service'])->find($id);

        if (!$form) {
            return response()->json(['message' => 'Formulaire introuvable'], 404);
        }

        $user = $form->user;
        $serviceName = $form->service->name ?? 'the service in question';
        $serviceId = $form->service_id;

        // Delete associated documents
        $userDocuments = UserDocuments::where('form_id', $form->id)->get();

        foreach ($userDocuments as $userDocument) {
            if (Storage::exists($userDocument->file_path)) {
                Storage::delete($userDocument->file_path);
            }
        }

        UserDocuments::where('form_id', $form->id)->delete();

        // Send notification before deletion
        if ($user) {
            Notification::create([
                'user_id' => $user->id,
                'type' => 'form_deleted',
                'title' => "Your form for {$serviceName} has been deleted !",
                'serviceLink' => "/dashboard/forms", // or anywhere you redirect for forms list
                'isUnRead' => true,
            ]);
            broadcast(new FormSubmitted([
                'title' => "Your form for {$serviceName} has been deleted !",
                'type' => 'form_deleted',
                'link' => "/dashboard/forms"
            ], $user->id));


        }

        $form->delete();

        return response()->json(['message' => 'Formulaire et documents associés supprimés avec succès']);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|string|in:pending,review,rejected,accepted,in_work',
        ]);

        $form = Form::with(['user', 'service'])->find($id); // Eager load user and service

        if (!$form) {
            return response()->json(['message' => 'Formulaire introuvable'], 404);
        }

        if ($form->status === $request->status) {
            return response()->json(['message' => 'Le statut est déjà défini à cette valeur'], 400);
        }

        $form->status = $request->status;
        $form->save();

        $user = $form->user;
        $serviceName = $form->service->name ?? 'le service concerné';

        $messages = [
            'accepted' => "Your form for {$serviceName} has been accepted. Thank you for your submission.",
            'rejected' => "Your form for {$serviceName} is missing some files. Please review the provided information.",
            'pending' => "Your form for {$serviceName} is checked by our and we completed it. Please review the verification files.",
            'review' => "Your form for {$serviceName} is under review. We will keep you informed shortly.",
            'in_work' => "Your form for {$serviceName} is now being processed by our team."
        ];

        $types = [
            'accepted' => 'form_accepted',
            'rejected' => 'form_rejection',
            'pending' => 'form_submission',
            'review' => 'form_submission',
            'in_work' => 'form_in_work'
        ];

        if ($user) {
            Notification::create([
                'user_id' => $user->id,
                'type' => $types[$form->status],
                'title' => $messages[$form->status],
                'serviceLink' => "/dashboard/forms/{$form->id}",
                'isUnRead' => true,
            ]);
            broadcast(new FormSubmitted([
                'title' => $messages[$form->status],
                'type' => $types[$form->status],
                'link' => "/dashboard/forms/{$form->id}"
            ], $user->id));
        }

        return response()->json([
            'message' => 'Statut du formulaire mis à jour avec succès',
            'form' => $form,
        ]);
    }

    public function acceptDemand($id)
    {
        $form = Form::with(['user', 'service'])->find($id);

        if (!$form) {
            return response()->json(['message' => 'Formulaire introuvable'], 404);
        }

        // Change status to in_work
        $form->status = 'in_work';
        $form->save();

        $user = $form->user;
        $serviceName = $form->service->name ?? 'le service concerné';

        if ($user) {
            Notification::create([
                'user_id' => $user->id,
                'type' => 'form_in_work',
                'title' => "Your form for {$serviceName} is now being processed by our team.",
                'serviceLink' => "/dashboard/forms/{$form->id}",
                'isUnRead' => true,
            ]);
            broadcast(new FormSubmitted([
                'title' => "Your form for {$serviceName} is now being processed by our team.",
                'type' => 'form_in_work',
                'link' => "/dashboard/forms/{$form->id}"
            ], $user->id));
        }

        return response()->json([
            'message' => 'Demand accepted and status updated to in work',
            'form' => $form,
        ]);
    }

    public function get($id)
    {
        $form = Form::with(['user', 'service'])->findOrFail($id);

        // Get all required documents for the service
        $documents = Document::where('service_id', $form->service_id)->get();

        // Get uploaded user documents for this form
        $userDocuments = $form->userDocuments()->get()->groupBy('document_id');

        // Attach the user_documents array (even if only one or none) to each document
        $documentsWithUserFiles = $documents->map(function ($document) use ($userDocuments) {
            $document->user_document = $userDocuments->get($document->id)?->values() ?? [];
            return $document;
        });

        // Add the documents to the form object
        $form->documents = $documentsWithUserFiles;

        return response()->json([
            'message' => 'Found!',
            'form' => $form,
        ]);
    }

    public function documentDelete($id)
    {
        // Retrieve the UserDocument by ID with the related form
        $userDocument = UserDocuments::with('form')->find($id);

        if (!$userDocument) {
            return response()->json(['message' => 'User document introuvable.'], 404); // Not Found
        }
        // Get the file path to delete it
        $filePath = $userDocument->file_path;

        // Delete the file from storage if it exists
        if (Storage::exists($filePath)) {
            Storage::delete($filePath);
        }

        // Save form ID before deleting
        $formId = $userDocument->form_id;

        // Delete the document
        $userDocument->delete();

        // If no documents remain, delete the form too
        $remainingDocuments = UserDocuments::where('form_id', $formId)->count();
        if ($remainingDocuments === 0) {
            Form::where('id', $formId)->delete();
        }

        return response()->json(['message' => 'Document et fichier supprimés avec succès'], 200);
    }

    public function getStatistics()
    {
        $months = collect();
        for ($i = 7; $i >= 0; $i--) {
            $months->push(Carbon::now()->subMonths($i)->startOfMonth());
        }

        $usersMonthly = $months->map(function ($date) {
            return User::whereBetween('created_at', [$date, $date->copy()->endOfMonth()])->count();
        });

        $formsMonthly = $months->map(function ($date) {
            return Form::whereBetween('created_at', [$date, $date->copy()->endOfMonth()])->count();
        });

        $documentsMonthly = $months->map(function ($date) {
            return UserDocuments::whereBetween('created_at', [$date, $date->copy()->endOfMonth()])->count();
        });

        return response()->json([
            'totalUsers' => User::count(),
            'totalForms' => Form::count(),
            'totalDocuments' => UserDocuments::count(),

            'usersMonthly' => $usersMonthly,
            'formsMonthly' => $formsMonthly,
            'documentsMonthly' => $documentsMonthly,
        ]);
    }

}