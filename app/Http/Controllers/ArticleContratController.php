<?php

namespace App\Http\Controllers;

use App\Models\ArticleContrat;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ArticleContratController extends Controller
{
    /**
     * Afficher tous les articles
     */
    public function index(): JsonResponse
    {
        $articles = ArticleContrat::all();
        return response()->json($articles);
    }

    /**
     * Créer un nouvel article
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'article' => 'required|string|max:255',
            'titre' => 'required|string|max:255',
            'contenu' => 'required|string',
        ]);

        $article = ArticleContrat::create($validated);

        return response()->json([
            'message' => 'Article créé avec succès',
            'data' => $article
        ], 201);
    }

    /**
     * Afficher un article spécifique
     */
    public function show($id): JsonResponse
    {
        $article = ArticleContrat::find($id);

        if (!$article) {
            return response()->json([
                'message' => 'Article non trouvé'
            ], 404);
        }

        return response()->json($article);
    }

    /**
     * Mettre à jour un article
     */
    public function update(Request $request, $id): JsonResponse
    {
        $article = ArticleContrat::find($id);

        if (!$article) {
            return response()->json([
                'message' => 'Article non trouvé'
            ], 404);
        }

        $validated = $request->validate([
            'article' => 'required|string|max:255',
            'titre' => 'required|string|max:255',
            'contenu' => 'required|string',
        ]);

        $article->update($validated);

        return response()->json([
            'message' => 'Article modifié avec succès',
            'data' => $article
        ]);
    }

    /**
     * Supprimer un article
     */
    public function destroy($id): JsonResponse
    {
        $article = ArticleContrat::find($id);

        if (!$article) {
            return response()->json([
                'message' => 'Article non trouvé'
            ], 404);
        }

        $article->delete();

        return response()->json([
            'message' => 'Article supprimé avec succès'
        ]);
    }
}