<?php

namespace App\Http\Controllers\Sport;

use App\Http\Controllers\Controller;
use App\Models\CategoryList;
use App\Models\GroupChat;
use App\Models\GroupChatUser;
use Illuminate\Http\Request;

// model
use App\Models\Sport;
use App\Models\User;
use App\Models\SportAssignment;
use Illuminate\Support\Facades\Redis;

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
            'image' => 'nullable|image|max:2048', // Allow image uploads
            'coach_id' => 'required|exists:users,id',
        ]);

        // Handle image upload
        $imagePath = $request->file('image') ? $request->file('image')->store('sports', 'public') : null;

        $sport = Sport::create([
            'name' => $request->name,
            'descriptions' => $request->descriptions,
            'image' => $imagePath,
            'created_by' => auth()->id(),
        ]);

        if ($sport) {
            // assign the coach
            SportAssignment::create([
                'sport_id' => $sport->id,
                'user_id' => $request->coach_id,
                'role' => 'coach',
            ]);

            // create a groupchat for that sports
            $groupChat = GroupChat::create([
                'sport_id' => $sport->id,
            ]);

            if($groupChat){
                GroupChatUser::create([
                    "group_chat_id"=>$groupChat->id,
                    "user_id"=>$request->coach_id,
                ]);
            }
        }

        return response()->json([
            'message' => 'Sport created successfully!',
            'sport' => $sport
        ], 201);
    }

    // edit sport
    public function edit(Request $request)
    {
        try {
            $validated = $request->validate([
                'sport_id' => 'required|exists:sports,id',
                'name' => 'required',
                'descriptions' => 'required',
                // 'image' => 'nullable|file|mimes:jpeg,png,jpg,gif,svg|max:2048', // Ensure it's a file
                'coach_id' => 'required|exists:users,id',
            ]);

            $sport = Sport::findOrFail($request->sport_id);

            // Handle image upload if a new image is provided
            if ($request->hasFile('image')) {
                $imagePath = $request->file('image')->store('sports_images', 'public');
                $sport->image = $imagePath;
            }

            // Update sport details
            $sport->update([
                'name' => $request->name,
                'descriptions' => $request->descriptions,
                'created_by' => auth()->id(),
            ]);

            if ($sport) {
                // Find the existing sport assignment
                $assignSport = SportAssignment::where('sport_id', $request->sport_id)
                    // ->where('user_id', $request->coach_id)
                    ->first();
                // return response()->json(['assign'=>$assignSport]);
                if ($assignSport) {
                    // Update if assignment exists
                    $assignSport->update(['user_id' => $request->coach_id]);
                } else {
                    // Create a new assignment if not found
                    SportAssignment::create([
                        'sport_id' => $request->sport_id,
                        'user_id' => $request->coach_id,
                    ]);
                }

            }

            return response()->json(['message' => 'Sport updated successfully!', 'sport' => $sport]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        }
    }

    //get sport by id
    public function getSportById(Request $request, $id)
    {
        $athletes = User::where('role','athlete')->get();
        $sport = Sport::with(['group','athletes'])->findOrFail($id);
        $categories = CategoryList::get();
        return response()->json([
            'status'=>200,
            'sport' => $sport,
            'athletes' => $athletes,
            'categories'=>$categories
        ]);
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
            'sport_id' => 'required|exists:sports,id',
            'athlete_id' => 'required|exists:users,id',
        ]);

        $athlete = User::where('id', $request->athlete_id)->where('role', 'athlete')->first();

        if (!$athlete) {
            return response()->json(['message' => 'Invalid athlete'], 400);
        }

        SportAssignment::create([
            'sport_id' => $request->sport_id,
            'user_id' => $athlete->id,
            'role' => 'athlete',
        ]);

        $groupChat = GroupChat::where('sport_id',$request->sport_id)->first();
        GroupChatUser::create([
            "group_chat_id"=>$groupChat->id,
            "user_id"=>$athlete->id,
        ]);

        return response()->json(['message' => 'Athlete assigned successfully']);
    }
}
