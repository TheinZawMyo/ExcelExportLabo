<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class ExcelController extends Controller
{
    private $api = 'https://rext.oapi.hrbrain.jp/members/v1/';

    private function getToken()
    {
        $endpoint = $this->api. "/members?columns=6fda3cab-6717-43cf-9589-6a2c8e6a7c1f&columns=bad4f848-0121-4cd2-b802-d1419a262409&columns=9af83ee5-68c7-4617-a7f1-0e74b7647145&columns=4b274913-b293-4d40-b0e5-09e16645ba37&columns=3306efe7-6a15-48bc-a5e1-8a3bfd98e6a0&columns=2e3503be-6b46-419c-a783-f215e553205b&columns=53327d1a-e19b-44db-9fc4-c172dabaefc4&columns=b39639f1-c7b3-4daf-95c5-a7cbc786357d&columns=083cfa63-d1c3-4812-833a-cb11c69e4847&columns=7be17979-1920-4415-a386-5820edc4160e";
        $token = Http::get($endpoint);

        return $token;
    }
    /***
     *  Fetch API data
     */

    public function index()
    {
        $response = Http::get('http://example.com');
    }
}
