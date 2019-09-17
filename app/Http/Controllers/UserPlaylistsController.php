<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use GuzzleHttp\Client;
use Illuminate\Support\Str;
use App\Traits\UserActivity;

class UserPlaylistsController extends Controller
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
            'playlistcategory_id' => 'required|uuid',
            'metadata_id' => 'required|uuid'
        ];

        $customMessages = [
             'required' => 'Please fill attribute :attribute'
        ];
        $this->validate($request, $rules, $customMessages);

        //Get list activity
        $last_activity = json_decode($this->ListActivity($id, 'playlists'));        

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
        if (array_search($request->playlistcategory_id, array_column($last_activity->result, 'playlistcategory_id')) === false AND array_search($request->metadata_id, array_column($last_activity->result, 'metadata_id')) === false) {
            
            $uuid_playlists = (string) Str::uuid();
            $array_playlists = array([
                'id'                    => $uuid_playlists,
                'playlistcategory_id'   => $request->playlistcategory_id,
                'metadata_id'           => $request->metadata_id,
                'order_list'            => ($request->order_list ? $request->order_list : 0),
                'last_watch'            => ($request->last_watch ? $request->last_watch : 0),
                'created_at'            => date(DATE_ATOM),
                'updated_at'            => date(DATE_ATOM)
            ]);

            $result = $this->client->request('POST', $this->endpoint.'user/update/'.$id, [
                'form_params' => [
                    'playlists' => array_merge($last_activity->result, $array_playlists)
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
                    'id' => $uuid_playlists
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

        if ($raw_user['playlists']) {
            $message    = ', data has been found';
            $total = count($raw_user['playlists']);
            $playlists_result = $raw_user['playlists'];
        } else {
            $message    = ', no data found';
            $total = 0;
            $playlists_result = [];
        }

        $playlists_data = array(
            'status' => [
                'code' => $result->getStatusCode(),
                'message' => 'list query has been performed'.$message,
                'total' => $total
            ],
            'result' => $playlists_result
        );

        return response()->json($playlists_data);
    }

    public function update($id_user, $id_playlists, Request $request)
    { 
        //Rule request
        $rules = [
            'playlistcategory_id'   => 'uuid',
            'metadata_id'           => 'uuid',
            'order_list'            => 'integer',
            'last_watch'            => 'integer'
        ];

        $customMessages = [
             'required' => 'Please fill attribute :attribute'
        ];
        $this->validate($request, $rules, $customMessages);

        //Get list activity
        $last_activity = json_decode($this->ListActivity($id_user, 'playlists')); 

        //Check connection dbil
        if ($last_activity->status->code != 200) {
            return response()->json([
                'status' => [
                    'code' => $last_activity->status->code,
                    'message' => 'Bad Gateway',
                ]
            ], $last_activity->status->code);
        }

        $key = array_search($id_playlists, array_column($last_activity->result, 'id'));
        
        if ( $key === false ) {

            return response()->json([
                'status' => [
                    'code' => 200,
                    'message' => 'data not found',
                ]
            ], 200);

        } else {

            //update playlistcategory_id, metadata_id, order_list and last_watch
            $last_activity->result[$key]->playlistcategory_id   = ($request->playlistcategory_id ? $request->playlistcategory_id : $last_activity->result[$key]->playlistcategory_id);
            $last_activity->result[$key]->metadata_id           = ($request->metadata_id ? $request->metadata_id : $last_activity->result[$key]->metadata_id);
            $last_activity->result[$key]->order_list            = ($request->order_list ? $request->order_list : $last_activity->result[$key]->order_list);
            $last_activity->result[$key]->last_watch            = ($request->last_watch ? $request->last_watch : $last_activity->result[$key]->last_watch);
    
            $result = $this->client->request('POST', $this->endpoint.'user/update/'.$id_user, [
                'form_params' => [
                    'playlists' => $last_activity->result
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
                    "id" => $id_playlists
                ]
            ), 200);

        }
    }

    public function destroy($id_user, $id_playlists)
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
        $list_playlists = $raw_user['playlists'];
        $key = array_search($id_playlists, array_column($raw_user['playlists'], 'id'));

        if ( $key === false) {
            $message    = 'data not found';
        } else {
            unset($raw_user['playlists'][$key]);
            $result = $this->client->request('POST', $this->endpoint.'user/update/'.$id_user, [
                'form_params' => [
                    'playlists' => ( count($raw_user['playlists']) === 0 ? 0 : array_values($raw_user['playlists']) )
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
