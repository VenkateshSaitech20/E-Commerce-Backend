<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Notification;
use App\Models\CustomerNotification;
use App\Models\RestaurantNotification;
use App\Models\DeliveryBoyNotification;
use App\Models\NotificationType;
use App\Models\Status;
use App\Models\CustomerAddress;
use Validator;



class NotificationController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function customer_notification(Request $request)
    {   
        $input = $request->all();

        $validator =  Validator::make($input,[
            'destination_id' => 'required',
        ]);

        if($validator->fails()){
            return $this->sendError($validator->errors());
        }
        
        $zone_ids = CustomerAddress::where('customer_id',$input['destination_id'])->pluck('zone_id')->toArray();
        $data = CustomerNotification::whereIN('destination_id',[0,$input['destination_id']])->orWhereIn('zone_id',$zone_ids)->get();
        
        return response()->json([
            "result" => $data,
            "count" => count($data),
            "message" => 'Success',
            "status" => 1
        ]);
    }




     public function restaurant_notification(Request $request)
    {   
         $input = $request->all();

        $validator =  Validator::make($input,[
            'destination_id' => 'required',
        ]);

         if($validator->fails()){
            return $this->sendError($validator->errors());
        }
        $data = RestaurantNotification::whereIN('destination_id',[0,$input['destination_id']])->get();
        
        return response()->json([
            "result" => $data,
            "count" => count($data),
            "message" => 'Success',
            "status" => 1
        ]);
    }

    public function delivery_partner_notification(Request $request)
    {   
         $input = $request->all();

        $validator =  Validator::make($input,[
            'destination_id' => 'required',
        ]);

         if($validator->fails()){
            return $this->sendError($validator->errors());
        }
        $data = DeliveryBoyNotification::whereIN('destination_id',[0,$input['destination_id']])->get();
        
        return response()->json([
            "result" => $data,
            "count" => count($data),
            "message" => 'Success',
            "status" => 1
        ]);
    }


    public function get_notification_type()
    {
        $data = Notification::where('status',1)->get();
        
        return response()->json([
            "result" => $data,
            "count" => count($data),
            "message" => 'Success',
            "status" => 1
        ]);
    }


     public function sendError($message) {
        $message = $message->all();
        $response['error'] = "validation_error";
        $response['message'] = implode('',$message);
        $response['status'] = "0";
        return response()->json($response, 200);
    }
    
   
}


 


