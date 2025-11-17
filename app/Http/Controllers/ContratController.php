<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Contrat;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\ArticleContrat;

class ContratController extends Controller
{
    public function index()
    {
        return Contrat::with('employe')->get();
    }

    public function store(Request $request)
    {
        $contrat = Contrat::create($request->all());
        return Contrat::with('employe')->find($contrat->id_contrat);
    }

    public function show($id)
    {
        return Contrat::with('employe')->find($id);
    }

    public function update(Request $request, $id)
    {
        $contrat = Contrat::find($id);
        $contrat->update($request->all());
        return Contrat::with('employe')->find($id);
    }

    public function destroy($id)
    {
        $contrat = Contrat::find($id);
        $contrat->delete();
        return response()->json(['message' => 'Contrat supprimé']);
    }
    
    public function getByEmploye($employe_id)
    {
        $contrat = Contrat::where('employe_id', $employe_id)->first();
        if (!$contrat) {
            return response()->json(null, 200); // Aucun contrat
        }
        return response()->json($contrat, 200);
    }

    public function generatePDF($id)
    {
        $contrat = Contrat::with('employe')->findOrFail($id);

        // Charger tous les articles du contrat
        $articles = ArticleContrat::all();

        $pdf = Pdf::loadView('pdf.contrat', [
            'contrat' => $contrat,
            'articles' => $articles
        ]);

        return $pdf->download('contrat_'.$contrat->id_contrat.'.pdf');
    }

}
