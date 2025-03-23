<?php

namespace App\Http\Controllers\Summary;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
// models
use App\Models\Sport;
use App\Models\User;

class SummaryController extends Controller
{
    public function summary()
    {
        $counts = User::whereIn('role', ['coach', 'athlete'])
            ->selectRaw("SUM(role = 'coach') as coaches, SUM(role = 'athlete') as athletes")
            ->first();

        $sports = Sport::get();

        return response()->json([
            'sports_record' => $sports,
            'sports' => Sport::count(),
            'coaches' => (int) $counts->coaches ?? 0,
            'athletes' => (int) $counts->athletes ?? 0,
        ]);
    }

}
