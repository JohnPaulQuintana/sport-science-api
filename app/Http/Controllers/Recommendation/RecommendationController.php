<?php

namespace App\Http\Controllers\Recommendation;

use App\Http\Controllers\Controller;
use App\Models\Recommendation;
use Illuminate\Http\Request;

class RecommendationController extends Controller
{
    //insert
    public function insert(Request $request)
    {
        $request->validate([
            'athlete_id' => 'required',
            'category' => 'required',
            'recommendation' => 'required',
            'sport' => 'required',
        ]);

        $recommendation = Recommendation::create([
            'user_id' => $request->athlete_id,
            'category' => $request->category,
            'recommendation' => $request->recommendation,
            'sport' => $request->sport
        ]);
        return response()->json([
            'recommendation',
            $recommendation
        ]);
    }

    //update
    public function update(Request $request)
    {
        $request->validate([
            'recom_id' => 'required|exists:recommendations,id',
            'recommendation' => 'required|string',
        ]);

        $recommendation = Recommendation::findOrFail($request->recom_id);

        $recommendation->recommendation = $request->recommendation;
        $recommendation->save();

        return response()->json([
            'message' => 'Recommendation updated successfully.',
            'data' => $recommendation
        ], 200);
    }

    //delete
    public function delete(Request $request)
    {
        $request->validate([
            'recom_id' => 'required|exists:recommendations,id',
        ]);

        $recommendation = Recommendation::findOrFail($request->recom_id);

        if ($recommendation) {
            $recommendation->delete();
            return response()->json([
                'message' => 'Recommendation deleted successfully.'
            ], 200);
        } else {
            return response()->json([
                'message' => 'Recommendation not deleted successfully.'
            ], 404);
        }
    }
}
