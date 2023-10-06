<?php

namespace App\Exports;
// use Maatwebsite\Excel\Concerns\FromCollection as ExcelFromCollection;
use Maatwebsite\Excel\Concerns\FromCollection;


class CompanyExport implements FromCollection
{
    private function getToken()
    {
        $client = new Client();
        try {
            $response = $client->post('https://rext.oapi.hrbrain.jp/auth/token', [
                'json' => [
                    'clientId' => 'rext',
                    'clientSecret' => 'QiRTxJ938LbznsHnq4Za6E6Jh9pEgwiS4PK9mfTg',
                ],
            ]);
            $responseData = json_decode($response->getBody(), true);
            // Process the response data as needed
            return [
                'Authorization' => 'Bearer ' . $responseData["token"],
            ];
        } catch (\Exception $e) {
            // Handle the API request error
            return response()->json(['error' => 'API token request failed'], 500);
        }
    }

    public function collection()
    {
        $token = $this->getToken();

        if($token){
            $response = Http::withHeaders($token)->get("https://rext.oapi.hrbrain.jp/members/v1/organization/82052994-d17a-41ca-8f08-086047b7206e/items");

            if ($response->successful()) {
                $get_company = $response->object()->items;
                dd($get_company);
                return $get_company;
            } else {
                // Handle API request error
                return response()->json(['error' => 'API request failed'], 500);
            }

        }
    }
}
