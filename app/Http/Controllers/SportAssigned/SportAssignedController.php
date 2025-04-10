<?php

namespace App\Http\Controllers\SportAssigned;

use App\Http\Controllers\Controller;
use App\Models\SportAssignment;
use Illuminate\Http\Request;

class SportAssignedController extends Controller
{
    //get sport
    public function sport_assigned($id)
    {
        $sport_assigned = SportAssignment::with('sport')->where('user_id',auth()->id())->get();

        return response()->json(['status'=>'success','sports'=>$sport_assigned]);
    }
}
