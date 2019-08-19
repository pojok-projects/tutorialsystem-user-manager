<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use GuzzleHttp\Client;

class UserFollowingController extends Controller
{
    private $client;
    private $endpoint;

    function __construct()
    {
        $this->client = new Client();
        $this->endpoint = env('ENDPOINT_API');
    }

    private function checkduplicate($q)
    {
        $result = $this->client->request('POST', $this->endpoint.'user/following/search', [
            'form_params' => [
                'query' => $q
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

    public function index()
    {
    	$result = $this->client->request('GET', $this->endpoint.'user/following');

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
            'following_user_id' => 'required',
        ];

        $customMessages = [
             'required' => 'Please fill attribute :attribute'
        ];
        $this->validate($request, $rules, $customMessages);

        $chekquery = urlencode('user_id='.$request->user_id.',following_user_id='.$request->following_user_id);
        $check_duplicate = self::checkduplicate($chekquery);

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
                    'message' => 'Duplicate user',
                    'total' => count($check_duplicate['result']),
                ],
                'result' => $check_duplicate['result'],
            ], 409);
        }else{
            $result = $this->client->request('POST', $this->endpoint.'user/following/store', [
                'form_params' => [
                    'user_id' => $request->user_id,
                    'following_user_id' => $request->following_user_id,
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
        $result = $this->client->request('GET', $this->endpoint.'user/following/'.$id);

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

        $query = urlencode($request->q);

        $result = $this->client->request('POST', $this->endpoint.'user/following/search', [
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

        $search_following = json_decode($result->getBody(), true);

        if ($search_following['status']['total']==0) {
            return response()->json([
                'status' => [
                    'code' => $result->getStatusCode(),
                    'message' => 'not found!',
                ]
            ], $result->getStatusCode());
        }else{
            return response()->json($search_following, $result->getStatusCode());  
        }    
    }

    public function update(Request $request, $id)
    { 
        $rules = [
            'user_id' => 'required',
            'following_user_id' => 'required',
        ];

        $customMessages = [
            'required' => 'Please fill attribute :attribute'
        ];
        $this->validate($request, $rules, $customMessages);

        $chekquery = urlencode('user_id='.$request->user_id.',following_user_id='.$request->following_user_id);
        $check_duplicate = self::checkduplicate($chekquery);

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
                    'message' => 'Duplicate user',
                    'total' => count($check_duplicate['result']),
                ],
                'result' => $check_duplicate['result'],
            ], 409);
        }else{
            $result = $this->client->request('POST', $this->endpoint.'user/following/update/'.$id, [
                'form_params' => [
                    'user_id' => $request->user_id,
                    'following_user_id' => $request->following_user_id,
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
        $result = $this->client->request('POST', $this->endpoint.'user/following/delete/'.$id);

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
