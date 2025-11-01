<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Employe;
use Illuminate\Support\Facades\Storage;

class EmployeController extends Controller
{
    //  Lister les employés d’un service
    public function index($service_id)
    {
        $employes = Employe::where('service_id', $service_id)->get();
        return response()->json($employes, 200);
    }

    public function all()
        {
            $employes = Employe::all(); // récupère tous les employés
            return response()->json($employes, 200);
        }


    //  Ajouter un employé avec photo
    public function store(Request $request)
    {
        $validated = $request->validate([
            'matricule_employe' => 'required|string|unique:employes,matricule_employe',
            'nom_employe' => 'required|string|max:255',
            'prenom_employe' => 'required|string|max:255',
            'date_naissance_employe' => 'required|date',
            'cin_employe' => 'required|string|max:12|unique:employes,cin_employe',
            'adresse_employe' => 'required|string',
            'email_employe' => 'required|email|unique:employes,email_employe',
            'telephone_employe' => 'required|string|max:20',
            'date_embauche_employe' => 'required|date',
            'poste_employe' => 'required|string|max:255',
            'salaire_base_employe' => 'required|numeric',
            'service_id' => 'required|exists:services,id_service',
            'photo_profil_employe' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

            // Transformer nom et prénom
            $validated['nom_employe'] = strtoupper($validated['nom_employe']);
            $validated['prenom_employe'] = ucfirst(strtolower($validated['prenom_employe']));
            $validated['poste_employe'] = ucfirst(strtolower($validated['poste_employe']));

        // Gestion de l’image
        if ($request->hasFile('photo_profil_employe')) {
            $path = $request->file('photo_profil_employe')->store('employes', 'public');
            $validated['photo_profil_employe'] = $path;
        }

        $employe = Employe::create($validated);

        return response()->json([
            'message' => 'Employé ajouté avec succès',
            'employe' => $employe
        ], 201);
    }

    // 🔹 Afficher un employé
    public function show($id)
    {
        $employe = Employe::find($id);
        if (!$employe) {
            return response()->json(['message' => 'Employé non trouvé'], 404);
        }
        return response()->json($employe, 200);
    }

    // 🔹 Modifier un employé
    public function update(Request $request, $id)
    {
        $employe = Employe::find($id);
        if (!$employe) {
            return response()->json(['message' => 'Employé non trouvé'], 404);
        }

        $validated = $request->validate([
            'matricule_employe' => 'required|string|unique:employes,matricule_employe,' . $id . ',id_employe',
            'nom_employe' => 'required|string|max:255',
            'prenom_employe' => 'required|string|max:255',
            'date_naissance_employe' => 'required|date',
            'cin_employe' => 'required|string|max:12|unique:employes,cin_employe,' . $id . ',id_employe',
            'adresse_employe' => 'required|string',
            'email_employe' => 'required|email|unique:employes,email_employe,' . $id . ',id_employe',
            'telephone_employe' => 'required|string|max:20',
            'date_embauche_employe' => 'required|date',
            'poste_employe' => 'required|string|max:255',
            'salaire_base_employe' => 'required|numeric',
            'service_id' => 'required|exists:services,id_service',
            'photo_profil_employe' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $validated['nom_employe'] = strtoupper($validated['nom_employe']);
        $validated['prenom_employe'] = ucfirst(strtolower($validated['prenom_employe']));
        $validated['poste_employe'] = ucfirst(strtolower($validated['poste_employe']));


        // Gestion de la photo (mise à jour)
        if ($request->hasFile('photo_profil_employe')) {
            if ($employe->photo_profil_employe) {
                Storage::disk('public')->delete($employe->photo_profil_employe);
            }
            $path = $request->file('photo_profil_employe')->store('employes', 'public');
            $validated['photo_profil_employe'] = $path;
        }

        $employe->update($validated);

        return response()->json([
            'message' => 'Employé mis à jour avec succès',
            'employe' => $employe
        ], 200);
    }

    // 🔹 Supprimer un employé
    public function destroy($id)
    {
        $employe = Employe::find($id);
        if (!$employe) {
            return response()->json(['message' => 'Employé non trouvé'], 404);
        }

        // Supprimer la photo si existe
        if ($employe->photo_profil_employe) {
            Storage::disk('public')->delete($employe->photo_profil_employe);
        }

        $employe->delete();
        return response()->json(['message' => 'Employé supprimé avec succès'], 200);
    }
}
