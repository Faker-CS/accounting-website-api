<?php

namespace App\Http\Controllers;

use Ramsey\Uuid\Uuid;
use Illuminate\Http\Request;
use App\Models\Event;
use App\Models\User;
use App\Models\Notification;
use App\Events\FormSubmitted;
use Illuminate\Support\Facades\Validator;




class EventController extends Controller
{
    public function index()
    {
        $events = Event::all()->map(function ($event) {
            return [
                'id' =>$event->id,
                'title' => $event->title,
                'description' => $event->description,
                'color' => $event->color,
                'allDay' => $event->all_day,
                'start' => $event->start->toIso8601String(),
                'end' => $event->end ? $event->end->toIso8601String() : null,
            ];
        });
        \Log::info('Fetched events', [
            'events' => $events,
        ]);

        return response()->json(['events' => $events], 200);
    }

    // Create a new event
    public function store(Request $request)
    {
        \Log::info('Event creation request', [
            'request' => $request->all(),
        ]);
        $validator = Validator::make($request->all(), [
            'eventData.title' => 'required|string|max:255',
            'eventData.description' => 'nullable|string',
            'eventData.color' => 'nullable|string|max:7', // e.g., #00B8D9
            'eventData.allDay' => 'required|boolean',
            'eventData.start' => 'required|date',
            'eventData.end' => 'nullable|date|after_or_equal:start',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $eventData = $request->input('eventData');

        $event = Event::create([
            // 'id' => $request['eventData.id'] ?? Uuid::uuid4()->toString(),
            'title' => $eventData['title'],
            'description' => $eventData['description'] ?? null,
            'color' => $eventData['color'] ?? null,
            'all_day' => $eventData['allDay'],
            'start' => $eventData['start'],
            'end' => $eventData['end'] ?? null,
        ]);

        // Notify all aide-comptables about the new event
        $aideComptables = User::role('aide-comptable')->get();
        $eventDate = \Carbon\Carbon::parse($event->start)->format('d/m/Y H:i');

        foreach ($aideComptables as $aideComptable) {
            // Create notification in database
            Notification::create([
                'user_id' => $aideComptable->id,
                'type' => 'new_event',
                'title' => "You have new event : <strong>{$event->title}</strong> le {$eventDate}",
                'serviceLink' => "/dashboard/calendar",
                'isUnRead' => true,
            ]);

            // Broadcast the event
            broadcast(new FormSubmitted([
                'title' => "You have new event : <strong>{$event->title}</strong> le {$eventDate}",
                'type' => 'new_event',
                'link' => "/dashboard/calendar"
            ], $aideComptable->id));
        }

        return response()->json([
            'id' => $event->id,
            'title' => $event->title,
            'description' => $event->description,
            'color' => $event->color,
            'allDay' => $event->all_day,
            'start' => $event->start->toIso8601String(),
            'end' => $event->end ? $event->end->toIso8601String() : null,
        ], 201);
    }

    // Update an existing event
    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'color' => 'nullable|string|max:7',
            'allDay' => 'required|boolean',
            'start' => 'required|date',
            'end' => 'nullable|date|after_or_equal:start',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $eventData = $request->all();
        $event = Event::where('id', $eventData['id'])->firstOrFail();

        $event->update([
            'title' => $eventData['title'],
            'description' => $eventData['description'] ?? null,
            'color' => $eventData['color'] ?? null,
            'all_day' => $eventData['allDay'],
            'start' => $eventData['start'],
            'end' => $eventData['end'] ?? null,
        ]);

        return response()->json([
            'id' => $event->id,
            'title' => $event->title,
            'description' => $event->description,
            'color' => $event->color,
            'allDay' => $event->all_day,
            'start' => $event->start->toIso8601String(),
            'end' => $event->end ? $event->end->toIso8601String() : null,
        ], 200);
    }

    // Delete an event
    public function destroy( $id)
    {
        
        $event = Event::where('id', $id)->firstOrFail();
        $event->delete();

        return response()->json(['message' => 'Event deleted successfully'], 200);
    }
}
