<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class ExcelController extends Controller
{
    private $api = 'https://rext.oapi.hrbrain.jp';

    public $specificColumn          = [
        'last_name'                 => '6fda3cab-6717-43cf-9589-6a2c8e6a7c1f',
        'first_name'                => 'bad4f848-0121-4cd2-b802-d1419a262409',
        'employee_number'           => '083cfa63-d1c3-4812-833a-cb11c69e4847',
        'company'                   => '9af83ee5-68c7-4617-a7f1-0e74b7647145',
        'email'                     => '7be17979-1920-4415-a386-5820edc4160e',
        'birth_date'                => '29fc075c-37d1-420a-9f1c-55d9b08e585f',
        'age'                       => 'db33e9d9-9153-4fd6-b399-270e87421594',
        'department_id'             => '4b274913-b293-4d40-b0e5-09e16645ba37',
        'recruit_type_id'           => 'b2cd5df1-c472-43ed-a8d0-73d60b2cca97',
        'job_type_id'               => '883aac0d-ccad-4d85-85b0-b36244d420a1',
        'enrollment_status'         => 'e3009cad-e539-484e-abf6-628adfc3c25d',
        'hire_date'                 => '2e3503be-6b46-419c-a783-f215e553205b',
        'leave_date'                => '53327d1a-e19b-44db-9fc4-c172dabaefc4',
        'gender'                    => 'c8cf23d6-f58f-4077-bc3a-64db8aa7c689',
        'position_id'               => '3306efe7-6a15-48bc-a5e1-8a3bfd98e6a0',
        'employment_status_id'      => 'b39639f1-c7b3-4daf-95c5-a7cbc786357d',
        'nationality'               => 'a15979fd-bb5f-434e-b0c1-a8f431f49be7',
        'disability_type'           => '6f2a1519-973e-48a9-96a9-b8dd12fbfc63',
    ];

    // ============ GET TOKEN FOR AUTH ============ //
    private function getToken()
    {
        $endpoint = $this->api. "/auth/token";
        try {
            $response = Http::post($endpoint,[
                'clientId' => 'rext',
                'clientSecret' => 'QiRTxJ938LbznsHnq4Za6E6Jh9pEgwiS4PK9mfTg'
            ]);
            if($response->successful()) {
                return [
                    'Authorization' => 'Bearer ' . $response->object()->token,
                ];
            }
        } catch (\Exception $e) {
            Log::error('An error occurred: ' . $e->getMessage());
        }
        
    }

    public function changeColumnsToQueryParas($columns)
    {
        return implode("&columns=", $columns);
    }

    // ============== MAIN =================== //
    public function index()
    {
        $employoeeArr = $this->getAllEmployee(); // All Employee
        $companyArr = $this->getAllCompany(); // All Company
        $position = $this->getAllPosition(); // All Position
        dd($companyArr);
    }


    // ========= Get All employee =========== //
    public function getAllEmployee() {

        $token = $this->getToken();
        $employees = [];
        if($token) {
            try {
                $query_paras = $this->changeColumnsToQueryParas($this->specificColumn); 
                $endpoint = $this->api. "/members/v1/members?offset=0&limit=100&columns={$query_paras}";
                $response = Http::withHeaders($token)->get($endpoint);
                $employees = array_merge($employees, $response->object()->data);
                $count = intval($response['paging']['totalCount'] / 100);  // count to get all employee from paginated data
                $initNum = 0;
                for($i = 1; $i <= $count; $i++){
                    $offset = $initNum + ($i * 100);
                    $endpoint = $this->api. "/members/v1/members?columns={$query_paras}&limit=100&offset={$offset}";
                    $response = Http::withHeaders($token)->get($endpoint);

                    $employees = array_merge($employees, $response->object()->data);

                }
                return $employees;
            } catch (\Exception $e) {
                Log::error('An error occurred: ' . $e->getMessage());
            }
        }

    }


    // ================ GET ALL COMPANY ============= //
    public function getAllCompany() {
        $token = $this->getToken();

        if($token) {
            try {
                $endpoint = $this->api . "/members/v1/organization/82052994-d17a-41ca-8f08-086047b7206e/items";
                $response = Http::withHeaders($token)->get($endpoint);

                return $response->object()->items;
            } catch (\Exception $e) {
                Log::error('An error occurred: ' . $e->getMessage());
            }
        }
    }

    // ===================== GET ALL POSITION ============== //
    public function getAllPosition() {
        $token = $this->getToken();

        if($token) {
            try {
                $endpoint = $this->api . "/members/v1/organization/4e3e321a-1753-4f7d-b420-e2fccd3c0354/items";
                $response = Http::withHeaders($token)->get($endpoint);

                return $response->object()->items;
            } catch (\Exception $e) {
                Log::error('An error occurred: ' . $e->getMessage());
            }
        }
    }
}


