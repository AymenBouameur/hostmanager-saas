<!doctype html>
<html lang="fr">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Galerie - SOAD</title>
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
        <tr style="height: 80px">
            <td style="
            position: relative;
            padding: 20px 50px 20px 50px;
            background-color: var(--light-gray);
          ">
                <!-- Logo positioned absolutely -->
                <div style="position: absolute; top: 20px; right: 50px; z-index: 10">
                    <img src="{{ $logo_page_two }}" alt="SOAD Logo" style="height: 50px; width: auto" />
                </div>
                <!-- Page identifier -->
                <div style="position: relative; z-index: 5; margin-top: 40px">
                    <p style="
                margin: 0;
                font-size: 14px;
                color: var(--text-light);
                font-weight: bold;
                letter-spacing: 1px;
              ">
                        02- FORT D'ISSY - SEM010
                    </p>
                </div>
                <div style="
              border-bottom: 3px solid var(--light-blue);
              width: 100%;
              margin-top: 10px;
            "></div>
            </td>
        </tr>

        <!-- Title section -->
        <tr style="height: 150px">
            <td style="padding: 0 20px 20px 50px; background-color: var(--light-gray)">
                <h1 style="
              margin: 0 0 20px 0;
              font-size: 48px;
              font-weight: bold;
              color: var(--text-dark);
              line-height: 1;
            ">
                    Galerie
                </h1>
                <div style="
              border-bottom: 3px solid var(--light-blue);
              width: 60px;
              margin-bottom: 20px;
            "></div>
            </td>
        </tr>

        <!-- Gallery content section -->
        <tr style="height: calc(100% - 200px)">
            <td style="padding: 0 50px 50px 50px; vertical-align: top">
                <!-- Gallery images table -->
                <table style="width: 100%; height: 100%; border-collapse: collapse">
                    <!-- First row of images -->
                    <tr style="height: 50%">
                        <!-- Extérieur image -->
                        <td style="width: 50%; padding: 10px; vertical-align: top">
                            <table style="width: 100%; height: 100%; border-collapse: collapse">
                                <tr style="height: 85%">
                                    <td style="
                        text-align: center;
                        vertical-align: middle;
                        background-color: white;
                        border-radius: 8px;
                        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
                      ">
                                        <img src="{{ $image1 }}" alt="Extérieur" style="
                          max-width: 90%;
                          max-height: 90%;
                          width: auto;
                          height: auto;
                          border-radius: 4px;
                        " />
                                    </td>
                                </tr>
                                <tr style="height: 15%">
                                    <td style="
                        text-align: center;
                        vertical-align: middle;
                        padding-top: 10px;
                      ">
                                        <h3 style="
                          margin: 0;
                          font-size: 18px;
                          font-weight: bold;
                          color: var(--text-dark);
                        ">
                                            Extérieur
                                        </h3>
                                    </td>
                                </tr>
                            </table>
                        </td>

                        <!-- Vue générale image -->
                        <td style="width: 50%; padding: 10px; vertical-align: top">
                            <table style="width: 100%; height: 100%; border-collapse: collapse">
                                <tr style="height: 85%">
                                    <td style="
                        text-align: center;
                        vertical-align: middle;
                        background-color: white;
                        border-radius: 8px;
                        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
                      ">
                                        <img src="{{ $image2 }}" alt="Vue générale" style="
                          max-width: 90%;
                          max-height: 90%;
                          width: auto;
                          height: auto;
                          border-radius: 4px;
                        " />
                                    </td>
                                </tr>
                                <tr style="height: 15%">
                                    <td style="
                        text-align: center;
                        vertical-align: middle;
                        padding-top: 10px;
                      ">
                                        <h3 style="
                          margin: 0;
                          font-size: 18px;
                          font-weight: bold;
                          color: var(--text-dark);
                        ">
                                            Vue générale
                                        </h3>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- Second row of images -->
                    <tr style="height: 50%">
                        <!-- Chauffage/Refroidissement/Climatisation image -->
                        <td style="width: 50%; padding: 10px; vertical-align: top">
                            <table style="width: 100%; height: 100%; border-collapse: collapse">
                                <tr style="height: 85%">
                                    <td style="
                        text-align: center;
                        vertical-align: middle;
                        background-color: white;
                        border-radius: 8px;
                        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
                      ">
                                        <img src="{{ $image3 }}" alt="Chauffage/Refroidissement/Climatisation" style="
                          max-width: 90%;
                          max-height: 90%;
                          width: auto;
                          height: auto;
                          border-radius: 4px;
                        " />
                                    </td>
                                </tr>
                                <tr style="height: 15%">
                                    <td style="
                        text-align: center;
                        vertical-align: middle;
                        padding-top: 10px;
                      ">
                                        <h3 style="
                          margin: 0;
                          font-size: 16px;
                          font-weight: bold;
                          color: var(--text-dark);
                          line-height: 1.2;
                        ">
                                            Chauffage /<br />
                                            Refroidissement /<br />
                                            Climatisation
                                        </h3>
                                    </td>
                                </tr>
                            </table>
                        </td>

                        <!-- Eclairement image -->
                        <td style="width: 50%; padding: 10px; vertical-align: top">
                            <table style="width: 100%; height: 100%; border-collapse: collapse">
                                <tr style="height: 85%">
                                    <td style="
                        text-align: center;
                        vertical-align: middle;
                        background-color: white;
                        border-radius: 8px;
                        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
                      ">
                                        <img src="{{ $image4 }}" alt="Eclairement" style="
                          max-width: 90%;
                          max-height: 90%;
                          width: auto;
                          height: auto;
                          border-radius: 4px;
                        " />
                                    </td>
                                </tr>
                                <tr style="height: 15%">
                                    <td style="
                        text-align: center;
                        vertical-align: middle;
                        padding-top: 10px;
                      ">
                                        <h3 style="
                          margin: 0;
                          font-size: 18px;
                          font-weight: bold;
                          color: var(--text-dark);
                        ">
                                            Eclairement
                                        </h3>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
        <tr style="position: relative">
            <td>
                <div style="
              position: absolute;
              left: 0px;
              bottom: 0;
              width: 50px;
              height: 40px;
              background-color: #4a7c59;
            "></div>

                <div style="
              position: absolute;
              left: 60px;
              bottom: 0;
              width: 100px;
              height: 40px;
              background-color: #4a7c59;
            "></div>
            </td>
        </tr>
    </table>
</body>

</html>