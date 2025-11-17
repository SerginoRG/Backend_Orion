<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Admin;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        $admin = Admin::where('email', $request->email)->first();

        if (!$admin) {
            return response()->json(['message' => 'Email introuvable'], 404);
        }

        // Vérifier si le mot de passe utilise le format PostgreSQL (commence par $2a$ ou $2b$)
        if (str_starts_with($admin->password, '$2a$') || str_starts_with($admin->password, '$2b$')) {
            // Vérification avec PostgreSQL directement
            $isValid = DB::selectOne(
                "SELECT (crypt(?, ?) = ?) as is_valid",
                [$request->password, $admin->password, $admin->password]
            )->is_valid;

            if ($isValid) {
                return response()->json([
                    'message' => 'Connexion réussie',
                    'admin' => $admin
                ], 200);
            } else {
                return response()->json(['message' => 'Mot de passe incorrect'], 401);
            }
        }

        // Vérification standard Laravel (pour les hash $2y$)
        if (Hash::check($request->password, $admin->password)) {
            return response()->json([
                'message' => 'Connexion réussie',
                'admin' => $admin
            ], 200);
        } else {
            return response()->json(['message' => 'Mot de passe incorrect'], 401);
        }
    }
}