<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use GuzzleHttp\Client;
use App\Traits\CheckDuplicate;

class UserController extends Controller
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
            'name'           => 'required|max:255|regex:/[a-zA-Z0-9\s]+/',
            'email'          => 'required|email',
            'first_name'     => 'required|max:255|regex:/[a-zA-Z0-9\s]+/',
            'last_name'      => 'required|max:255|regex:/[a-zA-Z0-9\s]+/',
            'birth_date'     => 'required|date',
            'gender'         => 'required|in:male,female',
            'photo_profile'  => 'max:256',
            'about'          => 'max:2000',

            'website_link'   => 'max:256|url',
            'facebook_link'  => 'max:256|url',
            'twitter_link'   => 'max:256|url',
            'linkedin_link'  => 'max:256|url',
        ];

        $customMessages = [
             'required' => 'Please fill attribute :attribute'
        ];
        $this->validate($request, $rules, $customMessages);

        //Check duplicate by EMAIL
        $query_check_duplicete = urlencode('"email='.$request->email.'"');
        $check_duplicate = $this->ReqCheck($query_check_duplicete);

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
                    'message' => 'Duplicate Email',
                    'total' => count($check_duplicate['result']),
                ],
                'result' => $check_duplicate['result'],
            ], 409);
        }else{
            $result = $this->client->request('POST', $this->endpoint.'user/store', [
                'form_params' => [
                    'name'           => $request->name,
                    'email'          => $request->email,
                    'first_name'     => $request->first_name,
                    'last_name'      => $request->last_name,
                    'birth_date'     => $request->birth_date,
                    'gender'         => $request->gender,
                    'photo_profile'  => $request->photo_profile,
                    'about'          => $request->about,

                    'website_link'   => $request->website_link,
                    'facebook_link'  => $request->facebook_link,
                    'twitter_link'   => $request->twitter_link,
                    'linkedin_link'  => $request->linkedin_link,
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
        $query = urlencode('"'.http_build_query($_GET,'',',').'"');
        $result = $this->client->request('POST', $this->endpoint.'user/search', [
            'form_params' => [
                'query' => $query
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

        return response()->json($search_category, $result->getStatusCode());  
    }

    public function update(Request $request, $id)
    { 
        $rules = [
            'name'           => 'max:256|regex:/[a-zA-Z0-9\s]+/',
            'email'          => 'email',
            'first_name'     => 'max:256|regex:/[a-zA-Z0-9\s]+/',
            'last_name'      => 'max:256|regex:/[a-zA-Z0-9\s]+/',
            'birth_date'     => 'date',
            'gender'         => 'in:male,female',
            'photo_profile'  => 'max:256',
            'about'          => 'max:2000',

            'website_link'   => 'max:256|url',
            'facebook_link'  => 'max:256|url',
            'twitter_link'   => 'max:256|url',
            'linkedin_link'  => 'max:256|url',
        ];

        $customMessages = [
            'required' => 'Please fill attribute :attribute'
        ];
        $this->validate($request, $rules, $customMessages);

        //Check duplicate by EMAIL
        $query_check_duplicete = urlencode('"email='.$request->email.'"');
        $check_duplicate = $this->ReqCheck($query_check_duplicete);

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
                    'name'           => $request->name,
                    'email'          => $request->email,
                    'first_name'     => $request->first_name,
                    'last_name'      => $request->last_name,
                    'birth_date'     => $request->birth_date,
                    'gender'         => $request->gender,
                    'photo_profile'  => $request->photo_profile,
                    'about'          => $request->about,

                    'website_link'   => $request->website_link,
                    'facebook_link'  => $request->facebook_link,
                    'twitter_link'   => $request->twitter_link,
                    'linkedin_link'  => $request->linkedin_link,
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
