<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use GuzzleHttp\Client;
use App\Traits\CheckDuplicate;

class UserFollowerController extends Controller
{
    use CheckDuplicate;

    private $client;
    private $endpoint;

    function __construct()
    {
        $this->client = new Client();
        $this->endpoint = env('ENDPOINT_API');
    }

    public function index()
    {
    	$result = $this->client->request('GET', $this->endpoint.'user/follower');

        if ($result->getStatusCode() != 200) {
            return response()->json([
                'status' => [
                    'code' => $result->getStatusCode(),
                    'message' => 'Bad Gateway',
                    // 'message' => $result->getStatusCode(),
                ]
            ], $result->getStatusCode());
        }

        return response()->json(json_decode($result->getBody(), true));
    }

    public function create(Request $request)
    {
        $rules = [
            'user_id' => 'required',
            'follower_user_id' => 'required',
        ];

        $customMessages = [
             'required' => 'Please fill attribute :attribute'
        ];
        $this->validate($request, $rules, $customMessages);

        //Check duplicate by USER_ID and FOLLOWER_USER_ID
        $query_check_duplicete = urlencode('"user_id='.$request->user_id.',follower_user_id='.$request->follower_user_id.'"');
        $check_duplicate = $this->ReqCheck($query_check_duplicete, 'follower');

        if ($check_duplicate['response'] === 500) {
            return response()->json([
                'status' => [
                    'code' => $check_duplicate['response'],
                    'message' => 'Bad Gateway',
                ]
            ], 500);
        }

        if ($check_duplicate['response']) {
            return response()->json([
                'status' => [
                    'code' => '409',
                    'message' => 'Duplicate',
                    'total' => count($check_duplicate['result']),
                ],
                'result' => $check_duplicate['result'],
            ], 409);
        }else{
            $result = $this->client->request('POST', $this->endpoint.'user/follower/store', [
                'form_params' => [
                    'user_id' => $request->user_id,
                    'follower_user_id' => $request->follower_user_id,
                ]
            ]);
            
            if ($result->getStatusCode() != 200) {
                return response()->json([
                    'status' => [
                        'code' => $result->getStatusCode(),
                        'message' => 'Bad Gateway',
                    ]
                ], $result->getStatusCode());
            }

            return response()->json(json_decode($result->getBody(), true));
        }
    }

    public function show($id)
    {
        $result = $this->client->request('GET', $this->endpoint.'user/follower/'.$id);

        if ($result->getStatusCode() != 200) {
            return response()->json([
                'status' => [
                    'code' => $result->getStatusCode(),
                    'message' => 'Bad Gateway',
                ]
            ], $result->getStatusCode());
        }

        return response()->json(json_decode($result->getBody(), true));
    }

    public function search(Request $request)
    {
        $rules = [
            'q' => 'required'
        ];

        $customMessages = [
             'required' => 'Please fill attribute :attribute'
        ];
        $this->validate($request, $rules, $customMessages);

        $query = urlencode('"'.$request->q.'"');

        $result = $this->client->request('POST', $this->endpoint.'user/follower/search', [
            'form_params' => [
                'query' => $query,
            ]
        ]);

        if ($result->getStatusCode() != 200) {
            return response()->json([
                'status' => [
                    'code' => $result->getStatusCode(),
                    'message' => 'Bad Gateway',
                ]
            ], $result->getStatusCode());
        }

        $search_follower = json_decode($result->getBody(), true);

        if ($search_follower['status']['total']==0) {
            return response()->json([
                'status' => [
                    'code' => $result->getStatusCode(),
                    'message' => 'not found!',
                ]
            ], $result->getStatusCode());
        }else{
            return response()->json($search_follower, $result->getStatusCode());  
        }    
    }

    public function update(Request $request, $id)
    { 
        $rules = [
            'user_id' => 'required',
            'follower_user_id' => 'required',
        ];

        $customMessages = [
            'required' => 'Please fill attribute :attribute'
        ];
        $this->validate($request, $rules, $customMessages);

        //Check duplicate by USER_ID and FOLLOWER_USER_ID
        $query_check_duplicete = urlencode('"user_id='.$request->user_id.',follower_user_id='.$request->follower_user_id.'"');
        $check_duplicate = $this->ReqCheck($query_check_duplicete, 'follower');

        if ($check_duplicate['response'] === 500) {
            return response()->json([
                'status' => [
                    'code' => $check_duplicate['response'],
                    'message' => 'Bad Gateway',
                ]
            ], 500);
        }

        if ($check_duplicate['response']) {
            return response()->json([
                'status' => [
                    'code' => '409',
                    'message' => 'Duplicate',
                    'total' => count($check_duplicate['result']),
                ],
                'result' => $check_duplicate['result'],
            ], 409);
        }else{
            $result = $this->client->request('POST', $this->endpoint.'user/follower/update/'.$id, [
                'form_params' => [
                    'user_id' => $request->user_id,
                    'follower_user_id' => $request->follower_user_id,
                ]
            ]);

            if ($result->getStatusCode() != 200) {
                return response()->json([
                    'status' => [
                        'code' => $result->getStatusCode(),
                        'message' => 'Bad Gateway',
                    ]
                ], $result->getStatusCode());
            }
            
            return response()->json(json_decode($result->getBody(), true));
        }
    }

    public function destroy($id)
    {
        $result = $this->client->request('POST', $this->endpoint.'user/follower/delete/'.$id);

        if ($result->getStatusCode() != 200) {
            return response()->json([
                'status' => [
                    'code' => $result->getStatusCode(),
                    'message' => 'Bad Gateway',
                ]
            ], $result->getStatusCode());
        }
        
        return response()->json(json_decode($result->getBody(), true));
    }
}
