<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Ixudra\Curl\Facades\Curl;
class PhonePeController extends Controller
{
    public function phonepe($amount){
        $data = [
            "merchantId" => env('PHONE_PE_MERCHANT_ID'),
            "merchantTransactionId" => 'DEARHANDY'.time(),
            "merchantUserId" => env('PHONE_PE_MERCHANT_USER_ID'),
            "amount" => $amount,
            "redirectUrl" => route('response'),
            "redirectMode" => "POST",
            "callbackUrl" => route('response'),
            "mobileNumber" => "9789354285",
            "paymentInstrument" => [
                "type" => "PAY_PAGE",
            ]
        ];
        $encode = base64_encode(json_encode($data));
        //$salt_key = '099eb0cd-02cf-4e2a-8aca-3e6c6aff0399';
        $salt_key = 'b1a1d1fc-dcef-4cc1-9f15-93b595115c59';
        
        $salt_index = 1;
        $string = $encode.'/pg/v1/pay'.$salt_key;
        $sha256 = hash('sha256',$string);
        $final_x_header = $sha256.'###'.$salt_index;
        
        $response = Curl::to('https://api.phonepe.com/apis/hermes/pg/v1/pay')
              ->withHeader('Content-Type:application/json')
              ->withHeader('X-VERIFY:'.$final_x_header)
              ->withData(json_encode(['request' => $encode]))
              ->post();
        
        $result = json_decode($response);
        return redirect()->to($result->data->instrumentResponse->redirectInfo->url);
    }

    public function phonepe_response(Request $request){
        $input = $request->all();
        //$salt_key = '099eb0cd-02cf-4e2a-8aca-3e6c6aff0399';
        $salt_key = 'b1a1d1fc-dcef-4cc1-9f15-93b595115c59';
        $salt_index = 1;

        $final_x_header =  hash('sha256','/pg/v1/status/'.$input['merchantId'].'/'.$input['transactionId'].$salt_key).'###'.$salt_index;

        $response = Curl::to('https://api.phonepe.com/apis/hermes/pg/v1/status/'.$input['merchantId'].'/'.$input['transactionId'])
              ->withHeader('Content-Type:application/json')
              ->withHeader('accept:application/json')
              ->withHeader('X-VERIFY:'.$final_x_header)
              ->withHeader('X-MERCHANT-ID:'.$input['transactionId'])
              ->get();
        $result = json_decode($response);
        if($result->code = 'PAYMENT_SUCCESS'){
            return redirect()->to('/phonepe_success');
        }else{
            return redirect()->to('/phonepe_failed');
        }
    }

    public function phonepe_success(){
        echo "Payment Success :)";
    }

    public function phonepe_failed(){
        echo "Payment Failed :(";
    }
}
