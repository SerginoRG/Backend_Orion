<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Service;

class ServiceController extends Controller
{
    // Lister tous les services
    public function index()
    {
        return response()->json(Service::all(), 200);
    }

    // 🔹 Afficher un service spécifique
    public function show($id)
    {
        $service = Service::find($id);
        if (!$service) {
            return response()->json(['message' => 'Service non trouvé'], 404);
        }
        return response()->json($service, 200);
    }

    // Ajouter un service
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nom_service' => 'required|string|max:255|unique:services,nom_service',
            'description_service' => 'nullable|string',
        ]);
        $validated['nom_service'] = strtoupper($validated['nom_service']);
        $service = Service::create($validated);

        return response()->json([
            'message' => 'Service ajouté avec succès',
            'service' => $service,
        ], 201);
    }

    // Modifier un service
    public function update(Request $request, $id)
    {
        $service = Service::find($id);
        if (!$service) {
            return response()->json(['message' => 'Service non trouvé'], 404);
        }

        $validated = $request->validate([
            'nom_service' => 'required|string|max:255|unique:services,nom_service,' . $id . ',id_service',
            'description_service' => 'nullable|string',
        ]);

        $validated['nom_service'] = strtoupper($validated['nom_service']);
        $service->update($validated);

        return response()->json([
            'message' => 'Service mis à jour avec succès',
            'service' => $service,
        ], 200);
    }

    // Supprimer un service
    public function destroy($id)
    {
        $service = Service::find($id);
        if (!$service) {
            return response()->json(['message' => 'Service non trouvé'], 404);
        }

        $service->delete();
        return response()->json(['message' => 'Service supprimé avec succès'], 200);
    }
}
