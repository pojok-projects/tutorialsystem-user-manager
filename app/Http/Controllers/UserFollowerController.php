<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use GuzzleHttp\Client;
use Illuminate\Support\Str;
use App\Traits\UserActivity;

class UserFollowerController extends Controller
{
    use UserActivity;

    private $client;
    private $endpoint;

    function __construct()
    {
        $this->client = new Client();
        $this->endpoint = env('ENDPOINT_API');
    }

    public function create($id, Request $request)
    {
        //Rule request
        $rules = [
            'follower_user_id' => 'required|uuid'
        ];

        $customMessages = [
             'required' => 'Please fill attribute :attribute'
        ];
        $this->validate($request, $rules, $customMessages);

        //Get list activity
        $last_activity = json_decode($this->ListActivity($id, 'follower'));        

        //Check connection dbil
        if ($last_activity->status->code != 200) {
            return response()->json([
                'status' => [
                    'code' => $last_activity->status->code,
                    'message' => 'Bad Gateway',
                ]
            ], $last_activity->status->code);
        }

        //check duplicate
        if (array_search($request->follower_user_id, array_column($last_activity->result, 'follower_user_id')) === false) {
            
            $uuid_follower = (string) Str::uuid();
            $array_follower = array([
                'id'                => $uuid_follower,
                'follower_user_id' => $request->follower_user_id,
                'created_at'        => date(DATE_ATOM),
                'updated_at'        => date(DATE_ATOM)
            ]);

            $result = $this->client->request('POST', $this->endpoint.'user/update/'.$id, [
                'form_params' => [
                    'follower' => array_merge($last_activity->result, $array_follower)
                ]
            ]);
            
            //Check connection dbil
            if ($result->getStatusCode() != 200) {
                return response()->json([
                    'status' => [
                        'code' => $result->getStatusCode(),
                        'message' => 'Bad Gateway',
                    ]
                ], $result->getStatusCode());
            }

            return response()->json(array(
                'status' => [
                    'code' => $result->getStatusCode(),
                    'message' => 'data has been saved',
                ],
                'result' => [
                    'id' => $uuid_follower
                ]
            ), 200);

        } else {

            return response()->json([
                'status' => [
                    'code' => 409,
                    'message' => 'Duplicate',
                ],
                'result' => []
            ], 409);
            
        }
    }

    public function show($id)
    {
        $result = $this->client->request('GET', $this->endpoint.'user/'.$id);

        //Check connection dbil
        if ($result->getStatusCode() != 200) {
            return response()->json([
                'status' => [
                    'code' => $result->getStatusCode(),
                    'message' => 'Bad Gateway',
                ]
            ], $result->getStatusCode());
        }

        $raw_user = json_decode($result->getBody(), true);

        if ($raw_user['follower']) {
            $message    = ', data has been found';
            $total = count($raw_user['follower']);
            $follower_result = $raw_user['follower'];
        } else {
            $message    = ', no data found';
            $total = 0;
            $follower_result = [];
        }

        $follower_data = array(
            'status' => [
                'code' => $result->getStatusCode(),
                'message' => 'list query has been performed'.$message,
                'total' => $total
            ],
            'result' => $follower_result
        );

        return response()->json($follower_data);
    }

    public function destroy($id_user, $id_follower)
    {
        $result = $this->client->request('GET', $this->endpoint.'user/'.$id_user);

        //Check connection dbil
        if ($result->getStatusCode() != 200) {
            return response()->json([
                'status' => [
                    'code' => $result->getStatusCode(),
                    'message' => 'Bad Gateway',
                ]
            ], $result->getStatusCode());
        }

        $raw_user = json_decode($result->getBody(), true);
        $list_follower = $raw_user['follower'];
        $key = array_search($id_follower, array_column($raw_user['follower'], 'follower_user_id'));

        if ( $key === false) {
            $message    = 'data not found';
        } else {
            unset($raw_user['follower'][$key]);
            $result = $this->client->request('POST', $this->endpoint.'user/update/'.$id_user, [
                'form_params' => [
                    'follower' => array_values($raw_user['follower'])
                ]
            ]);
            
            //Check connection dbil
            if ($result->getStatusCode() != 200) {
                return response()->json([
                    'status' => [
                        'code' => $result->getStatusCode(),
                        'message' => 'Bad Gateway',
                    ]
                ], $result->getStatusCode());
            }

            $message = 'data has been delete';
        }

        return response()->json(array(
            'status' => [
                'code' => $result->getStatusCode(),
                'message' => $message,
            ]
        ), 200);
    }
}
