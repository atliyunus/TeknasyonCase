<?php

namespace App\Http\Controllers;

use App\Models\Device;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class DeviceController extends MainController
{
    public function insert(Request $request)
    {
        $validatedData = $request->validate([
            'uid' => 'required',
            'appId' => 'required',
            'language' => 'required',
            'operating_system'=>'required',
        ]);

        //Aynı device-App kaydı yoksa yeni device ekleyecek. Aksi durumda client-token response edilecek.
        $checkDevice=Device::where('uid',$request->uid)->where('appId',$request->appId);

        if ($checkDevice->count()==0){
            try {
                $validatedData['client_token'] = Str::random(50);
                $device=Device::create($validatedData);
                $msg['response']='200';
                return $this->sendResponse($device->toArray(), 'Device created successfully.');

            }catch( \Exception $e) {
                return $this->sendError($e, 'Device Not Create.');
            }
        }else {
            $checkDevice=$checkDevice->first();
            $msg['client_token']=$checkDevice->client_token;
            return $this->sendResponse( $msg,'Register OK!');
        }
    }
}
