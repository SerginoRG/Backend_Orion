<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Contrat</title>
    <style>
        /* BASE & TYPOGRAPHIE */
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 11pt;
            line-height: 1.6;
            color: #333;
            margin: 0 auto;
            padding: 40px;
            max-width: 800px;
        }

        /* EN-TÊTES */
        h2 {
            text-align: center;
            font-size: 24pt;
            color: #2c3e50;
            border-bottom: 2px solid #3498db;
            padding-bottom: 10px;
            margin-bottom: 30px;
        }

        h3 {
            font-size: 16pt;
            color: #34495e;
            margin-top: 40px;
            margin-bottom: 15px;
        }

        /* SECTION D'INFORMATION (DÉTAILS DU CONTRAT) */
        .info-section p {
            margin-bottom: 8px;
        }
        .info-section strong {
            display: inline-block;
            width: 120px; /* Aligner les doubles points */
            font-weight: bold;
        }

        /* TABLEAU (SI UTILISÉ) */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            font-size: 10pt;
        }
        th {
            background-color: #3498db;
            color: white;
            padding: 12px 8px;
            text-align: left;
            border: 1px solid #2980b9;
        }
        td {
            padding: 8px;
            border: 1px solid #ddd;
        }
        /* Effet zébré */
        tbody tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        /* STYLE DES ARTICLES DÉTAILLÉS (VOTRE REQUÊTE) */
        .article-section {
            margin-top: 25px;
            page-break-inside: avoid; /* Important pour l'impression/PDF */
        }
        .article-title {
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 5px;
        }
        .article-content {
            margin-left: 20px; /* Indentation pour le corps de l'article */
            text-align: justify; /* Justifier le texte */
        }
    </style>
</head>
<body>

    <h2>Contrat de Travail</h2>

    <div class="info-section">
        <p><strong>Employé :</strong> {{ $contrat->employe->nom_employe }} {{ $contrat->employe->prenom_employe }}</p>
        <p><strong>Type :</strong> {{ $contrat->type_contrat }}</p>
        <p><strong>Date début :</strong> {{ $contrat->date_debut_contrat }}</p>
        <p><strong>Date fin :</strong> {{ $contrat->date_fin_contrat }}</p>
        <p><strong>Statut :</strong> {{ $contrat->statut_contrat }}</p>
    </div>

    <h3>Articles du Contrat</h3>

    @foreach ($articles as $a)
    <div class="article-section">
        <div class="article-title">
             {{ $a->article }} : {{ $a->titre }}
        </div>
        <div class="article-content">
            {{ $a->contenu }}
        </div>
    </div>
    @endforeach

    </body>
</html>