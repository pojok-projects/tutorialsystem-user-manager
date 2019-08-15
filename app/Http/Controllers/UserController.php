<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
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

    private function checkduplicate($name)
    {
        $result = $this->client->request('POST', $this->endpoint.'user/search', [
            'form_params' => [
                'name' => $name
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
    	$result = $this->client->request('GET', $this->endpoint.'user');

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

    public function create(Request $request)
    {
        $rules = [
            'name' => 'required|max:255|alpha_dash',
            'email' => 'required|email',
            'password' => [
                            'required',
                            'string',
                            'min:6',              // must be at least 10 characters in length
                            'regex:/[a-z]/',      // must contain at least one lowercase letter
                            'regex:/[A-Z]/',      // must contain at least one uppercase letter
                            'regex:/[0-9]/',      // must contain at least one digit
                            'regex:/[@$!%*#?&]/', // must contain a special character
                        ],
            'first_name' => 'required|max:255|alpha_dash',
            'last_name' => 'required|max:255|alpha_dash',
            'birth_date' => 'date',
            'gender' => 'required|in:male,female',
        ];

        $customMessages = [
             'required' => 'Please fill attribute :attribute'
        ];
        $this->validate($request, $rules, $customMessages);

        $check_duplicate = self::checkduplicate($request->name);

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
            $result = $this->client->request('POST', $this->endpoint.'user/store', [
                'form_params' => [
                    'name' => $request->name,
                    'email' => $request->email,
                    'password' => md5($request->password),
                    'first_name' => $request->first_name,
                    'last_name' => $request->last_name,
                    'birth_date' => $request->birth_date,
                    'gender' => $request->gender,
                    'photo_profile' => $request->photo_profile,
                    'about' => $request->about,
                    'website_link' => $request->website_link,
                    'facebook_link' => $request->facebook_link,
                    'twitter_link' => $request->twitter_link,
                    'linkedin_link' => $request->linkedin_link,
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
        $result = $this->client->request('GET', $this->endpoint.'user/'.$id);

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
            'name' => 'required|max:255|alpha_dash'
        ];

        $customMessages = [
             'required' => 'Please fill attribute :attribute'
        ];
        $this->validate($request, $rules, $customMessages);

        $name = $request->name;
        $result = $this->client->request('POST', $this->endpoint.'user/search', [
            'form_params' => [
                'name' => $name
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

        $search_category = json_decode($result->getBody(), true);

        if ($search_category['status']['total']==0) {
            return response()->json([
                'status' => [
                    'code' => $result->getStatusCode(),
                    'message' => 'Category not found',
                ]
            ], $result->getStatusCode());
        }else{
            return response()->json($search_category, $result->getStatusCode());  
        }    
    }

    public function update(Request $request, $id)
    { 
        $rules = [
            'name' => 'required|max:255|alpha_dash',
            'email' => 'required|email',
            'password' => [
                            'required',
                            'string',
                            'min:6',              // must be at least 10 characters in length
                            'regex:/[a-z]/',      // must contain at least one lowercase letter
                            'regex:/[A-Z]/',      // must contain at least one uppercase letter
                            'regex:/[0-9]/',      // must contain at least one digit
                            'regex:/[@$!%*#?&]/', // must contain a special character
                        ],
            'first_name' => 'required|max:255|alpha_dash',
            'last_name' => 'required|max:255|alpha_dash',
            'birth_date' => 'date',
            'gender' => 'required|in:male,female',
        ];

        $customMessages = [
            'required' => 'Please fill attribute :attribute'
        ];
        $this->validate($request, $rules, $customMessages);

        $check_duplicate = self::checkduplicate($request->name);

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
            $result = $this->client->request('POST', $this->endpoint.'user/update/'.$id, [
                'form_params' => [
                    'name' => $request->name,
                    'email' => $request->email,
                    'password' => md5($request->password),
                    'first_name' => $request->first_name,
                    'last_name' => $request->last_name,
                    'birth_date' => $request->birth_date,
                    'gender' => $request->gender,
                    'photo_profile' => $request->photo_profile,
                    'about' => $request->about,
                    'website_link' => $request->website_link,
                    'facebook_link' => $request->facebook_link,
                    'twitter_link' => $request->twitter_link,
                    'linkedin_link' => $request->linkedin_link,
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
        $result = $this->client->request('POST', $this->endpoint.'user/delete/'.$id);

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
