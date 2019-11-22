<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use GuzzleHttp\Client;
use Illuminate\Support\Str;
use App\Traits\UserActivity;

class UserHistoryVideoController extends Controller
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
            'video_id' => 'required|uuid',
            'last_watch' => 'required'
        ];

        $customMessages = [
             'required' => 'Please fill attribute :attribute'
        ];
        $this->validate($request, $rules, $customMessages);

        //Get list activity
        $last_activity = json_decode($this->ListActivity($id, 'history_video'));        

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
        if (array_search($request->video_id, array_column($last_activity->result, 'video_id')) === false) {
            
            $uuid_history_video = (string) Str::uuid();
            $array_history_video = array([
                'id'                => $uuid_history_video,
                'video_id'          => $request->video_id,
                'last_watch'        => $request->last_watch,
                'created_at'        => date(DATE_ATOM),
                'updated_at'        => date(DATE_ATOM)
            ]);

            $result = $this->client->request('POST', $this->endpoint.'user/update/'.$id, [
                'json' => [
                    'history_video' => array_merge($last_activity->result, $array_history_video)
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
                    'id' => $uuid_history_video
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

        if ($raw_user['history_video']) {
            $message    = ', data has been found';
            $total = count($raw_user['history_video']);
            $history_video_result = $raw_user['history_video'];
        } else {
            $message    = ', no data found';
            $total = 0;
            $history_video_result = [];
        }

        $history_video_data = array(
            'status' => [
                'code' => $result->getStatusCode(),
                'message' => 'list query has been performed'.$message,
                'total' => $total
            ],
            'result' => $history_video_result
        );

        return response()->json($history_video_data);
    }

    public function update($id_user, $id_history_video, Request $request)
    { 
        //Rule request
        $rules = [
            'last_watch' => 'required'
        ];

        $customMessages = [
             'required' => 'Please fill attribute :attribute'
        ];
        $this->validate($request, $rules, $customMessages);

        //Get list activity
        $last_activity = json_decode($this->ListActivity($id_user, 'history_video')); 

        //Check connection dbil
        if ($last_activity->status->code != 200) {
            return response()->json([
                'status' => [
                    'code' => $last_activity->status->code,
                    'message' => 'Bad Gateway',
                ]
            ], $last_activity->status->code);
        }

        $key = array_search($id_history_video, array_column($last_activity->result, 'id'));
        
        if ( $key === false ) {

            return response()->json([
                'status' => [
                    'code' => 200,
                    'message' => 'data not found',
                ]
            ], 200);

        } else {

            //update last_watch
            $last_activity->result[$key]->last_watch = $request->last_watch;
    
            $result = $this->client->request('POST', $this->endpoint.'user/update/'.$id_user, [
                'form_params' => [
                    'history_video' => $last_activity->result
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
                    "id" => $id_history_video
                ]
            ), 200);

        }
    }
    
    public function destroy($id_user, $id_history_video)
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
        $list_history_video = $raw_user['history_video'];

        $key = array_search($id_history_video, array_column($raw_user['history_video'], 'id'));

        if ( $key === false ) {
            $message    = 'data not found';
        } else {
            unset($raw_user['history_video'][$key]);
            $result = $this->client->request('POST', $this->endpoint.'user/update/'.$id_user, [
                'json' => [
                    'history_video' => ( count($raw_user['history_video']) === 0 ? 0 : array_values($raw_user['history_video']) )
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
