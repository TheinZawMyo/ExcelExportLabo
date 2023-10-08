<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

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

    // ============== GENERATE EXCEL FOR ALL EMPLOYEE BY YEAR =================== //
    public function generateExcel(Request $request)
    {
        set_time_limit(1200);
        $selectedYear = $request->year;

        $employoeeArr = $this->getAllEmployee(); // All Employee
        $companyArr = $this->getEmployeeInfo("82052994-d17a-41ca-8f08-086047b7206e"); // All Company
        $positionArr = $this->getEmployeeInfo("4e3e321a-1753-4f7d-b420-e2fccd3c0354"); // All Position
        $departmentArr = $this->getEmployeeInfo("f46eb059-6dda-4d63-874e-a7a2f82a12ed"); // All department
        $empStatusArr = $this->getEmployeeInfo("8caf6aea-6e5b-449d-8c86-9bc56073a407"); // All emp status

        $token = $this->getToken();
        $addOnYear = $selectedYear + 1;
        $filteredEmployeeData = array_filter($employoeeArr, function($data) use($selectedYear, $addOnYear){
            $enterDate = NULL;
            $leaveDate = NULL;

            foreach($data->fields as $field) {
                if($field->alias === "EnteredDay") {
                    $enterDate = $field->value;
                }

                if($field->id === "53327d1a-e19b-44db-9fc4-c172dabaefc4") {
                    $leaveDate = $field->value;
                }
            }

            return ($enterDate !== NULL && $enterDate !== "" && $enterDate < "{$addOnYear}/01/01") && ($leaveDate === NULL || $leaveDate >= "{$selectedYear}/01/01");
        });


        // ====== GET DATA OF EACH EMPLOYEE
        $combinedData = [];
        $filteredDepartment = NULL;
        $row = 2;

        $templateFile = public_path('templates/employee_template.xlsx');

        $spreadsheet = IOFactory::load($templateFile);

        $sheet = $spreadsheet->getActiveSheet();

        foreach($filteredEmployeeData as $employee) {
            $employeeId = $employee->id;

            // ====== Change History List
            $endpoint = $this->api. "/members/v1/member/{$employeeId}/listUnitGroup/402a9569-f9af-4fce-93a1-90d111929e90/blocks";
            $historyResponse = Http::timeout(1200)->withHeaders($token)->get($endpoint);

            $statusCode = $historyResponse->getStatusCode();

            if($statusCode === 401) {
                $token = $this->getToken();
                $historyResponse = Http::timeout(1200)->withHeaders($token)->get($endpoint);
            }

            $historyData = ($historyResponse->successful()) ? $historyResponse->object()->data : "";

            $companyId = NULL;
            $deptId = NULL;
            $positionId = NULL;
            $statusId = NULL;
            $hireDate = "";
            $resignDate = "";
            foreach($employee->fields as $field) {
                if($field->id === "9af83ee5-68c7-4617-a7f1-0e74b7647145") {
                    $companyId = $field->value;
                }

                if($field->id === "2e3503be-6b46-419c-a783-f215e553205b") {
                    $hireDate = $field->value;
                }

                if($field->id === "53327d1a-e19b-44db-9fc4-c172dabaefc4") {
                    $resignDate = $field->value;
                }

                if($field->id === "3306efe7-6a15-48bc-a5e1-8a3bfd98e6a0") {
                    $positionId = $field->value;
                }

                if($field->id === "4b274913-b293-4d40-b0e5-09e16645ba37") {
                    $deptId = $field->value;
                }

                if($field->id === "b39639f1-c7b3-4daf-95c5-a7cbc786357d") {
                    $statusId = $field->value;
                }
            }
            $filteredCompanyData = array_filter($companyArr, function($company) use($companyId) {
                return $company->id == $companyId;
            });

            $filteredPositionData = array_filter($positionArr, function($position) use($positionId) {
                return $position->id == $positionId;
            });

            $filteredEmploymentData = array_filter($empStatusArr, function($empStatus) use($statusId) {
                return $empStatus->id = $statusId;
            });



            $filteredDepartment = array_filter($departmentArr, function($department) use($deptId) {
                if(isset($department->items)) {
                    foreach($department->items as $dept) {

                        return $dept->id == $deptId;
                    }
                }
            });


            $companyData = array_values($filteredCompanyData);
            $departmentData = array_values($filteredDepartment);
            $positionData = array_values($filteredPositionData);
            $empStatusData = array_values($filteredEmploymentData);



            if(empty($historyData)) {
                $sheet->setCellValue('A'. $row, $employee->fields[0]->value. "". $employee->fields[1]->value);
                $sheet->setCellValue('B'. $row, isset($companyData[0]->value) ? ($companyData[0]->value) : '-');
                $sheet->setCellValue('C'. $row, isset($departmentData[0]->value) ? $departmentData[0]->value : '-');
                $sheet->setCellValue('D'. $row, "-");
                $sheet->setCellValue('E'. $row, isset($positionData[0]->value) ? $positionData[0]->value : '-');
                $sheet->setCellValue('F'. $row, $hireDate);
                $sheet->setCellValue('G'. $row, $resignDate);
                $sheet->setCellValue('H'. $row, isset($empStatusData[0]->value) ? $empStatusData[0]->value : '-');
                // $sheet->setCellValue('I'. $row, $employee->id);
                // $combinedData[] = [
                //     "employeeId" => $employee->id,
                //     "employee_name" => $employee->fields[0]->value. "". $employee->fields[1]->value,
                //     "company" => isset($companyData[0]->value) ? ($companyData[0]->value) : '-',
                //     "department" => isset($departmentData[0]->value) ? $departmentData[0]->value : '-',
                //     "transfer_date" => "-",
                //     "positon" => isset($positionData[0]->value) ? $positionData[0]->value : '-',
                //     "emp_status" => isset($empStatusData[0]->value) ? $empStatusData[0]->value : '-',
                //     "hire_date" => $hireDate,
                //     "resign_date" => $resignDate
                // ];
            }else {
                foreach($historyData as $history) {
                    $sheet->setCellValue('A'. $row, $employee->fields[0]->value. "". $employee->fields[1]->value);
                    $sheet->setCellValue('B'. $row, $history->fields[0]->value);
                    $sheet->setCellValue('C'. $row, $history->fields[2]->value);
                    $sheet->setCellValue('D'. $row, $history->fields[1]->value);
                    $sheet->setCellValue('E'. $row, isset($positionData[0]->value) ? $positionData[0]->value : '-');
                    $sheet->setCellValue('F'. $row, $hireDate);
                    $sheet->setCellValue('G'. $row, $resignDate);
                    $sheet->setCellValue('H'. $row, isset($empStatusData[0]->value) ? $empStatusData[0]->value : '-');
                    // $sheet->setCellValue('I'. $row, $employee->id);
                    // $combinedData[] = [
                    //     "employeeId" => $employee->id,
                    //     "employeeName" => $employee->fields[0]->value. "". $employee->fields[1]->value,
                    //     "company" => $history->fields[0]->value,
                    //     "department" => $history->fields[2]->value,
                    //     "transfer_date" => $history->fields[1]->value,
                    //     "positon" => isset($positionData[0]->value) ? $positionData[0]->value : '-',
                    //     "emp_status" => isset($empStatusData[0]->value) ? $empStatusData[0]->value : '-',
                    //     "hire_date" => $hireDate,
                    //     "resign_date" => $resignDate
                    // ];
                    $row ++;
                }
                $row --;
            }

            $row++;
        }

        $response = response()->stream(function () use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        }, 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="employees.xlsx"',
        ]);

        return $response;

        // return response()->json(count($combinedData), 200);
    }


    // ========= Get All employee =========== //
    public function getAllEmployee() {

        // $cacheKey = 'all_employees';
        // $cacheMinutes = 60;
        // $cachedData = Cache::get($cacheKey);

        // if ($cachedData !== null) {
        //     return $cachedData;
        // }

        $token = $this->getToken();
        $employees = [];
        if($token) {
            try {
                $query_paras = $this->changeColumnsToQueryParas($this->specificColumn);
                $endpoint = $this->api. "/members/v1/members?offset=0&limit=100&columns={$query_paras}";
                $response = Http::withHeaders($token)->get($endpoint);
                $employees = array_merge($employees, $response->object()->data);
                $count = intval($response['paging']['totalCount'] / 100);  // count to get all employee from paginated data

                for($i = 1; $i <= $count; $i++){
                    $offset = $i * 100;
                    $endpoint = $this->api. "/members/v1/members?columns={$query_paras}&limit=100&offset={$offset}";
                    $response = Http::withHeaders($token)->get($endpoint);

                    $employees = array_merge($employees, $response->object()->data);

                }

                // Cache::put($cacheKey, $employees, $cacheMinutes);
                return $employees;
            } catch (\Exception $e) {
                Log::error('An error occurred: ' . $e->getMessage());
            }
        }

    }


    // ================ GET INFO FOR EMPLOYEE ============= //
    public function getEmployeeInfo($item) {
        $token = $this->getToken();

        if($token) {
            try {
                $endpoint = $this->api . "/members/v1/organization/{$item}/items";
                $response = Http::withHeaders($token)->get($endpoint);

                return $response->object()->items;
            } catch (\Exception $e) {
                Log::error('An error occurred: ' . $e->getMessage());
            }
        }
    }

}
