<?php

namespace App\Http\Controllers\Sport;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

// model
use App\Models\Sport;
use App\Models\User;
use App\Models\SportAssignment;

class SportController extends Controller
{
    // get the total sports
    public function totalSport()
    {
        $sports = Sport::get();
    }
    /**
     * Admin creates a new sport.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|unique:sports',
            'descriptions' => 'required',
            'image' => 'nullable|image|max:2048' // Allow image uploads
        ]);

        // Handle image upload
        $imagePath = $request->file('image') ? $request->file('image')->store('sports', 'public') : null;

        $sport = Sport::create([
            'name' => $request->name,
            'descriptions' => $request->descriptions,
            'image' => $imagePath,
            'created_by' => auth()->id(),
        ]);

        return response()->json([
            'message' => 'Sport created successfully!',
            'sport' => $sport
        ], 201);
    }

    /**
     * Admin assigns a coach to a sport.
     */
    public function assignCoach(Request $request, Sport $sport)
    {
        $request->validate([
            'coach_id' => 'required|exists:users,id',
        ]);

        $coach = User::where('id', $request->coach_id)->where('role', 'coach')->first();

        if (!$coach) {
            return response()->json(['message' => 'Invalid coach'], 400);
        }

        SportAssignment::create([
            'sport_id' => $sport->id,
            'user_id' => $coach->id,
            'role' => 'coach',
        ]);

        return response()->json(['message' => 'Coach assigned successfully']);
    }

    /**
     * Coach assigns an athlete to a sport.
     */
    public function assignAthlete(Request $request, Sport $sport)
    {
        $request->validate([
            'athlete_id' => 'required|exists:users,id',
        ]);

        $athlete = User::where('id', $request->athlete_id)->where('role', 'athlete')->first();

        if (!$athlete) {
            return response()->json(['message' => 'Invalid athlete'], 400);
        }

        SportAssignment::create([
            'sport_id' => $sport->id,
            'user_id' => $athlete->id,
            'role' => 'athlete',
        ]);

        return response()->json(['message' => 'Athlete assigned successfully']);
    }
}
