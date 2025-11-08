<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Bulletin de Salaire</title>
    <style>
        body { font-family: Arial, sans-serif; }

        /* Header */
        .header { text-align: center; margin-bottom: 30px; }

        /* Info employé */
        .info { margin: 20px 0; }
        .info h3 { text-align: center; margin-bottom: 20px; }
        .info p { margin: 4px 0; }

        /* Tableau des montants */
        table { width: 100%; border-collapse: collapse; margin-bottom: 30px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }

        /* Tableau signatures */
        .signature-table {
            width: 100%;
            margin-top: 50px;
            border-collapse: collapse;
        }
        .signature-table td {
            width: 50%;
            height: 120px; /* Grande hauteur pour laisser un espace pour signer */
            vertical-align: bottom;
            text-align: center;
            border: none;
            border-top: 1px solid #000; /* Ligne pour signature */
            padding-top: 5px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>BULLETIN DE SALAIRE</h1>
        <p>Référence: {{ $bulletin->reference_bulletin }}</p>
        <p>Date: {{ $bulletin->date_generation }}</p>
    </div>

    <div class="info">
        <h3>Informations Employé</h3>
        <p><strong>Nom:</strong> {{ $employe->nom_employe }}</p>
        <p><strong>Prénom:</strong> {{ $employe->prenom_employe }}</p>
        <p><strong>Matricule:</strong> {{ $employe->matricule_employe ?? 'N/A' }}</p>
        <p><strong>Mois:</strong> {{ $salaire->mois_salaire }}</p>
        <p><strong>Année:</strong> {{ $salaire->annee_salaire }}</p>
    </div>

    <table>
        <tr>
            <th>Désignation</th>
            <th>Montant</th>
        </tr>
        <tr>
            <td>Salaire de base</td>
            <td>{{ number_format($salaire->salaire_base ?? 0, 2) }} Ar</td>
        </tr>
        <tr>
            <td>Primes</td>
            <td>{{ number_format($salaire->primes_salaire ?? 0, 2) }} Ar</td>
        </tr>
        <tr>
            <td>Retenues</td>
            <td>{{ number_format($salaire->retenues_salaire ?? 0, 2) }} Ar</td>
        </tr>
         <tr>
            <td>CNaPS</td>
            <td>{{ number_format($salaire->cnaps ?? 0, 2) }} %</td>
        </tr>
         <tr>
            <td>OSTIE</td>
            <td>{{ number_format($salaire->medical ?? 0, 2) }} %</td>
        </tr>
        <tr>
            <td>IRSA</td>
            <td>{{ number_format($salaire->irsa ?? 0, 2) }} %</td>
        </tr>
        <tr>
            <th>Salaire Net</th>
            <th>{{ number_format($salaire->salaire_net ?? 0, 2) }} Ar</th>
        </tr>
    </table>

    <!-- Tableau pour signatures -->
    <table class="signature-table">
        <tr>
            <td>Signature du Responsable</td>
            <td>Signature Employé</td>
        </tr>
    </table>
</body>
</html>
