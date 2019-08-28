<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use GuzzleHttp\Client;
use Illuminate\Support\Str;
use App\Traits\UserActivity;

class UserFollowingController extends Controller
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
            'following_user_id' => 'required|uuid'
        ];

        $customMessages = [
             'required' => 'Please fill attribute :attribute'
        ];
        $this->validate($request, $rules, $customMessages);

        //Get list activity
        $last_activity = json_decode($this->ListActivity($id, 'following'));        

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
        if (array_search($request->following_user_id, array_column($last_activity->result, 'following_user_id')) === false) {
            
            $uuid_following = (string) Str::uuid();
            $array_following = array([
                'id'                => $uuid_following,
                'following_user_id' => $request->following_user_id,
                'created_at'        => date(DATE_ATOM),
                'updated_at'        => date(DATE_ATOM)
            ]);

            $result = $this->client->request('POST', $this->endpoint.'user/update/'.$id, [
                'form_params' => [
                    'following' => array_merge($last_activity->result, $array_following)
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
                    'id' => $uuid_following
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

        if ($raw_user['following']) {
            $message    = ', data has been found';
            $total = count($raw_user['following']);
            $following_result = $raw_user['following'];
        } else {
            $message    = ', no data found';
            $total = 0;
            $following_result = [];
        }

        $following_data = array(
            'status' => [
                'code' => $result->getStatusCode(),
                'message' => 'list query has been performed'.$message,
                'total' => $total
            ],
            'result' => $following_result
        );

        return response()->json($following_data);
    }

    public function destroy($id_user, $id_following)
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
        $list_following = $raw_user['following'];
        $key = array_search($id_following, array_column($raw_user['following'], 'id'));

        if ( $key === false) {
            $message    = 'data not found';
        } else {
            unset($raw_user['following'][$key]);
            $result = $this->client->request('POST', $this->endpoint.'user/update/'.$id_user, [
                'form_params' => [
                    'following' => ( count($raw_user['following']) === 0 ? 0 : array_values($raw_user['following']) )
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
