<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Attestation d'Absence</title>
    <style>
        /* Styles de base pour l'ensemble du document */
        body {
            font-family: 'DejaVu Sans', 'Arial', sans-serif; /* Assurez-vous d'avoir une police de secours standard */
            font-size: 12pt; /* Taille de police légèrement plus grande pour la lecture */
            margin: 40px; /* Marges autour de la page pour une meilleure impression */
            color: #333; /* Couleur de texte sombre mais pas noir absolu */
            line-height: 1.6; /* Espacement des lignes pour la lisibilité */
        }

        /* En-tête / Titre du document */
        .title {
            text-align: center;
            font-size: 22pt; /* Titre plus grand et impactant */
            font-weight: bold;
            color: #1a428a; /* Une couleur professionnelle, comme un bleu foncé */
            text-transform: uppercase; /* Met le titre en majuscules pour l'impact */
            margin-bottom: 40px;
            padding-bottom: 10px;
            border-bottom: 2px solid #ddd; /* Ligne de séparation sous le titre */
        }

        /* Style pour la section des détails de l'attestation */
        .details-container {
            border: 1px solid #ccc; /* Encadré léger autour des informations */
            padding: 20px;
            margin: 30px 0;
            border-radius: 5px; /* Coins légèrement arrondis */
            background-color: #f9f9f9; /* Fond légèrement grisé pour le bloc */
        }

        /* Style pour chaque ligne d'information */
        .detail-line {
            margin-bottom: 10px;
            display: block; /* S'assure que l'élément prend toute la ligne */
            padding-left: 10px;
            border-left: 3px solid #1a428a; /* Barre de couleur à gauche pour structurer */
        }
        
        /* Style pour les libellés (Nom, Motif, etc.) */
        .detail-line strong {
            display: inline-block; /* Permet de fixer une largeur pour aligner les deux-points */
            width: 120px; /* Largeur fixe pour le libellé */
            color: #555; /* Couleur du libellé légèrement plus claire */
            font-weight: 700;
        }

        /* Texte introductif */
        .intro-text {
            margin-top: 30px;
            margin-bottom: 20px;
            font-size: 13pt;
        }

        /* Pied de page (date de création) */
        .footer-date {
            margin-top: 50px; /* Espace important avant le bas de page */
            text-align: right; /* Alignement à droite pour la date */
            font-style: italic;
            font-size: 11pt;
            color: #777;
        }
    </style>
</head>
<body>

    <p class="title">ATTESTATION D'ABSENCE</p>

    <p class="intro-text">Nous, soussignés, certifions que l'employé(e) dont les détails suivent a été en période d'absence :</p>

    <div class="details-container">
        <p class="detail-line"><strong>Nom :</strong> {{ $absence->employe->nom_employe }} {{ $absence->employe->prenom_employe }}</p>
        <p class="detail-line"><strong>Motif :</strong> {{ $absence->motif_absence }}</p>
        <p class="detail-line"><strong>Période :</strong> du {{ \Carbon\Carbon::parse($absence->date_debut)->format('d/m/Y') }} 
        au {{ \Carbon\Carbon::parse($absence->date_fin)->format('d/m/Y') }}</p>

        <p class="detail-line"><strong>Nombre de jours :</strong> {{ $jours }} jour(s)</p> <p class="detail-line"><strong>Statut :</strong> {{ $absence->statut_absence }}</p> </div>

    <p class="footer-date">Fait à Toliara, le {{ \Carbon\Carbon::now()->format('d/m/Y') }}</p>

</body>
</html>