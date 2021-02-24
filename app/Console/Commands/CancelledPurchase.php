<?php

namespace App\Console\Commands;

use App\Jobs\PurchaseCancelledJob;
use App\Models\Purchase;
use GuzzleHttp\Client;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class CancelledPurchase extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cancelledPurchase:cron';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cancelled Purchase Cron';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {

        $expireDateQuery=Purchase::where('status','=',1)
            ->where('active','=','1')
            ->where('expire_date','<',Carbon::today())
            ->leftJoin('device','device.client_token','=','purchase.client_token')
            ->selectRaw('`device`.`operating_system` as operating_system')
            ->selectRaw('`purchase`.`receipt_id` as receipt_id')
            ->selectRaw('`purchase`.`id` as purchase_id')
            ->get()
            ->toArray();


        foreach ($expireDateQuery as $eliminateRecord) {

            if ($eliminateRecord['operating_system']=='Android') {
                $endpoint='GoogleReceipt';
            }
            elseif($eliminateRecord['operating_system']=='IOS') {
                $endpoint='IOSReceipt';
            }

            $client = new Client(['base_uri' => 'http://127.0.0.1:8001']);
            $response = $client->request('POST', '/api/'.$endpoint.'',
                ['form_params' => [
                    'receipt_id' => $eliminateRecord['receipt_id'],
                    'day'=>'0'
                ],
                ]
            );

            $response = json_decode($response->getBody(),true);
            if ($response['success']==1){

                if ($response['rate_limit']==1) { // kuyruğa gönder

                    PurchaseCancelledJob::dispatch($eliminateRecord['purchase_id']);

                }else { // işlemi şimdi gerçeklşetir
                    $eliminateRecordQuery=Purchase::where('id','=',$eliminateRecord['purchase_id'])->update(['active'=>'0']);
                }

            }
        }


    }
}
