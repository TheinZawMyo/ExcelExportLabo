<?php

namespace App\Http\Controllers;

use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class HomeController extends Controller
{
    // getapi token
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
            return $responseData;
        } catch (\Exception $e) {
            // Handle the API request error
            return response()->json(['error' => 'API token request failed'], 500);
        }
    }
    
    private function fetchEmployeeFromAPI($offset){
        $client = new Client();
        $tokenToken = $this->getToken();

        //$token = 'your_auth_token_here'; // Replace with your actual token
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $tokenToken["token"],
        ])->get('https://rext.oapi.hrbrain.jp/members/v1/members?offset=0&limit=100&columns=6fda3cab-6717-43cf-9589-6a2c8e6a7c1f&columns=bad4f848-0121-4cd2-b802-d1419a262409&columns=9af83ee5-68c7-4617-a7f1-0e74b7647145&columns=4b274913-b293-4d40-b0e5-09e16645ba37&columns=3306efe7-6a15-48bc-a5e1-8a3bfd98e6a0&columns=2e3503be-6b46-419c-a783-f215e553205b&columns=53327d1a-e19b-44db-9fc4-c172dabaefc4&columns=b39639f1-c7b3-4daf-95c5-a7cbc786357d&columns=083cfa63-d1c3-4812-833a-cb11c69e4847&columns=7be17979-1920-4415-a386-5820edc4160e');
        
        if ($response->successful()) {
            $data = $response->json();           
            // Process the data as needed
            return response()->json($data);
        } else {
            // Handle API request error
            return response()->json(['error' => 'API request failed'], 500);
        }
    }
    public function getEmployee()
    {
        $client = new Client();
        $tokenToken = $this->getToken();

        //$token = 'your_auth_token_here'; // Replace with your actual token
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $tokenToken["token"],
        ])->get('https://rext.oapi.hrbrain.jp/members/v1/members?offset=0&limit=100&columns=6fda3cab-6717-43cf-9589-6a2c8e6a7c1f&columns=bad4f848-0121-4cd2-b802-d1419a262409&columns=9af83ee5-68c7-4617-a7f1-0e74b7647145&columns=4b274913-b293-4d40-b0e5-09e16645ba37&columns=3306efe7-6a15-48bc-a5e1-8a3bfd98e6a0&columns=2e3503be-6b46-419c-a783-f215e553205b&columns=53327d1a-e19b-44db-9fc4-c172dabaefc4&columns=b39639f1-c7b3-4daf-95c5-a7cbc786357d&columns=083cfa63-d1c3-4812-833a-cb11c69e4847&columns=7be17979-1920-4415-a386-5820edc4160e');
        
        if ($response->successful()) {
            $data = $response->json();
            
            // Process the data as needed
            $arrayEmployee =  response()->json($data);
            dd($arrayEmployee->count());
            dd($data["paging"]["totalCount"]);
        } else {
            // Handle API request error
            return response()->json(['error' => 'API request failed'], 500);
        }
    }
}
