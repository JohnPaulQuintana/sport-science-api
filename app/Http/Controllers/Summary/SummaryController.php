<?php

namespace App\Http\Controllers\Summary;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
// models
use App\Models\Sport;
use App\Models\SportAssignment;
use App\Models\User;

class SummaryController extends Controller
{
    public function summary()
    {
        $counts = User::whereIn('role', ['coach', 'athlete'])
            ->selectRaw("SUM(role = 'coach') as coaches, SUM(role = 'athlete') as athletes")
            ->first();

        $sports = Sport::orderBy('created_at', 'desc')->get();

        $users = User::where('role','!=','admin')->get();

        return response()->json([
            'sports_record' => $sports,
            'user_record' => $users,
            'sports' => Sport::count(),
            'coaches' => (int) $counts->coaches ?? 0,
            'athletes' => (int) $counts->athletes ?? 0,
        ]);
    }

    //for coach dashboard
    public function summaryCoach()
    {
        // $sports = Sport::where()->orderBy('created_at', 'desc')->get();
        $sports = SportAssignment::with('sport')->where('user_id',auth()->id())->get();
        return response()->json([
            'assign_sports' => $sports,
            'sports' => count($sports)
        ]);
    }
    //for athlete dashboard
    public function summaryAthelete()
    {
        // $sports = Sport::where()->orderBy('created_at', 'desc')->get();
        $sports = SportAssignment::with('sport')->where('user_id',auth()->id())->get();
        return response()->json([
            'assign_sports' => $sports,
            'sports' => count($sports)
        ]);
    }

}
