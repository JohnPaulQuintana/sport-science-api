<?php

namespace App\Http\Controllers\Performance;

use App\Http\Controllers\Controller;
use App\Models\AthletePerformance;
use App\Models\CategoryList;
use Illuminate\Http\Request;
use App\Models\PerformanceCategory;
use App\Models\Recommendation;
use App\Models\Sport;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
// use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class PerformanceCategoryController extends Controller
{
    public function store(Request $request)
    {
        try {
            $request->validate([
                'sport_id' => 'required|exists:sports,id',
                'name' => 'required|unique:performance_categories,name,NULL,id,coach_id,' . auth()->id(),
                'description' => 'required'
            ], [
                'sport_id.required' => 'The sport ID is required and must exist in the database.',
                'sport_id.exists' => 'The selected sport ID does not exist.',
                'name.required' => 'Please provide a category name.',
                'name.unique' => 'This category name already exists for the selected sport.',
                'description.required' => 'Please provide a description for the category.'
            ]);



            PerformanceCategory::create([
                'sport_id' => $request->sport_id,
                'name' => $request->name,
                'coach_id' => auth()->id(),
                'description' => $request->description
            ]);

            $categoryExists = CategoryList::where('category', $request->name)->first();
            if (!$categoryExists) {
                CategoryList::create(['category' => $request->name]);
            }

            return response()->json(['message' => 'Category created successfully!']);
        } catch (ValidationException $e) {
            // Custom error message for validation failure
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors(),  // Return the validation errors
            ], 422);  // 422 Unprocessable Entity
        }
    }

    public function update(Request $request)
    {
        $category = PerformanceCategory::findOrFail($request->category_id);

        //get caetegory record
        $categorylist = CategoryList::where('category', $category->name)->first();


        $request->validate([
            'name' => 'required|unique:performance_categories,name,' . $request->category_id . ',id',
            'description' => 'required'
        ]);

        if ($categorylist) {
            $categorylist->update([
                'category' => $request->name,
            ]);
        }
        $category->update([
            'name' => $request->name,
            'description' => $request->description,
        ]);



        return response()->json(['message' => 'Category updated successfully!']);
    }

    public function sportCategories($sport_id)
    {
        $categories = Sport::where('id', $sport_id)->with('categories')->get();
        return response()->json([
            'status' => 'success',
            'categories' => $categories
        ]);
    }

    // insert category value
    public function sportCategoryValue(Request $request)
    {
        $request->validate([
            'athlete_id' => 'required',
            'category_id' => 'required',
            'category_score' => 'required',
        ]);

        $athlete_performance = AthletePerformance::create([
            'athlete_id' => $request->athlete_id,
            'category_id' => $request->category_id,
            'result' => $request->category_score,
            'recorded_at' => now(), // Stores the current timestamp
        ]);

        return response()->json([
            'status' => 'success',
            'success' => $athlete_performance,
            'requested' => $request->all()
        ]);
    }

    // insert category value
    public function sportCategoryValueEdit(Request $request)
    {
        $request->validate([
            'performance_id' => 'required',
            'result' => 'required',
        ]);

        $record = AthletePerformance::findOrFail($request->performance_id);
        if ($record) {
            $record->update([
                'result' => $request->result
            ]);
        }
        return response()->json([
            'status' => 'success',
            'data' => $record,
            'message' => "You successfully updated the performance record!"
        ]);
    }

    // get the chartdata for coach
    public function chartData($id)
    {
        $chartData = PerformanceCategory::with(['performances.athlete'])
            ->where('sport_id', $id)
            ->where('coach_id', auth()->id())->get();

        return response()->json([
            'status' => 'success',
            'chart_data' => $chartData
        ]);
    }

    //ignore the athlete_id its for all athlete result
    public function analyzePerformance(Request $request, $id)
    {
        // Validate the request parameters
        $validated = $request->validate([
            'start_month' => 'nullable|date_format:Y-m|before_or_equal:now',
            'end_month' => 'nullable|date_format:Y-m|after_or_equal:start_month|before_or_equal:now',
            'athlete_id' => 'nullable|integer|exists:users,id'
        ]);

        // Set date ranges
        if (!empty($validated['start_month']) && !empty($validated['end_month'])) {
            // Use provided months
            $endMonth = Carbon::createFromFormat('Y-m', $validated['end_month'])->endOfMonth();
            $startMonth = Carbon::createFromFormat('Y-m', $validated['start_month'])->startOfMonth();

            // If same month was provided for both, compare with previous month
            if ($startMonth->format('Y-m') === $endMonth->format('Y-m')) {
                $startMonth = $startMonth->subMonth()->startOfMonth();
            }
        } else {
            // Default to current month vs last month
            $endMonth = Carbon::now()->endOfMonth();
            $startMonth = Carbon::now()->subMonth()->startOfMonth();
        }

        // Base query for raw performance data
        $rawDataQuery = DB::table('athlete_performances')
            ->join('users', 'athlete_performances.athlete_id', '=', 'users.id')
            ->join('performance_categories', 'athlete_performances.category_id', '=', 'performance_categories.id')
            ->join('sports', 'performance_categories.sport_id', '=', 'sports.id')
            ->whereBetween('athlete_performances.created_at', [$startMonth, $endMonth]);

        // Filter by athlete_id if provided
        if (!empty($validated['athlete_id'])) {
            $rawDataQuery->where('users.id', $validated['athlete_id']);
        }

        // Get all raw performance data for the time period
        $rawData = $rawDataQuery
            ->select([
                'users.name as athlete_name',
                'performance_categories.name as category_name',
                'athlete_performances.result',
                'athlete_performances.created_at'
            ])
            ->get()
            ->groupBy(['athlete_name', 'category_name', function ($item) use ($endMonth) {
                $recordDate = Carbon::parse($item->created_at);
                $comparisonMonth = $endMonth->copy()->startOfMonth();
                return $recordDate->greaterThanOrEqualTo($comparisonMonth) ? 'current' : 'previous';
            }]);

        // Base query for aggregated performance data
        $performanceQuery = DB::table('users')
            ->join('athlete_performances', 'users.id', '=', 'athlete_performances.athlete_id')
            ->join('performance_categories', 'athlete_performances.category_id', '=', 'performance_categories.id')
            ->join('sports', 'performance_categories.sport_id', '=', 'sports.id');

        // Filter by athlete_id if provided
        if (!empty($validated['athlete_id'])) {
            $performanceQuery->where('users.id', $validated['athlete_id']);
        }

        // Get aggregated performance data
        $performanceData = $performanceQuery
            ->select([
                'users.id as athlete_id',
                'users.name as athlete_name',
                'sports.name as sport_name',
                'performance_categories.name as category_name',
                DB::raw('SUM(CASE WHEN athlete_performances.created_at BETWEEN ? AND ? THEN athlete_performances.result ELSE 0 END) as current_period_score'),
                DB::raw('SUM(CASE WHEN athlete_performances.created_at BETWEEN ? AND ? THEN athlete_performances.result ELSE 0 END) as previous_period_score'),
                DB::raw('COUNT(CASE WHEN athlete_performances.created_at BETWEEN ? AND ? THEN 1 ELSE NULL END) as current_period_attempts'),
                DB::raw('COUNT(CASE WHEN athlete_performances.created_at BETWEEN ? AND ? THEN 1 ELSE NULL END) as previous_period_attempts')
            ])
            ->setBindings([
                $endMonth->copy()->startOfMonth(),
                $endMonth,
                $startMonth,
                $endMonth->copy()->startOfMonth()->subSecond(),
                $endMonth->copy()->startOfMonth(),
                $endMonth,
                $startMonth,
                $endMonth->copy()->startOfMonth()->subSecond()
            ])
            ->groupBy([
                'users.id',
                'users.name',
                'sports.id',
                'sports.name',
                'performance_categories.id',
                'performance_categories.name'
            ])
            ->orderBy('users.name')
            ->orderBy('sports.name')
            ->orderBy('performance_categories.name')
            ->get();

        // Process the data with raw scores included
        $groupedAnalysis = $performanceData->mapToGroups(function ($item) use ($rawData) {
            $currentPeriodAvg = $item->current_period_attempts > 0
                ? $item->current_period_score / $item->current_period_attempts
                : 0;

            $previousPeriodAvg = $item->previous_period_attempts > 0
                ? $item->previous_period_score / $item->previous_period_attempts
                : 0;

            // Calculate improvement with a reasonable cap
            $improvement = 0;
            if ($previousPeriodAvg > 0) {
                $rawImprovement = (($currentPeriodAvg - $previousPeriodAvg) / $previousPeriodAvg) * 100;
                $improvement = min(abs($rawImprovement), 200) * ($rawImprovement < 0 ? -1 : 1); // Cap at ±200%
            }

            // Fix: Correct variable names (athlete_name instead of athlete_name)
            $currentPeriodRaw = isset($rawData[$item->athlete_name][$item->category_name]['current'])
                ? $rawData[$item->athlete_name][$item->category_name]['current']->pluck('result')->toArray()
                : [];

            $previousPeriodRaw = isset($rawData[$item->athlete_name][$item->category_name]['previous'])
                ? $rawData[$item->athlete_name][$item->category_name]['previous']->pluck('result')->toArray()
                : [];

            return [
                $item->athlete_name => [
                    'athlete_id' => $item->athlete_id,
                    'sport' => $item->sport_name,
                    'category' => $item->category_name,
                    'current_period' => [
                        'total' => $item->current_period_score,  // Fix: Corrected from current_period_score
                        'attempts' => $item->current_period_attempts,
                        'average' => round($currentPeriodAvg, 2),
                        'raw_data' => $currentPeriodRaw
                    ],
                    'previous_period' => [
                        'total' => $item->previous_period_score,
                        'attempts' => $item->previous_period_attempts,
                        'average' => round($previousPeriodAvg, 2),
                        'raw_data' => $previousPeriodRaw
                    ],
                    'improvement_percentage' => round($improvement, 2),
                    'improvement_absolute' => round($currentPeriodAvg - $previousPeriodAvg, 2)
                ]
            ];
        })->map(function ($athleteData, $athleteName) {
            return [
                'athlete_id' => $athleteData->first()['athlete_id'],
                'recommendations' => Recommendation::where('user_id',$athleteData->first()['athlete_id'])->latest()->get(),
                'athlete' => $athleteName,
                'sport' => $athleteData->first()['sport'],
                'performance' => $athleteData->mapWithKeys(function ($item) {
                    return [
                        $item['category'] => [
                            'current_period' => $item['current_period'],
                            'previous_period' => $item['previous_period'],
                            'improvement_percentage' => $item['improvement_percentage'],
                            'improvement_absolute' => $item['improvement_absolute']
                        ]
                    ];
                })
            ];
        })->values();

        return response()->json([
            'status' => 'success',
            'periods' => [
                'current' => $endMonth->format('F Y'),
                'previous' => $startMonth->format('F Y')
            ],
            'analysis' => $groupedAnalysis
        ]);
    }

    public function getSingleAthletePerformance(Request $request)
    {
        // Validate the request parameters
        $validated = $request->validate([
            'start_month' => 'required|date_format:Y-m|before_or_equal:now',
            'end_month' => 'required|date_format:Y-m|after_or_equal:start_month|before_or_equal:now',
            'athlete_id' => 'required|integer|exists:users,id'
        ]);

        // Convert dates to Carbon instances
        $endMonth = Carbon::createFromFormat('Y-m', $validated['end_month'])->endOfMonth();
        $startMonth = Carbon::createFromFormat('Y-m', $validated['start_month'])->startOfMonth();

        // If same month was provided, compare with previous month
        if ($startMonth->format('Y-m') === $endMonth->format('Y-m')) {
            $startMonth = $startMonth->subMonth()->startOfMonth();
        }

        // Get raw performance data for the athlete and time period
        $rawData = DB::table('athlete_performances')
            ->join('users', 'athlete_performances.athlete_id', '=', 'users.id')
            ->join('performance_categories', 'athlete_performances.category_id', '=', 'performance_categories.id')
            ->join('sports', 'performance_categories.sport_id', '=', 'sports.id')
            ->whereBetween('athlete_performances.created_at', [$startMonth, $endMonth])
            ->where('users.id', $validated['athlete_id'])
            ->select([
                'users.name as athlete_name',
                'performance_categories.name as category_name',
                'athlete_performances.result',
                'athlete_performances.created_at'
            ])
            ->get()
            ->groupBy(['category_name', function ($item) use ($endMonth) {
                return Carbon::parse($item->created_at)
                    ->greaterThanOrEqualTo($endMonth->copy()->startOfMonth())
                    ? 'current'
                    : 'previous';
            }]);


        // Prepare the SQL query with explicit bindings
        $query = "
            SELECT
                users.id as athlete_id,
                users.name as athlete_name,
                sports.name as sport_name,
                performance_categories.name as category_name,
                SUM(CASE WHEN athlete_performances.created_at BETWEEN ? AND ? THEN athlete_performances.result ELSE 0 END) as current_period_score,
                SUM(CASE WHEN athlete_performances.created_at BETWEEN ? AND ? THEN athlete_performances.result ELSE 0 END) as previous_period_score,
                COUNT(CASE WHEN athlete_performances.created_at BETWEEN ? AND ? THEN 1 ELSE NULL END) as current_period_attempts,
                COUNT(CASE WHEN athlete_performances.created_at BETWEEN ? AND ? THEN 1 ELSE NULL END) as previous_period_attempts
            FROM users
            INNER JOIN athlete_performances ON users.id = athlete_performances.athlete_id
            INNER JOIN performance_categories ON athlete_performances.category_id = performance_categories.id
            INNER JOIN sports ON performance_categories.sport_id = sports.id
            WHERE users.id = ?
            GROUP BY
                users.id, users.name,
                sports.id, sports.name,
                performance_categories.id, performance_categories.name
            ORDER BY performance_categories.name ASC
        ";

        // Prepare all bindings in the correct order
        $bindings = [
            $endMonth->copy()->startOfMonth(),
            $endMonth,           // Current period scores
            $startMonth,
            $endMonth->copy()->startOfMonth()->subSecond(), // Previous period scores
            $endMonth->copy()->startOfMonth(),
            $endMonth,           // Current period attempts
            $startMonth,
            $endMonth->copy()->startOfMonth()->subSecond(),  // Previous period attempts
            $validated['athlete_id']                                // Athlete ID
        ];

        // Execute the query with bindings
        $performanceData = DB::select($query, $bindings);

        // Process the data
        $analysis = collect($performanceData)->map(function ($item) use ($rawData) {
            $currentPeriodAvg = $item->current_period_attempts > 0
                ? $item->current_period_score / $item->current_period_attempts
                : 0;

            $previousPeriodAvg = $item->previous_period_attempts > 0
                ? $item->previous_period_score / $item->previous_period_attempts
                : 0;

            // Calculate improvement with a reasonable cap
            $improvementPercentage = 0;
            if ($previousPeriodAvg > 0) {
                $rawImprovement = (($currentPeriodAvg - $previousPeriodAvg) / $previousPeriodAvg) * 100;
                $improvementPercentage = min(abs($rawImprovement), 200) * ($rawImprovement < 0 ? -1 : 1);
            }

            // Retrieve the athlete's name
            $athleteName = $item->athlete_name ?? 'Unknown Athlete'; // Set a default value if the name is not found

            return [
                'category' => $item->category_name,
                'current_period' => [
                    'total' => number_format((float) $item->current_period_score, 2, '.', ''),
                    'attempts' => (int) $item->current_period_attempts,
                    'average' => round($currentPeriodAvg, 2),
                    'raw_data' => $rawData[$item->category_name]['current']->pluck('result')->toArray() ?? []
                ],
                'previous_period' => [
                    'total' => number_format((float) $item->previous_period_score, 2, '.', ''),
                    'attempts' => (int) $item->previous_period_attempts,
                    'average' => round($previousPeriodAvg, 2),
                    'raw_data' => isset($rawData[$item->category_name]['previous'])
                        ? $rawData[$item->category_name]['previous']->pluck('result')->toArray()
                        : []  // Default to empty array if no 'previous' data
                ],
                'improvement_percentage' => round($improvementPercentage, 2),
                'improvement_absolute' => round($currentPeriodAvg - $previousPeriodAvg, 2),
                'athlete_name' => $athleteName,  // Ensure that the athlete's name is included
            ];
        });

        // Return the final response with the required structure
        $performanceAnalysis = $analysis->groupBy('athlete_name')->map(function ($athleteData) use ($validated, $performanceData) {
            $athlete = $athleteData->first();  // Assume the first record is enough for athlete data
            $performance = [];
            foreach ($athleteData as $data) {
                $performance[$data['category']] = [
                    'current_period' => $data['current_period'],
                    'previous_period' => $data['previous_period'],
                    'improvement_percentage' => $data['improvement_percentage'],
                    'improvement_absolute' => $data['improvement_absolute'],
                ];
            }

            return [
                'athlete_id' => $validated['athlete_id'],
                'athlete' => $athlete['athlete_name'],
                'sport' => $performanceData[0]->sport_name ?? null,
                'performance' => $performance
            ];
        })->values()->toArray();  // Ensure to get the results as an array

        return response()->json([
            'status' => 'success',
            'periods' => [
                'current' => $endMonth->format('F Y'),
                'previous' => $startMonth->format('F Y')
            ],
            'analysis' => $performanceAnalysis
        ]);
    }




    // get the chartdata for athlete
    public function chartDataAthlete($id)
    {
        $chartData = PerformanceCategory::with(['performances.athlete'])
            ->where('sport_id', $id)->get();

        return response()->json([
            'status' => 'success',
            'chart_data' => $chartData
        ]);
    }
}
