<?php
namespace Majormedia\Adaptexcel\Http;

use Backend\Classes\Controller;
use League\Csv\Reader;
use League\Csv\Statement;
use League\Csv\Writer;

use GuzzleHttp\Client;

/**
 * Adapt Excel Back-end Controller
 */
class AdaptExcel extends Controller
{
    public $implement = [
        'MajorMedia.ToolBox.Behaviors.RestController'
    ];

    public $restConfig = 'config_rest.yaml';



    public function csvReader($file_path)
    {

        $csvFilePath = base_path($file_path);
        $csv = Reader::createFromPath($csvFilePath, 'r');
        $records = $csv->getRecords();
        $records = iterator_to_array($records);
        return $records;
    }

    public function csvWriter($file_path, $record)
    {

        $csvFilePath = base_path($file_path);
        $csvWriter = Writer::createFromPath($csvFilePath, 'a+');
        $csvWriter->insertOne($record);
    }

    // TO CALL ON GET METHOD
    public function method()
    {

        // Read data from excel A & B.
        $records_matrice = $this->csvReader('plugins/majormedia/adaptexcel/files/Copie de Liste Dahua.csv');
        $records_products = $this->csvReader('plugins/majormedia/adaptexcel/files/products.csv');

        // Get the headers
        $records_products_header = $records_products[0];
        $records_matrice_header = $records_matrice[1];

        // Ask the use to match headers
        $this->mapCsv($records_matrice_header, $records_products_header);
    }

    // TO CALL ON POST METHOD
    public function matchHeader()
    {

        $records_matrice = $this->csvReader('plugins/majormedia/adaptexcel/files/Copie de Liste Dahua.csv');
        $maps = $_POST;
        // Check if data is posted
        if (empty($maps)) {
            echo "No mappings provided.";
            return;
        }

        print_r($maps);

        foreach ($records_matrice as $key => $value) {

            // Define an array with a defined size 68 (number of B's columns).
            $new_records = array_fill(0, 68, '');

            // skip the header
            if ($key == 0) {
                continue;
            }

            foreach ($maps as $records_matrice_index => $products_index) {
                $new_records[$products_index] = $value[$records_matrice_index];
            }

            // Write in the new line in B
            $this->csvWriter('plugins/majormedia/adaptexcel/files/products.csv', $new_records);
            echo "Line $key written successuflly : [<span style='color:red'>" . implode(",", $new_records) . "</span>] !<br>";
        }

    }

    // SELECT FORM
    public function mapCsv($columns_1, $columns_2)
    {
        echo "<style> form { margin: 20px; } label { font-weight: bold; display: block; margin-top: 10px; } select { margin-bottom: 10px; padding: 5px; border-radius: 5px; border: 1px solid #ccc; } option { padding: 5px;}</style>";
        $options = "<option value=''>Nothing</option>";
        foreach ($columns_2 as $key => $value) {
            $options = $options . "<option value='$key'>$value</option><br>";
        }
        echo "<form action='adaptexcel' method='POST'>";
        foreach ($columns_1 as $key => $value) {
            echo "<label>$value</label>";
            echo "<select name='$key'>";
            echo $options;
            echo "</select><br>";
        }
        echo "<input type='submit' value='Submit'/>";
        echo "</form>";
    }

    // EXAMPLE OF HOW THE ABOVE ALGORITHM WORKS
    public function get()
    {
        //$map = $_POST;
        $array_1 = ["BOB", "ALICE", "JHON"];
        $array_2 = ["Orange:Jhon", "Apple:Bob", "Banana:Alice"];
        print_r($array_1);
        echo "<br>";
        print_r($array_2);
        echo "<br>";
        $this->mapCsv($array_1, $array_2);
    }

    public function post()
    {
        print_r($_POST);
        $record = array_fill(0, 3, '');
        $array_1 = ["BOB", "ALICE", "JHON"];
        $array_2 = ["Orange:Jhon", "Apple:Bob", "Banana:Alice"];
        foreach ($_POST as $key => $value) {
            echo "$array_1[$key] : $array_2[$value] <br>";
        }
    }

