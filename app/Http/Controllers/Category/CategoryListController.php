<?php

namespace App\Http\Controllers\Category;

use App\Http\Controllers\Controller;
use App\Models\CategoryList;
use Illuminate\Http\Request;

class CategoryListController extends Controller
{
    //insert category for recording
    public function insert(Request $request)
    {
        $request->validate(['category'=>'required|unique:category_lists,category']);

        $category = CategoryList::create(['category'=>$request->category]);

        return response()->json([
            'status'=>'succcess',
            'message'=>'successfully created a new category!'
        ]);
    }
}
