<?php

namespace App\Services;
use Illuminate\Support\Facades\Log;
use Phpml\Regression\LeastSquares;

class AthletePerformanceService
{
    private $regression;

    public function __construct()
    {
        $this->regression = new LeastSquares();

        // Training dataset (Training Hours → Performance Score)
        $samples = [[1], [2], [3], [4], [5]]; // Training hours
        $targets = [50, 55, 60, 65, 70]; // Performance scores

        // Train the model
        $this->regression->train($samples, $targets);
    }

    public function predict($trainingHours)
    {
        try {
            $result = $this->regression->predict([$trainingHours]);
            \Log::info('Prediction successful', ['input' => $trainingHours, 'output' => $result]);

            return $result;
        } catch (\Exception $e) {
            \Log::error('Prediction error', [
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return null; // Prevent crashing the app
        }
    }

}
