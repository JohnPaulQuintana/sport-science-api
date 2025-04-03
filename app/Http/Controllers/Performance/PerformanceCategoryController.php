<?php

namespace App\Http\Controllers\Performance;

use App\Http\Controllers\Controller;
use App\Models\AthletePerformance;
use Illuminate\Http\Request;
use App\Models\PerformanceCategory;
use App\Models\Sport;
use Illuminate\Support\Facades\Auth;

class PerformanceCategoryController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'sport_id' => 'required|exists:sports,id',
            'name' => 'required|unique:performance_categories,name,NULL,id,sport_id,' . $request->sport_id,
        ]);


        PerformanceCategory::create([
            'sport_id' => $request->sport_id,
            'name' => $request->name,
            'coach_id' => auth()->id(),
        ]);

        return response()->json(['message' => 'Category created successfully!']);
    }

    public function update(Request $request)
    {
        $category = PerformanceCategory::findOrFail($request->category_id);

        $request->validate([
            'name' => 'required|unique:performance_categories,name,' . $request->category_id . ',id',
        ]);

        $category->update([
            'name' => $request->name,
        ]);

        return response()->json(['message' => 'Category updated successfully!']);
    }

    public function sportCategories($sport_id)
    {
        $categories = Sport::where('id',$sport_id)->with('categories')->get();
        return response()->json([
            'status'=>'success',
            'categories' => $categories
        ]);
    }

    // insert category value
    public function sportCategoryValue(Request $request)
    {
        $request->validate([
            'athlete_id'=>'required',
            'category_id'=>'required',
            'category_score'=>'required',
        ]);

        $athlete_performance = AthletePerformance::create([
            'athlete_id' => $request->athlete_id,
            'category_id' => $request->category_id,
            'result' => $request->category_score,
            'recorded_at' => now(), // Stores the current timestamp
        ]);

        return response()->json([
            'status'=>'success',
            'success'=>$athlete_performance,
            'requested'=>$request->all()
        ]);
    }

    // insert category value
    public function sportCategoryValueEdit(Request $request)
    {
        $request->validate([
            'performance_id'=>'required',
            'result'=>'required',
        ]);

        $record = AthletePerformance::findOrFail($request->performance_id);
        if ($record)
        {
            $record->update([
                'result'=>$request->result
            ]);
        }
        return response()->json([
            'status'=>'success',
            'data'=>$record,
            'message'=>"You successfully updated the performance record!"
        ]);
    }

    // get the chartdata for coach
    public function chartData($id)
    {
        $chartData = PerformanceCategory::with(['performances.athlete'])
            ->where('sport_id',$id)
            ->where('coach_id',auth()->id())->get();

        return response()->json([
            'status'=>'success',
            'chart_data'=>$chartData
        ]);
    }
    // get the chartdata for athlete
    public function chartDataAthlete($id)
    {
        $chartData = PerformanceCategory::with(['performances.athlete'])
            ->where('sport_id',$id)->get();

        return response()->json([
            'status'=>'success',
            'chart_data'=>$chartData
        ]);
    }

}
