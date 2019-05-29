<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Video;
use App\Project;
use App\Note;
use App\Video_note;
use App\Company;

class API extends Controller
{

    /** ----------------------------------------------------
     * Get
     *
     * @param $type
     * @param $linkedId
     * @return string
     */
    public function get($type, $linkedId = false) {

        $typeVariables = $this->_getTypeVariables($type);

        if($typeVariables['valid']){
            if ($linkedId) {
                $items = $typeVariables['model']::where($typeVariables['linkedTable'], $linkedId)->get();
            } else {
                $items = $typeVariables['model']::all();
            }

            $message = 'Success';
            $data = $items;
            $httpResponseCode = 200;
        } else {
            $message = 'Not Found';
            $data = '';
            $httpResponseCode = 404;
        }

        return response()->json([
            'message' => $message,
            'data' => $data
        ], $httpResponseCode);
    }

    /** ----------------------------------------------------
     * Create
     *
     * @param $request
     * @param $type
     * @return string
     */
    public function create(Request $request, $type) {

        $typeVariables = $this->_getTypeVariables($type);

        if($typeVariables['valid']){
            $isValid = $this->_checkIfValid($request->all(), $typeVariables['fields']);

            if ($isValid) {
                $item = $typeVariables['model']::create($request->all());
                if(!empty($item->id)) {
                    $message = 'Successfully added ' . $type . ' with id '.$item->id;
                    $data = $item;
                    $httpResponseCode = 201;
                } else {
                    $message = 'Failed uploading data in database.';
                    $data = '';
                    $httpResponseCode = 500;
                }
            } else {
                $message = 'Did not pass validator.';
                $data = '';
                $httpResponseCode = 400;
            }
        } else {
            $message = 'Not Found';
            $data = '';
            $httpResponseCode = 404;
        }

        return response()->json([
            'message' => $message,
            'data' => $data
        ], $httpResponseCode);
    }

    /** ----------------------------------------------------
     * Update
     *
     * @param $request
     * @param $type
     * @param $id
     * @return string
     */
    public function update(Request $request, $type ,$id) {

        $typeVariables = $this->_getTypeVariables($type);

        if($typeVariables['valid']){
            $isValid = $this->_checkIfValid($request->all(), $typeVariables['fields']);

            if ($isValid) {
                if (intval($id) === 0) {
                    $message = 'Invalid argument.';
                    $data = '';
                    $httpResponseCode = 400;
                } else {
                    $item = $typeVariables['model']::find($id);

                    if (!empty($item)) {
                        foreach ($typeVariables['fields'] as $key => $field){
                            $item->$key= $request->get($key);
                        }
                        $item->save();

                        $message = 'Successfully updated note with id ' . $item->id;
                        $data = $item;
                        $httpResponseCode = 201;
                    } else {
                        $message = $type.' with ID ' . $id . ' not found.';
                        $data = '';
                        $httpResponseCode = 404;
                    }
                }
            } else {
                $message = 'Did not pass validator.';
                $data = '';
                $httpResponseCode = 400;
            }
        } else {
            $message = 'Not Found';
            $data = '';
            $httpResponseCode = 404;
        }

        return response()->json([
            'message' => $message,
            'data' => $data,
        ], $httpResponseCode);
    }

    /** ----------------------------------------------------
     * Delete
     *
     * @param $type
     * @param $id
     * @return string
     */
    public function delete($type, $id) {
        $typeVariables = $this->_getTypeVariables($type);

        if($typeVariables['valid']){
            if (intval($id) === 0) {
                $message = 'Invalid argument.';
                $data = '';
                $httpResponseCode = 400;
            } else {
                $item = $typeVariables['model']::find($id);

                if (!empty($item)) {
                    $item->delete();

                    $message = 'Succesfully removed '.$type.' with id ' . $id;
                    $data = '';
                    $httpResponseCode = 201;
                } else {
                    $message = $type.' with ID ' . $id . ' not found.';
                    $data = '';
                    $httpResponseCode = 404;
                }
            }
        } else {
            $message = 'Not Found';
            $data = '';
            $httpResponseCode = 404;
        }

        return response()->json([
            'message' => $message,
            'data' => $data,
        ], $httpResponseCode);

    }

    /** ----------------------------------------------------
     * checkIfValid
     *
     * @param $data
     * @param $fields
     * @return bool
     */
    private function _checkIfValid($data, $fields){

        $validator = Validator::make($data, $fields);

        if ($validator->fails()) {
            $returnValue = false;
        } else {
            $returnValue = true;
        }

        return $returnValue;
    }

    /** ----------------------------------------------------
     * _getTypeVariables
     *
     * @param $type
     * @return array
     */
    private function _getTypeVariables($type){
        /**
         * Persoonlijk vind ik dit het meest overzichtelijk omdat je niet in denkbeeldige arrays zit te werken.
        **/
        $array = [
            'video' => [
                'model' => Video::class,
                'linkedTable' => 'project_id',
                'fields' => [
                    'project_id' => 'required|int',
                    'name' => 'required|string|max:255',
                    'link' => 'required|string|max:255'
                ],
                //'valid' => true
            ],

            'project' => [
                'model' => Project::class,
                'linkedTable' => 'company_id',
                'fields' => [
                    'company_id' => 'required|int',
                    'name' => 'required|string|max:255'
                ],
                //'valid' => true
            ],

            'note' => [
                'model' => Note::class,
                'linkedTable' => 'video',
                'fields' => [
                    'project_id' => 'required|int',
                    'title' => 'required|string|max:255',
                    'content' => 'required|string'
                ],
                //'valid' => true
            ],

            'video-note' => [
                'model' => Video_note::class,
                'linkedTable' => 'video',
                'fields' => [
                    'video_id' => 'required|int',
                    'content' => 'required|string',
                    'timestamp' => 'required|string'
                ],
                //'valid' => true
            ],

            'company' => [
                'model' => Company::class,
                'linkedTable' => '',
                'fields' => [
                    'name' => 'required|string|max:255',
                    'address' => 'required|string|max:255',
                    'phone' => 'required|string|max:255',
                    'email' => 'required|string|max:255'
                ],
                //'valid' => true
            ]
        ];

        // deze voegt aan alle types valid = true toe
        foreach ($array as $tableType) {
            $tableType['valid'] = true;
            // dit moet nog even getest worden
        };

        if (in_array($type, $array)) {
            $returnArray = $array[$type];
        } else {
            $returnArray = ['valid' => false];
        };

        return $returnArray;
    }
}