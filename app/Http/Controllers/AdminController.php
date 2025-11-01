<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Admin;

class AdminController extends Controller
{
    public function login(Request $request)
    {
        // Validation simple
        $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        // Recherche de l'admin dans la base
        $admin = Admin::where('email', $request->email)->first();

        if (!$admin) {
            return response()->json(['message' => 'Email introuvable'], 404);
        }

        // Comparaison du mot de passe en clair
        if ($admin->password === $request->password) {
            return response()->json([
                'message' => 'Connexion réussie',
                'admin' => $admin
            ], 200);
        } else {
            return response()->json(['message' => 'Mot de passe incorrect'], 401);
        }
    }
}
