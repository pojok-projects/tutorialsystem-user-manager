<?php
namespace App\Traits;

use GuzzleHttp\Client;

trait UserActivity
{
    private $client;
    private $endpoint;

    function __construct()
    {
        $this->client = new Client();
        $this->endpoint = env('ENDPOINT_API');
    }

    public function ListActivity($id_user, $activity)
    {
        $result = $this->client->request('GET', $this->endpoint.'user/'.$id_user);

        if ($result->getStatusCode() != 200) {
            return json_encode([
                'status' => [
                    'code'      => $result->getStatusCode()
                ],
                'result'    => []
            ]);
        }else{
            $raw_user = json_decode($result->getBody(), true);

            $activityuser = ($raw_user[$activity] ? $raw_user[$activity] : []);

            return json_encode([
                'status' => [
                    'code'      => $result->getStatusCode()
                ],
                'result'    => $activityuser
            ]);
        }
    }
}