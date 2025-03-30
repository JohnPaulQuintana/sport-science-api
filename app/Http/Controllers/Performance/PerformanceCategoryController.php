<?php

namespace App\Http\Controllers\Performance;

use App\Http\Controllers\Controller;
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

}
