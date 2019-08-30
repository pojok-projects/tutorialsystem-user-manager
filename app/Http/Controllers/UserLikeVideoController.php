<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use GuzzleHttp\Client;
use Illuminate\Support\Str;
use App\Traits\UserActivity;

class UserLikeVideoController extends Controller
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
            'user_id' => 'required|uuid',
            'video_id' => 'required|uuid'
        ];

        $customMessages = [
             'required' => 'Please fill attribute :attribute'
        ];
        $this->validate($request, $rules, $customMessages);

        //Get list activity
        $last_activity = json_decode($this->ListActivity($id, 'like_video'));        

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
        if (array_search($request->user_id, array_column($last_activity->result, 'user_id')) === false AND array_search($request->video_id, array_column($last_activity->result, 'video_id')) === false) {
            
            $uuid_like_video = (string) Str::uuid();
            $array_like_video = array([
                'id'                => $uuid_like_video,
                'user_id'           => $request->user_id,
                'video_id'          => $request->video_id,
                'created_at'        => date(DATE_ATOM),
                'updated_at'        => date(DATE_ATOM)
            ]);

            $result = $this->client->request('POST', $this->endpoint.'user/update/'.$id, [
                'form_params' => [
                    'like_video' => array_merge($last_activity->result, $array_like_video)
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
                    'id' => $uuid_like_video
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

        if ($raw_user['like_video']) {
            $message    = ', data has been found';
            $total = count($raw_user['like_video']);
            $like_video_result = $raw_user['like_video'];
        } else {
            $message    = ', no data found';
            $total = 0;
            $like_video_result = [];
        }

        $like_video_data = array(
            'status' => [
                'code' => $result->getStatusCode(),
                'message' => 'list query has been performed'.$message,
                'total' => $total
            ],
            'result' => $like_video_result
        );

        return response()->json($like_video_data);
    }

    public function destroy($id_user, $id_like_video)
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
        $list_like_video = $raw_user['like_video'];
        $key = array_search($id_like_video, array_column($raw_user['like_video'], 'id'));

        if ( $key === false) {
            $message    = 'data not found';
        } else {
            unset($raw_user['like_video'][$key]);
            $result = $this->client->request('POST', $this->endpoint.'user/update/'.$id_user, [
                'form_params' => [
                    'like_video' => ( count($raw_user['like_video']) === 0 ? 0 : array_values($raw_user['like_video']) )
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
