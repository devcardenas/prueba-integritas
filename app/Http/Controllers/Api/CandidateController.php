<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\StoreCandidateRequest;
use App\Models\Candidate;
use App\Http\Controllers\Controller;

class CandidateController extends Controller
{
    public function index()
    {
        return Candidate::all();
    }

    public function store(StoreCandidateRequest $request)
    {
        $candidate = Candidate::create($request->validated());
        return response()->json($candidate, 201);
    }

    // update method here
    public function update(StoreCandidateRequest $request, $id)
    {
        $candidate = Candidate::findOrFail($id);
        $candidate->update($request->validated());
        return response()->json($candidate);
    }

    public function destroy($id)
    {
        $candidate = Candidate::findOrFail($id);
        $candidate->delete();
        return response()->json(null, 204);
    }
}
