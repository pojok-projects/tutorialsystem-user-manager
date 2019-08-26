<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use GuzzleHttp\Client;
use Illuminate\Support\Str;
use App\Traits\CheckDuplicate;
use App\Traits\UserActivity;

class UserFollowingController extends Controller
{
    use CheckDuplicate;
    use UserActivity;

    private $client;
    private $endpoint;

    function __construct()
    {
        $this->client = new Client();
        $this->endpoint = env('ENDPOINT_API');
    }

    // public function index()
    // {
    // 	$result = $this->client->request('GET', $this->endpoint.'user/following');

    //     if ($result->getStatusCode() != 200) {
    //         return response()->json([
    //             'status' => [
    //                 'code' => $result->getStatusCode(),
    //                 'message' => 'Bad Gateway',
    //             ]
    //         ], $result->getStatusCode());
    //     }

    //     return response()->json(json_decode($result->getBody(), true));
    // }

    public function create($id, Request $request)
    {
        $rules = [
            'following_user_id' => 'required|uuid'
        ];

        $customMessages = [
             'required' => 'Please fill attribute :attribute'
        ];
        $this->validate($request, $rules, $customMessages);

        //Check duplicate by FOLLOWING_USER_ID
        // $query_check_duplicete = urlencode('"user_id='.$request->user_id.',following_user_id='.$request->following_user_id.'"');
        // $check_duplicate = $this->ReqCheck($query_check_duplicete, 'following');

        // if ($check_duplicate['response'] === 500) {
        //     return response()->json([
        //         'status' => [
        //             'code' => $check_duplicate['response'],
        //             'message' => 'Bad Gateway',
        //         ]
        //     ], 500);
        // }

        // if ($check_duplicate['response']) {
        //     return response()->json([
        //         'status' => [
        //             'code' => '409',
        //             'message' => 'Duplicate',
        //             'total' => count($check_duplicate['result']),
        //         ],
        //         'result' => $check_duplicate['result'],
        //     ], 409);
        // }else{

            $last_activity = json_decode($this->LastActivity($id, 'following'));

            $array_following = array([
                'id'                => (string) Str::uuid(),
                'following_user_id' => $request->following_user_id,
                'created_at'        => date(DATE_ATOM),
                'updated_at'        => date(DATE_ATOM)
            ]);

            $result = $this->client->request('POST', $this->endpoint.'user/update/'.$id, [
                'form_params' => [
                    'following' => array_merge($last_activity, $array_following)
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
        // }
    }

    public function show($id)
    {
        $result = $this->client->request('GET', $this->endpoint.'user/'.$id);

        if ($result->getStatusCode() != 200) {
            return response()->json([
                'status' => [
                    'code' => $result->getStatusCode(),
                    'message' => 'Bad Gateway',
                ]
            ], $result->getStatusCode());
        }

        $raw_user = json_decode($result->getBody(), true);

        $following_data = array(
            'status' => [
                'code' => $result->getStatusCode(),
                'message' => 'list query has been performed, data has been found',
                'total' => ($raw_user['following'] ? count($raw_user['following']) : 0)
            ],
            'result' => ($raw_user['following'] ? $raw_user['following'] : [])
        );

        return response()->json($following_data);

        // return response()->json(json_decode($result->getBody(), true));
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

        //Check duplicate by USER_ID and FOLLOWING_USER_ID
        $query_check_duplicete = urlencode('"user_id='.$request->user_id.',following_user_id='.$request->following_user_id.'"');
        $check_duplicate = $this->ReqCheck($query_check_duplicete, 'following');

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
