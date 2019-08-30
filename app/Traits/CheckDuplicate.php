<?php
namespace App\Traits;

use GuzzleHttp\Client;

trait CheckDuplicate
{
    private $client;
    private $endpoint;

    function __construct()
    {
        $this->client = new Client();
        $this->endpoint = env('ENDPOINT_API');
    }

    public function ReqCheck($query, $route='')
    {
        if (!empty($route)) {
            $route = $route.'/';
        }
        $result = $this->client->request('POST', $this->endpoint.'user/'.$route.'search', [
            'form_params' => [
                'query' => $query
            ]
        ]);
            
        if ($result->getStatusCode() != 200) {
            return [
                'response' => 500
            ];
        }else{
            $check_duplicate = json_decode($result->getBody(), true);
            if ($check_duplicate['status']['total'] == 0) {
                return [
                    'response' => false
                ];
            }else{
                return [
                    'response' => true,
                    'result' => $check_duplicate['result']
                ];
            }
        }
    }
}