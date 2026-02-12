<!doctype html>
<html lang="fr">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Situation - Descriptif Détaillé - SOAD</title>
    <style>
        :root {
            --primary-blue: #1e3d72;
            --secondary-green: #4a7c59;
            --light-green: #7fb069;
            --light-gray: #f0f2f5;
            --light-blue: rgba(26, 57, 115, 0.47);
            --text-dark: #1a3973;
            --text-light: rgba(26, 57, 115, 0.74);
            --text-green: #6f9a6a;
            --accent-yellow: #ffff01;
            --accent-purple: #dfc1f9;
        }
    </style>
</head>

<body style="
      margin: 0;
      padding: 0;
      font-family: Arial, sans-serif;
      background-color: var(--light-gray);
      color: var(--text-dark);
      width: 210mm;
      height: 297mm;
      box-sizing: border-box;
    ">
    <!-- Main container table -->
    <table style="width: 100%; height: 100%; border-collapse: collapse">
        <!-- Header section with logo and pharmacy image -->
        <tr style="height: 120px">
            <td style="
            position: relative;
            padding: 20px 50px 20px 50px;
            background-color: var(--light-gray);
          ">
                <!-- Logo positioned absolutely -->
                <div style="position: absolute; top: 20px; right: 50px; z-index: 10">
                    <img src="{{ $logo_page_one }}" alt="SOAD Logo" style="height: 50px; width: auto" />
                </div>

                <!-- Page identifier -->
                <div style="position: relative; z-index: 5; margin-top: 10px">
                    <p style="
                margin: 0;
                font-size: 14px;
                color: var(--text-light);
                font-weight: bold;
                letter-spacing: 1px;
              ">
                        01- FORT D'ISSY - SEM010
                    </p>
                </div>

                <div style="
              border-bottom: 3px solid var(--light-blue);
              width: 100%;
              margin-top: 10px;
            "></div>
            </td>
        </tr>

        <!-- Situation section -->
        <tr style="height: 200px">
            <td style="padding: 0 20px 20px 50px; background-color: var(--light-gray)">
                <h1 style="
              margin: 0 0 20px 0;
              font-size: 48px;
              font-weight: bold;
              color: var(--text-dark);
              line-height: 1;
            ">
                    Situation.
                </h1>
                <div style="
              border-bottom: 3px solid var(--light-blue);
              width: 60px;
              margin-bottom: 20px;
            "></div>
                <p style="
              margin: 0;
              font-size: 14px;
              color: var(--text-dark);
              line-height: 1.6;
              max-width: 70%;
            ">
                    Locaux commerciaux sis 63 Esplanade du Belvédère, 92130
                    Issy-les-Moulineaux.<br />
                    Cet emplacement de choix, est actuellement une pharmacie qui
                    bénéficie d'une<br />
                    situation privilégiée dans un environnement dynamique et en pleine
                    expansion.<br />
                    Idéalement situé, le local offre excellente visibilité et un flux
                    piétonnier régulier,<br />
                    garantissant un accès facile et une forte attractivité commerciale
                    pour toute<br />
                    activité.
                </p>
            </td>
        </tr>

        <!-- Descriptif Détaillé section -->
        <tr style="height: auto">
            <td style="padding: 0 50px 40px 50px; background-color: var(--light-gray)">
                <h1 style="
              margin: 0 0 20px 0;
              font-size: 48px;
              font-weight: bold;
              color: var(--text-dark);
              line-height: 1;
            ">
                    Descriptif Détaillé.
                </h1>
                <div style="
              border-bottom: 3px solid var(--light-blue);
              width: 60px;
              margin-bottom: 30px;
            "></div>

                <!-- Details grid using table -->
                <table style="width: 100%; border-collapse: collapse; border-spacing: 0">
                    <tr>
                        <!-- Left column -->
                        <td style="width: 50%; vertical-align: top; padding-right: 20px">
                            <!-- Chauffage -->
                            <div style="margin-bottom: 25px">
                                <h3 style="
                      margin: 0 0 10px 0;
                      font-size: 16px;
                      font-weight: bold;
                      color: var(--text-dark);
                    ">
                                    ✓ Chauffage:
                                </h3>
                                <p style="
                      margin: 0 0 8px 20px;
                      font-size: 14px;
                      color: var(--text-green);
                      font-weight: bold;
                    ">
                                    Géothermie
                                </p>
                                <div style="margin-left: 20px">
                                    <p style="
                        margin: 0 0 5px 0;
                        font-size: 12px;
                        color: var(--text-dark);
                      ">
                                        • Consommation énergétique
                                    </p>
                                    <div style="display: inline-block; margin-bottom: 8px">
                                        <span style="
                          background-color: var(--accent-yellow);
                          color: black;
                          padding: 2px 6px;
                          border-radius: 2px;
                          font-size: 12px;
                          font-weight: bold;
                          margin-right: 5px;
                        ">D</span>
                                        <span style="
                          background-color: #333;
                          color: white;
                          padding: 2px 6px;
                          border-radius: 2px;
                          font-size: 10px;
                          margin-right: 5px;
                        ">297</span>
                                        <span style="font-size: 10px; color: var(--text-light)">kWhep/m².an</span>
                                    </div>
                                    <p style="
                        margin: 0 0 5px 0;
                        font-size: 12px;
                        color: var(--text-dark);
                      ">
                                        • Emission de GES
                                    </p>
                                    <div style="display: inline-block">
                                        <span style="
                          background-color: var(--accent-purple);
                          color: white;
                          padding: 2px 6px;
                          border-radius: 2px;
                          font-size: 12px;
                          font-weight: bold;
                          margin-right: 5px;
                        ">B</span>
                                        <span style="
                          background-color: #333;
                          color: white;
                          padding: 2px 6px;
                          border-radius: 2px;
                          font-size: 10px;
                          margin-right: 5px;
                        ">6</span>
                                        <span style="font-size: 10px; color: var(--text-light)">kgCO2/m².an</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Climatisation -->
                            <div style="margin-bottom: 25px">
                                <h3 style="
                      margin: 0 0 8px 0;
                      font-size: 16px;
                      font-weight: bold;
                      color: var(--text-dark);
                    ">
                                    ✓ Climatisation:
                                </h3>
                                <p style="
                      margin: 0 0 15px 20px;
                      font-size: 14px;
                      color: var(--text-green);
                    ">
                                    oui / Réversible
                                </p>
                            </div>

                            <!-- Recouvrement de sol -->
                            <div style="margin-bottom: 25px">
                                <h3 style="
                      margin: 0 0 8px 0;
                      font-size: 16px;
                      font-weight: bold;
                      color: var(--text-dark);
                    ">
                                    ✓ Recouvrement de sol:
                                </h3>
                                <p style="
                      margin: 0 0 15px 20px;
                      font-size: 14px;
                      color: var(--text-green);
                    ">
                                    Dalle béton plus parquet stratifié
                                </p>
                            </div>

                            <!-- Murs et plafonds -->
                            <div style="margin-bottom: 25px">
                                <h3 style="
                      margin: 0 0 8px 0;
                      font-size: 16px;
                      font-weight: bold;
                      color: var(--text-dark);
                    ">
                                    ✓ Murs et plafonds :
                                </h3>
                                <p style="
                      margin: 0 0 15px 20px;
                      font-size: 14px;
                      color: var(--text-green);
                    ">
                                    Murs doublés (Plaque de placoplâtre) /<br />Dalles de faux
                                    plafond
                                </p>
                            </div>

                            <!-- Aménagements intérieurs -->
                            <div style="margin-bottom: 25px">
                                <h3 style="
                      margin: 0 0 8px 0;
                      font-size: 16px;
                      font-weight: bold;
                      color: var(--text-dark);
                    ">
                                    ✓ Aménagements intérieurs<br />&nbsp;&nbsp;&nbsp;spécifiques
                                    :
                                </h3>
                                <p style="
                      margin: 0 0 15px 20px;
                      font-size: 14px;
                      color: var(--text-green);
                    ">
                                    Rayonnages, espace de rangement, robot<br />de triage,
                                    rideau électrique
                                </p>
                            </div>
                        </td>

                        <!-- Right column -->
                        <td style="
                  width: 50%;
                  vertical-align: top;
                  padding-left: 20px;
                  position: relative;
                ">
                            <!-- Installation électrique -->
                            <div style="margin-bottom: 25px">
                                <h3 style="
                      margin: 0 0 8px 0;
                      font-size: 16px;
                      font-weight: bold;
                      color: var(--text-dark);
                    ">
                                    ✓ Installation électrique:
                                </h3>
                                <p style="
                      margin: 0 0 15px 20px;
                      font-size: 14px;
                      color: var(--text-green);
                    ">
                                    Bon Etat
                                </p>
                            </div>

                            <!-- Luminaires -->
                            <div style="margin-bottom: 25px">
                                <h3 style="
                      margin: 0 0 8px 0;
                      font-size: 16px;
                      font-weight: bold;
                      color: var(--text-dark);
                    ">
                                    ✓ Luminaires:
                                </h3>
                                <p style="
                      margin: 0 0 15px 20px;
                      font-size: 14px;
                      color: var(--text-green);
                    ">
                                    Type (spots, néons, dalles LED)
                                </p>
                            </div>

                            <!-- Prises électriques -->
                            <div style="margin-bottom: 25px">
                                <h3 style="
                      margin: 0 0 8px 0;
                      font-size: 16px;
                      font-weight: bold;
                      color: var(--text-dark);
                    ">
                                    ✓ Prises électriques :
                                </h3>
                                <p style="
                      margin: 0 0 15px 20px;
                      font-size: 14px;
                      color: var(--text-green);
                    ">
                                    A revoir
                                </p>
                            </div>

                            <!-- Tableau électrique -->
                            <div style="margin-bottom: 25px">
                                <h3 style="
                      margin: 0 0 8px 0;
                      font-size: 16px;
                      font-weight: bold;
                      color: var(--text-dark);
                    ">
                                    ✓ Tableau électrique:
                                </h3>
                                <p style="
                      margin: 0 0 15px 20px;
                      font-size: 14px;
                      color: var(--text-green);
                    ">
                                    Conforme
                                </p>
                            </div>

                            <!-- Linéaire vitrine -->
                            <div style="margin-bottom: 25px">
                                <h3 style="
                      margin: 0 0 8px 0;
                      font-size: 16px;
                      font-weight: bold;
                      color: var(--text-dark);
                    ">
                                    ✓ Linéaire vitrine
                                </h3>
                                <p style="
                      margin: 0 0 15px 20px;
                      font-size: 14px;
                      color: var(--text-green);
                    ">
                                    5 ml
                                </p>
                            </div>

                            <!-- Accessibilité PMR -->
                            <div style="margin-bottom: 25px">
                                <h3 style="
                      margin: 0 0 8px 0;
                      font-size: 16px;
                      font-weight: bold;
                      color: var(--text-dark);
                    ">
                                    ✓ Accessibilité PMR
                                </h3>
                                <p style="
                      margin: 0 0 15px 20px;
                      font-size: 14px;
                      color: var(--text-green);
                    ">
                                    Oui
                                </p>
                            </div>

                            <div style="
                    position: absolute;
                    right: 0;
                    bottom: 0;
                    width: 50px;
                    height: 40px;
                    background-color: #4a7c59;
                  "></div>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>

</html>