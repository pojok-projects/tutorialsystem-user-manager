<?php

namespace App\Http\Controllers;

use GuzzleHttp\Client;

class UserController extends Controller
{
    private $client;
    private $endpoint;

    function __construct()
    {
        $this->client = new Client();
        $this->endpoint = env('ENDPOINT_API');
    }
}
