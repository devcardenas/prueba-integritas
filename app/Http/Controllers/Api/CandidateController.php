<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCandidateRequest;
use App\Models\Candidate;
use Illuminate\Support\Facades\Storage;

class CandidateController extends Controller
{
    public function index()
    {
        return Candidate::all();
    }

    public function store(StoreCandidateRequest $request)
    {
        try {
            $candidateData = $request->only('name', 'phone', 'profession');

            // Verificar si se subió el archivo y guardarlo
            if ($request->hasFile('cv')) {
                $filePath = $request->file('cv')->store('cvs', 'public');
                $candidateData['cv_path'] = $filePath;
            }

            $candidate = Candidate::create($candidateData);

            return response()->json($candidate, 201);
        } catch (\Throwable $th) {
            return response()->json(['error' => 'Error al guardar el candidato.'], 500);
        }
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

    public function downloadCV($id)
    {
        $candidate = Candidate::findOrFail($id);

        if ($candidate->cv_path && Storage::disk('public')->exists($candidate->cv_path)) {
            return response()->download(storage_path("app/public/{$candidate->cv_path}"));
        }

        return response()->json(['error' => 'Archivo no encontrado.'], 404);
    }
}
