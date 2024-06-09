<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use App\Models\Pet;
use App\Models\Report;

class PetController extends Controller
{
    //
    public function index() {
        $pets = Pet::with(['report'])->get();

        if($pets->isEmpty()) {
            return response()->json(["error" => "There is not pets"], 422);
        }

        return response()->json($pets, 200);
    }

    public function show($id) {
        $pet = Pet::with(['report'])->find($id);

        if(!$pet) {
            return response()->json(["error"=> "Pet not found"],404);
        }

        return response()->json($pet, 200);
    }

    public function store_report(Request $request, $id) {
        $validator = Validator::make($request->all(), [
             'report' => 'required|string'
        ]);

        if($validator->fails()) {
            return response()->json(["error" => $validator->errors()], 422);
        }

        $pet = Pet::findOrFail($id);

        $report = new Report(['report' => $request->input('report')]);
        $pet->report()->save($report);

        return response()->json(['message' => 'Report added successfully', 'report' => $report], 200);
    }

    public function store(Request $request) {
        $validator = Validator::make($request->all(), [ 
            "name"=> "required|string",
            "type" => "required|string",
            "breed" => "required|string",
            "gender" => "required|in:male,female",
            "age" => "sometimes|numeric",
            "weight" => "required|numeric",
            "img" => "required|url",
            "report" => "sometimes|string"
        ]);

        if($validator->fails()) {
            return response()->json(["error" => $validator->errors()], 422);
        }

        $pet = Pet::create($validator->validated());

        if($request->has('report')) {
            $report = new Report(['report' => $request->input('report')]);
            $pet->report()->save($report);
        }

        if (!$pet) {
            return response()->json(["error" => "Error in creating the pet"], 500);
        }
        $newReport = isset($report) ? $report : null;

        return response()->json(['message' => 'Pet created successfully', 'data' => $pet, 'report' => $newReport], 200);
    }

    public function update(Request $request, $id) {
        $validator = Validator::make($request->all(), [
            "name"=> "sometimes|string",
            "type" => "sometimes|string",
            "breed" => "sometimes|string",
            "gender" => "sometimes|in:male,female",
            "age" => "sometimes|numeric",
            "weight" => "sometimes|numeric",
            "img" => "sometimes|url",
            "report" => "sometimes|string"
        ]);

        if($validator->fails()) {
            return response()->json(["error" => $validator->errors()], 422);
        }

        $pet = Pet::findOrFail($id);
        $pet->update($validator->validated());

        if ($request->has('report')) {
            $report = new Report(['report' => $request->input('report')]);
            $pet->reports()->save($report);
        }
        $newReport = isset($report) ? $report : null;

        return response()->json(['message' => 'Pet updated successfully', 'data' => $pet, 'report' => $newReport], 200);
    }
}
