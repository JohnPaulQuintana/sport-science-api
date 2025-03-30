<?php

namespace App\Http\Controllers\Athlete;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\AthletePerformanceService;
use Illuminate\Support\Facades\Log;

class AthleteController extends Controller
{
    protected $athleteService;
    public function __construct(AthletePerformanceService $athleteService)
    {
        $this->athleteService = $athleteService;
    }

    public function predictPerformance(Request $request)
    {
        try {
            $trainingHours = $request->input('training_hours');

            if (!is_numeric($trainingHours) || $trainingHours < 0) {
                Log::warning('Invalid training hours input: ', ['training_hours' => $trainingHours]);
                return response()->json(['error' => 'Invalid input. Training hours must be a positive number.'], 400);
            }

            // Get prediction
            $prediction = $this->athleteService->predict($trainingHours);

            Log::info('Prediction successful', [
                'training_hours' => $trainingHours,
                'predicted_performance' => $prediction[0] ?? null
            ]);

            return response()->json([
                'training_hours' => $trainingHours,
                'predicted_performance' => round($prediction, 2)
            ]);
        } catch (\Exception $e) {
            Log::error('Error in predicting performance', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json(['error' => 'Something went wrong. Please try again.'], 500);
        }
    }
}
