<?php

namespace App\Http\Controllers;

use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Collection;
use App\Exports\CompanyExport;
use Maatwebsite\Excel\Facades\Excel;

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
            return [
                'Authorization' => 'Bearer ' . $responseData["token"],
            ];
        } catch (\Exception $e) {
            // Handle the API request error
            return response()->json(['error' => 'API token request failed'], 500);
        }
    }

    public function show_download()
    {
        return view('welcome');
    }

    public function company_export()
    {
        // $get_company = $this->getCompany();
        // Convert the JSON response to a collection
        // $data = new Collection($get_company);

        // Create an instance of the export class
        // $export = new CompanyExport($data);
        // Generate and return the Excel file
        // return Excel::download($export, 'exported_data.xlsx');
        return Excel::download(new CompanyExport(), 'company_exported_data.xlsx');

    }

    public function getAllData()
    {
        $all_employee = $this->getEmployee();
        $changeHistory_employee = $this->changeHistory();
        $get_company = $this->getCompany();
        $get_position = $this->getPosition();
        $get_department = $this->getDepartment();
        // dd($get_department);
    }
    //get all employee
    private function getEmployee()
    {
        $token = $this->getToken();
        $all_employee = [];
        if($token){
            $response = Http::withHeaders($token)->get('https://rext.oapi.hrbrain.jp/members/v1/members?offset=0&limit=100&columns=6fda3cab-6717-43cf-9589-6a2c8e6a7c1f&columns=bad4f848-0121-4cd2-b802-d1419a262409&columns=9af83ee5-68c7-4617-a7f1-0e74b7647145&columns=4b274913-b293-4d40-b0e5-09e16645ba37&columns=3306efe7-6a15-48bc-a5e1-8a3bfd98e6a0&columns=2e3503be-6b46-419c-a783-f215e553205b&columns=53327d1a-e19b-44db-9fc4-c172dabaefc4&columns=b39639f1-c7b3-4daf-95c5-a7cbc786357d&columns=083cfa63-d1c3-4812-833a-cb11c69e4847&columns=7be17979-1920-4415-a386-5820edc4160e');

            if ($response->successful()) {
                $data = $response->json();
                $all_employee = array_merge($all_employee, $response->object()->data);
                $total_count = (int)($data["paging"]["totalCount"] / 100);
                // dd($total_count);
                for($i=1; $i<= $total_count; $i++){
                    $offset = $i * 100;
                    $response = Http::withHeaders($token)->get("https://rext.oapi.hrbrain.jp/members/v1/members?offset={$offset}&limit=100&columns=6fda3cab-6717-43cf-9589-6a2c8e6a7c1f&columns=bad4f848-0121-4cd2-b802-d1419a262409&columns=9af83ee5-68c7-4617-a7f1-0e74b7647145&columns=4b274913-b293-4d40-b0e5-09e16645ba37&columns=3306efe7-6a15-48bc-a5e1-8a3bfd98e6a0&columns=2e3503be-6b46-419c-a783-f215e553205b&columns=53327d1a-e19b-44db-9fc4-c172dabaefc4&columns=b39639f1-c7b3-4daf-95c5-a7cbc786357d&columns=083cfa63-d1c3-4812-833a-cb11c69e4847&columns=7be17979-1920-4415-a386-5820edc4160e");
                    $all_employee = array_merge($all_employee, $response->object()->data);
                }
                dd($all_employee);
            } else {
                // Handle API request error
                return response()->json(['error' => 'API request failed'], 500);
            }

        }

    }

    private function changeHistory()
    {
        $token = $this->getToken();

        if($token){
            $response = Http::withHeaders($token)->get("https://rext.oapi.hrbrain.jp/members/v1/member/487c50db-811c-45cb-9b19-26aa9619b4f9/listUnitGroup/402a9569-f9af-4fce-93a1-90d111929e90/blocks");

            if ($response->successful()) {

                $change_history_employee = $response->object()->data;

                return $change_history_employee;
            } else {
                // Handle API request error
                return response()->json(['error' => 'API request failed'], 500);
            }

        }

    }

    private function getCompany()
    {
        $token = $this->getToken();

        if($token){
            $response = Http::withHeaders($token)->get("https://rext.oapi.hrbrain.jp/members/v1/organization/82052994-d17a-41ca-8f08-086047b7206e/items");

            if ($response->successful()) {
                $get_company = $response->object()->items;

                return $get_company;
            } else {
                // Handle API request error
                return response()->json(['error' => 'API request failed'], 500);
            }

        }
    }

    private function getPosition()
    {
        $token = $this->getToken();

        if($token){
            $response = Http::withHeaders($token)->get("https://rext.oapi.hrbrain.jp/members/v1/organization/4e3e321a-1753-4f7d-b420-e2fccd3c0354/items");

            if ($response->successful()) {
                $get_position = $response->object()->items;
                return $get_position;
            } else {
                // Handle API request error
                return response()->json(['error' => 'API request failed'], 500);
            }

        }
    }

    private function getDepartment()
    {
        $token = $this->getToken();

        if($token){
            $response = Http::withHeaders($token)->get("https://rext.oapi.hrbrain.jp/members/v1/organization/f46eb059-6dda-4d63-874e-a7a2f82a12ed/items");

            if ($response->successful()) {
                $get_department = $response->object()->items;

                return $get_department;
            } else {
                // Handle API request error
                return response()->json(['error' => 'API request failed'], 500);
            }

        }
    }

}
