<?php 

namespace App\Traits;

trait HttpResponse{
    protected function success($data, $message=null, $code=200){
        return response()->json([
            "status"=>"Request okay!!",
            "message"=>$message,
            "data"=>$data
        ],$code);
    }


    protected function error($data, $message=null, $code){
        return response()->json([
            "status"=>422,
            "statusMessage"=>"somethig went worng!!!",
            "message"=>$message,
            "data"=>$data
        ],$code);
    }
}