<!doctype html>
<html lang="fr">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Fiche de Présentation - SOAD</title>
    <style>
        :root {
            --primary-blue: #1a3973;
            --secondary-green: #4f744b;
            --light-green: #7fb069;
            --star-yellow: #fabe02;
            --text-white: #ffffff;
            --text-light: #b8c5d6;
            --light-white: rgba(255, 255, 255, 0.12);
        }
    </style>
</head>

<body style="
      margin: 0;
      padding: 0;
      font-family: Arial, sans-serif;
      background-color: var(--primary-blue);
      color: var(--text-white);
      width: 210mm;
      height: 297mm;
      box-sizing: border-box;
    ">
    <!-- Main container table -->
    <table style="width: 100%; height: 100%; border-collapse: collapse">
        <!-- Header section with logo and pharmacy image -->
        <tr style="height: 200px">
            <td style="position: relative; padding: 0">
                <!-- Logo positioned absolutely -->
                <div style="position: absolute; top: 20px; left: 100px; z-index: 10">
                    <img src="{{ $logo_path }}" alt="SOAD Logo" style="height: 40px; width: auto" />
                </div>

                <div style="position: absolute; top: 0px; left: 20px; z-index: 10">
                    <img src="{{ $green_shape_path }}" alt="Green shape" style="height: 140px; width: auto" />
                </div>
                <!-- Pharmacy image -->
                <div style="
              position: absolute;
              top: 80px;
              right: 0;
              left: 0;
              height: 180px;
              background: url(&quot;{{ $place_holder_path }}&quot;) center/cover
                no-repeat;
              opacity: 0.9;
            "></div>
            </td>
        </tr>

        <!-- Title section -->
        <tr style="height: 180px">
            <td style="padding: 150px 0 0 50px; background-color: var(--primary-blue)">
                <table style="width: 100%; border-collapse: collapse">
                    <tr>
                        <td style="vertical-align: top; width: 70%">
                            <h2 style="
                    margin: 0 0 10px 0;
                    font-size: 36px;
                    font-weight: normal;
                    color: var(--text-white);
                    line-height: 1.2;
                  ">
                                Fiche de
                            </h2>
                            <h1 style="
                    margin: 0 0 20px 0;
                    font-size: 64px;
                    font-weight: bold;
                    color: var(--text-white);
                    line-height: 1;
                  ">
                                Présentation
                            </h1>
                            <p style="
                    margin: 0;
                    font-size: 18px;
                    color: var(--text-light);
                    font-weight: normal;
                    letter-spacing: 3px;
                  ">
                                FORT D'ISSY - SEM010
                            </p>
                        </td>
                        <td style="
                  vertical-align: top;
                  width: 30%;
                  text-align: right;
                  padding-top: 50px;
                ">
                            <div style="display: inline-block">
                                <div style="
                      background-color: var(--secondary-green);
                      width: 30px;
                      height: 54px;
                      display: inline-block;
                      vertical-align: top;
                      margin-right: 4px;
                    "></div>
                                <div style="
                      background-color: var(--secondary-green);
                      padding: 15px 25px;
                      display: inline-block;
                      vertical-align: top;
                    ">
                                    <div style="
                        color: var(--star-yellow);
                        font-size: 20px;
                        letter-spacing: 2px;
                      ">
                                        ★★★★★
                                    </div>
                                </div>
                            </div>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>

        <!-- Main content section -->
        <tr style="height: auto">
            <td style="padding: 40px 0 0 50px; background-color: var(--primary-blue)">
                <table style="width: 100%; border-collapse: collapse; border-spacing: 0">
                    <tr>
                        <!-- Adresse du bien -->
                        <td style="width: 33.33%; vertical-align: top; padding-right: 20px">
                            <h3 style="
                    margin: 0 0 20px 0;
                    font-size: 18px;
                    font-weight: bold;
                    color: var(--text-white);
                  ">
                                Adresse du bien
                            </h3>
                            <div style="
                    border-bottom: 2px solid var(--light-white);
                    width: 50px;
                    margin-bottom: 20px;
                  "></div>
                            <p style="
                    margin: 0;
                    font-size: 16px;
                    color: var(--text-white);
                    line-height: 1.4;
                  ">
                                63 ESPLANADE DU BELVEDERE<br />
                                9213, ISSY LES MOULINEAUX
                            </p>
                        </td>

                        <!-- Surface utile -->
                        <td style="width: 33.33%; vertical-align: top; padding: 0 20px">
                            <h3 style="
                    margin: 0 0 20px 0;
                    font-size: 18px;
                    font-weight: bold;
                    color: var(--text-white);
                  ">
                                Surface utile
                            </h3>
                            <div style="
                    border-bottom: 2px solid var(--light-white);
                    width: 50px;
                    margin-bottom: 20px;
                  "></div>
                            <p style="
                    margin: 0;
                    font-size: 16px;
                    color: var(--text-white);
                    line-height: 1.4;
                  ">
                                109,5 M²
                            </p>
                        </td>

                        <!-- Type de local -->
                        <td style="width: 33.33%; vertical-align: top; padding-left: 20px">
                            <h3 style="
                    margin: 0 0 20px 0;
                    font-size: 18px;
                    font-weight: bold;
                    color: var(--text-white);
                  ">
                                Type de local
                            </h3>
                            <div style="
                    border-bottom: 2px solid var(--light-white);
                    width: 50px;
                    margin-bottom: 20px;
                  "></div>
                            <p style="
                    margin: 0;
                    font-size: 16px;
                    color: var(--text-white);
                    line-height: 1.4;
                  ">
                                LOCAL COMMERCIAL À<br />
                                USAGE DE PHARMACIE
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>

        <!-- Footer section -->
        <tr style="height: 80px">
            <td style="
            padding: 20px;
            background-color: var(--primary-blue);
            vertical-align: bottom;
          ">
                <div style="
              border-top: 1px solid var(--text-light);
              padding-top: 20px;
              text-align: center;
            ">
                    <p style="
                margin: 0;
                font-size: 14px;
                color: var(--text-light);
                letter-spacing: 1px;
              ">
                        52 PROMENADE DU VERGER 92130 ISSY-LES-MOULINEAUX
                    </p>
                </div>
            </td>
        </tr>
    </table>
</body>

</html>