<?php

namespace App\Http\Controllers\EventScheduling;

use App\Http\Controllers\Controller;
use App\Models\EventScheduling;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class EventSchedulingController extends Controller
{
    //insert
    public function addEvent(Request $request)
    {
        $validated = $request->validate([
            'sport_id' => 'required',  // Title is required, must be a string, max 255 characters
            'title' => 'required|string|max:255',  // Title is required, must be a string, max 255 characters
            'start' => 'required|date',           // Start time is required and must be a valid date
            'end' => 'required|date|after_or_equal:start',  // End time is required, must be a valid date, and should be after or equal to the start date
            'description' => 'nullable|string',   // Description is optional but must be a string if provided
        ]);

        $event = EventScheduling::create([
            'sport_id' => $validated['sport_id'],
            'title' => $validated['title'],
            'start' => $validated['start'],
            'end' => $validated['end'],
            'description' => $validated['description'] ?? '',  // Default to an empty string if description is not provided
        ]);

        // Return a success response
        return response()->json([
            'message' => 'Event created successfully!',
            'event' => $event,
        ], 201); // HTTP 201 created
    }

    // Update an existing event
    public function update(Request $request, $id)
    {
        $event = EventScheduling::find($id);

        if (!$event) {
            return response()->json(['error' => 'Event not found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'sport_id' => 'required|exists:sports,id',
            'start' => 'required|date',
            'end' => 'required|date|after_or_equal:start',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        $event->update([
            'title' => $request->title,
            'description' => $request->description,
            'sport_id' => $request->sport_id,
            'start' => $request->start,
            'end' => $request->end,
        ]);

        return response()->json($event);
    }

    // Delete an event
    public function destroy($id)
    {
        $event = EventScheduling::find($id);

        if (!$event) {
            return response()->json(['error' => 'Event not found'], 404);
        }

        $event->delete();

        return response()->json(['message' => 'Event deleted successfully']);
    }
}
