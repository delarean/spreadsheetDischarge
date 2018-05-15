<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AuthGoogleController extends Controller
{

    public function auth()
    {

        putenv('GOOGLE_APPLICATION_CREDENTIALS=/var/www/MySklad/service-account.json');


        $client = new \Google_Client();
        $client->useApplicationDefaultCredentials();

        $client->addScope("https://www.googleapis.com/auth/spreadsheets");

        $service = new \Google_Service_Sheets($client);

        $spreadsheetId = '1wKCUfq0Zr_ItIa4WzUMwLERewdCFYlIZZ9ETHo-q1Vk';
        $range = 'A2:D3';
        $valueInputOption = 'RAW';

        $values = [
            [
                'Хорошо',
                'Отлично',
                'Хорошо',
                'Отлично'
            ],
            [
                'Хорошо',
                'Отлично',
                'Хорошо',
                'Отлично'
            ],
        ];
        $data = [];
        $data[] = new \Google_Service_Sheets_ValueRange([
            'range' => $range,
            'values' => $values
        ]);

        $body = new \Google_Service_Sheets_BatchUpdateValuesRequest([
            'valueInputOption' => $valueInputOption,
            'data' => $data
        ]);

        $result = $service->spreadsheets_values->batchUpdate($spreadsheetId, $body);

    }

}
