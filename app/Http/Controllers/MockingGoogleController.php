<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class MockingGoogleController extends MainController
{

    public function GoogleReceipt(Request $request){

        $datetime = Carbon::now()->addDays($request->day)->format("Y-m-d H:i:s");

        if (is_numeric(substr($request->receipt_id,-2)) && substr($request->receipt_id,-2) % 6==0){

            $data['status']='true';
            $data['expire_date']=$datetime;

            $response = [
                'success' => true,
                'data'    => $data,
                'rate_limit'=>true,
                'message' => "429 Too Many Requests",
            ];

        }
        else {
            if (is_numeric(substr($request->receipt_id,-1)) && substr($request->receipt_id,-1) % 2==1){

                $data['status']='true';
                $data['expire_date']=$datetime;

                $response = [
                    'success' => true,
                    'data'    => $data,
                    'rate_limit'=>false,
                    'message' => "OK",
                ];
            }else {
                $data['status']='false';
                $data['expire_date']=$datetime;
                $response = [
                    'success' => false,
                    'data'    => $data,
                ];
            }
        }




        return $response;
    }
}