    public function testAccessTokenApi()
    {
        $client = new \GuzzleHttp\Client();

        try {
            // Send the request
            $response = $client->request('POST', 'https://qaext-auth.eviivo.com/api/connect/token', [
                'form_params' => [
                    'client_id' => 'bd855c94-135a-49b7-96b0-2a3887501fec',
                    'client_secret' => 'pccwLpD6UovEIof0xyiF',
                    'grant_type' => 'client_credentials'
                ],
                'headers' => [
                    'accept' => 'application/json',
                    'content-type' => 'application/x-www-form-urlencoded',
                ],
            ]);

            // Get the response body content and decode it to an array
            $responseData = json_decode($response->getBody()->getContents(), true);

            // Dump the response data for debugging

            // Return the response as JSON
            return response()->json($responseData);
        } catch (\Exception $e) {
            // Handle any potential errors
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }


    public function getAvailableProperties()
    {
        // Initialize the Guzzle HTTP client
        $client = new Client();

        // Set the Bearer Token
        $bearerToken = '/8bcKsAM2NHXer4dXaoeplFbQsC5S1Bht5gap5BppZp5u0PkJywDyomWhgu8UdhFmpMIU5YC33ATjyDfXHK9YeGG1BjaSDdabfjNEdwda14lVQdVbn3dppYg2iS721maSny1B3FK1b8jYESP7qSjtotiHQaiQl47jrIHuDSsYCIIEtmOhDxb7P8Mpa0yuMn1vZQiRyKnk+ENhqg/19nP6CAheU3UwFaqOmzGokNkxuHG4rUbe+Ibgh6NwTW1fdJEVpwrv1MMCouDIKP2Tb8hY8loTNVaA7xPfXtc+955wPLDEcTn7R568JW02Addk9i1u9T7CpoZ+QbjmIcV6Jv3v0BMy0Ab15K9DBvukzhLPAm0tvf2lcQc0nvFpHVbY7pxJv4kqJWOgtPoL/iVxaL/oVuqq6KUN8W4dzFXqo11mki5YlsYcCuu34K4GX/mRCuPFAYkO+ZOS+XNiTNUI/dFcKA4eSip8+dQ9zG4zfLDuWybBlBq48ddzVwIKdKcv4SoZGIrI8CLLvrODAPYVoW4+G98i82sR+znJ+8ETAaOjE4twExUEgiIvoEW0JDV1PgmMNOyqHOaVQylplFJBiQBl/52IbyyeMaemP+19zYcUc2N3KW2nJNz2GO2e4pE5xX3uFMnaMB6hhuc1z30i1kmTLwjm0CS762vjpH9kieCI4jrpu/dWtsGhVQ2FsN/BBLo4tCRX7yksXbtZxsOvdH7PVbz54IfkSw9nqOXspUeESIZMcYNifdssrt17qYmYMcUgsVWFAvggCwyOi9pD3s8nazbRlHgsH1VUu2dMCmRFYF9X9Ko72jOtA5eDrWS7FKP6SGmeXHIdThSnZZBRGAz+4QQzlVg9Gq18b1IrYOfjzctlim0qsUYqGJMxwW9R/GM';
        // Make the GET request
        $response = $client->request('GET', 'https://qaext-io.eviivo.com/distribution/api/v2/available-properties?pageSize=10&indexOffset=10', [
            'headers' => [
                'Authorization' => 'bearer ' . $bearerToken,  // Set the Authorization header with the bearer token
                'X-Auth-ClientId' => 'd0c7d698-15b3-4b72-8ca9-b3229611ebdc',  // Your client ID
                'accept' => 'application/json',  // Ensure the response is in JSON format
            ],
        ]);

        // Output the response body
        $responseData = json_decode($response->getBody()->getContents(), true);
        return response()->json($responseData);
    }



}
