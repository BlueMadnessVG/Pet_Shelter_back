<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use App\Models\Adoption;

class AdoptionController extends Controller
{
    public function index() {

        $userId = Auth::id();

        $adoptions = Adoption::where('userId', $userId)->with(["pet"])->get();

        if($adoptions->isEmpty()) {
            return response()->json(["error" => "There is no adoption yet"]. 422);
        }

        return response()->json($adoptions, 200);
    }

    public function show($id) {
        $userId = Auth::id();

        $adoption = Adoption::where('userId', $userId)->with("pet")->find($id);

        if(!$adoption) {
            return response()->json(["error" => "Adoption not fount"], 404);
        }

        return response()->json($adoption, 200);
    }
    //
    public function store(Request $request) {
        $userId = Auth::id();

        $validatorData = Validator::make($request->all(), [
            "petId"=> "required|exists:pets,id",
        ]);

        if($validatorData->fails()) {
            return response()->json(["error" => $validatorData->errors()], 422);
        }

        $adoption = Adoption::create([
            'userId' => $userId,
            'petId' => $request['petId']
        ]);

        return response()->json(['message' => 'Adoption created successfully', 'data' => $adoption], 200);
    }

    public function update(Request $request, $id) { 
        $userId = Auth::id();

        $validatorData = Validator::make($request->all(), [
            "petId"=> "required|exists:pets,id",
        ]);

        if($validatorData->fails()) {
            return response()->json(["error" => $validatorData->errors()], 422);
        }

        $adoption = Adoption::find($id);
        if(!$adoption) {
            return response()->json(["error" => "Adoption not fount"], 404);
        }
        $adoption->update($request->only(['petId']));

        return response()->json(['message' => 'Adoption updated successfully', 'data' => $adoption], 200);
    }
}
