<?php

namespace App\Http\Controllers;

use App\Models\Device;
use App\Models\Purchase;
use GuzzleHttp\Client;
use Illuminate\Http\Request;

class PurchaseController extends MainController
{
    public function insert(Request $request){

        //Parametre olarak abonelik tarihi(day) alınıyor
        $validator = $request->validate([
            'client_token' => 'required',
            'receipt_id'   => 'required',
            'day'           =>'nullable'
        ]);



        //Device'ın Purchase kaydı var mı ? kontrol.
        $checkPurchase=Purchase::where('client_token',$request->client_token)->where('status','1');

        if ($checkPurchase->count()==0) {

            //Device var ise devam et
            $checkOS=Device::where('client_token',$request->client_token);

            if ($checkOS->count()!=0) {
                $checkOS=$checkOS->first();

                //HTTPClient ile Mock API'ye bağlanacak.
                if ($checkOS->operating_system=='Android') {
                    $endpoint='GoogleReceipt';
                }
                elseif($checkOS->operating_system=='IOS') {
                    $endpoint='IOSReceipt';
                }else {
                    return $this->sendError( 'The OS is undefined.!');
                }

                //Mock Apı'den gelen response göre işlemler gerçekleşecek
                $client = new Client(['base_uri' => 'http://127.0.0.1:8001']);
                $response = $client->request('POST', '/api/'.$endpoint.'',
                    ['form_params' => [
                        'receipt_id' => $request->receipt_id,
                        'day'=>is_null($request->day)?'0':$request->day
                    ],
                    ]
                );
                $response = json_decode($response->getBody(),true);

                //Doğrulama başarılı ise yeni Purchase oluşturulacak
                if ($response['data']['status']=='true'){
                    try {
                        $data['status']="1";
                        $data['client_token']=$request->client_token;
                        $data['receipt_id']=$request->receipt_id;
                        $data['active']='1';
                        $data['expire_date']=$response['data']['expire_date'];
                        $data['day']=is_null($request->day)?'0':$request->day;
                        $purchase=Purchase::create($data);

                        return $this->sendResponse($purchase->toArray(), 'Purchase created successfully.');
                    }catch( \Exception $e) {
                        return $this->sendError($e, 'Purchase Not Create.');
                    }
                }else {
                    //Doğrulama başarısız ise yeni Purchase oluşturulacak ama status=0 olarak
                    try {
                        $data['status']="0";
                        $data['client_token']=$request->client_token;
                        $data['receipt_id']=$request->receipt_id;
                        $data['active']='0';
                        $data['expire_date']=$response['data']['expire_date'];
                        $data['day']=is_null($request->day)?'0':$request->day;
                        $purchase=Purchase::create($data);

                        return $this->sendResponse($purchase->toArray(), 'Purchase create failed.');
                    }catch( \Exception $e) {
                        return $this->sendError($e, 'Purchase Not Create.');
                    }
                }
            }
            else {
                return $this->sendError( 'Device Not Found.');
            }
        }else {


            $checkPurchase=$checkPurchase->first();
            $response['expire_date']=$checkPurchase->expire_date;
            return $this->sendResponse($response, 'Already have purchase.');
        }

    }

    //Purchase abonelik kontrolü
    public function check_subscription(Request $request){
        $validator = $request->validate([
            'client_token' => 'required'
        ]);
        $checkPurchase=Purchase::where('client_token',$request->client_token)->orderByDesc('id')->first();
        return $checkPurchase;
    }
}
