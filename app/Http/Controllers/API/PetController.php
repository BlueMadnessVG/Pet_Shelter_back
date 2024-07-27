<?php

namespace App\Http\Controllers\API;

use App\Models\Breed;
use Illuminate\Http\Request;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use App\Models\Pet;
use App\Models\Report;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class PetController extends Controller
{
    //
    public function index() {
        $pets = Pet::whereDoesntHave('adoptions')
                    ->orWhereHas('adoptions', function ($query) {
                        $query->where('status', 'rejected');
                    })
                    ->with('report') // Eager load the 'report' relationship if needed
                    ->get();

        if($pets->isEmpty()) {
            return response()->json(["error" => "There is not pets"], 422);
        }

        $petData = [];

        foreach ($pets as $pet) {
            $petArray = $pet->toArray();
            $petArray['img'] = Storage::url($pet->img);
            $petData[] = $petArray;
        };

        return response()->json($pets, 200);
    }

    public function list(Request $request) {
        $pageToken = $request->input('page', 1);
        $maxResults = $request->input('maxResults', 10);

        $pets = Pet::select('id', 'name', 'img')->
                    whereDoesntHave('adoptions')
                    ->orWhereHas('adoptions', function ($query) {
                        $query->where('status', 'rejected');
                    })->paginate($maxResults, ['*'], 'page', $pageToken);

        $petData = [];

        foreach ($pets as $pet) {
            $petArray = $pet->toArray();
            $petArray['img'] = Storage::url($pet->img);
            $petData[] = $petArray;
        };

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
            "breed_id" => "required|exists:breeds,id",
            "gender" => "required|in:male,female",
            "age" => "sometimes|numeric",
            "weight" => "required|numeric",
            "img" => "required|image|mimes:jpeg,png,jpg,gif|max:2048",
            "report" => "sometimes|string"
        ]);

        if($validator->fails()) {
            return response()->json(["error" => $validator->errors()], 422);
        }

        $petData = $validator->validate();

        $image = $request->file('img');
        $filename = time() . '.' . $image->getClientOriginalExtension();
        $imagePath = $image->storeAs('pets', $filename, 'public');
        $imageUrl = asset('storage/' . $imagePath);
        $imageUrl = Storage::url($imagePath);
        $petData['img'] = $imageUrl;

        $pet = Pet::create($petData);

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
            "breed_id" => "sometimes|exist:breeds,id",
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

    public function show_breeds(Request $request) {
        $type = $request->input('type', 'dog');

        $breed = Breed::where('type', $type)->select('id', 'breed')->get();

        if($breed->isEmpty()) {
            return response()->json(["error" => "There is no breeds yet"], 422);
        }

        return response()->json($breed, 200);
    }

    public function store_breed(Request $request) {
        $validator = Validator::make($request -> all(), [
            "type" => "required|in:dog,cat",
            "breed" => "required|string"
        ]);

        if($validator->fails()) {
            return response()->json(["error" => $validator->errors()], 422);
        }

        $breed = Breed::create( $validator->validated());

        return response()->json(['message' => 'Breed added successfully', 'data' => $breed], 200);
    }
}
