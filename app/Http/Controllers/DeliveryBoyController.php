<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DeliveryBoy;
use App\Models\DeliveryBoyEarning;
use App\Models\DeliveryBoyWalletHistory;
use App\Models\DeliveryBoyWithdrawal;
use Validator;
use LaravelFCM\Message\OptionsBuilder;
use LaravelFCM\Message\PayloadDataBuilder;
use LaravelFCM\Message\PayloadNotificationBuilder;
use FCM;
use Carbon\Carbon;
use App\FcmNotification;
use Illuminate\Support\Facades\Hash;
use Kreait\Firebase;
use Kreait\Firebase\Factory;
use Kreait\Firebase\ServiceAccount;
use Kreait\Firebase\Database;
use Illuminate\Support\Facades\DB;
use App\Models\Status;
use App\Models\Zone;
class DeliveryBoyController extends Controller
{
    //
    public function login(Request $request){

        $input = $request->all();
        $validator = Validator::make($input, [
            'phone_with_code' => 'required',
            'password' => 'required',
            'fcm_token' => 'required'
        ]);

        if ($validator->fails()) {
            //return $this->sendError($validator->errors());
        }

        $credentials = request(['phone_with_code', 'password']);
        $delivery_boy = DeliveryBoy::where('phone_with_code',$credentials['phone_with_code'])->first();

        if (!($delivery_boy)) {
            return response()->json([
                "message" => 'Invalid phone number or password',
                "status" => 0
            ]);
        }
        
        if (Hash::check($credentials['password'], $delivery_boy->password)) {
            if($delivery_boy->status == 1){
                
                DeliveryBoy::where('id',$delivery_boy->id)->update([ 'fcm_token' => $input['fcm_token']]);
                
                return response()->json([
                    "result" => $delivery_boy,
                    "message" => 'Success',
                    "status" => 1
                ]);   
            }else{
                return response()->json([
                    "message" => 'Your account has been blocked',
                    "status" => 0
                ]);
            }
        }else{
            return response()->json([
                "message" => 'Invalid phone number or password',
                "status" => 0
            ]);
        }

    }

    public function check_phone(Request $request)
    {

        $input = $request->all();
        $validator = Validator::make($input, [
            'phone_with_code' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->sendError($validator->errors());
        }
        $data = array();
        $delivery_boy = DeliveryBoy::where('phone_with_code',$input['phone_with_code'])->first();

        if(is_object($delivery_boy)){
            $data['is_available'] = 1;
            $data['otp'] = "";
            return response()->json([
                "result" => $data,
                "message" => 'Success',
                "status" => 1
            ]);
        }else{
            $data['is_available'] = 0;
            $data['otp'] = rand(1000,9999);
            if(env('MODE') != 'DEMO'){
                $message = "Hi, from ".env('APP_NAME'). "  , Your OTP code is:".$data['otp'];
                $this->sendSms($input['phone_with_code'],$message);
            }
            return response()->json([
                "result" => $data,
                "message" => 'Success',
                "status" => 1
            ]);
        }
    }
    public function register(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'delivery_boy_name' => 'required',
            'phone_number' => 'required|numeric|unique:delivery_boys,phone_number',
            'phone_with_code' => 'required',
            'aadhar_no' => 'required',
            'license_no' => 'required',
            'profile_picture' => 'required',
            'password' => 'required',
            'fcm_token' => 'required',
            'zone_id' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->sendError($validator->errors());
        }

        $options = [
            'cost' => 12,
        ];
        $input['password'] = password_hash($input["password"], PASSWORD_DEFAULT, $options);
        $input['status'] = 1;
        

        $delivery_boy = DeliveryBoy::create($input);
        $del = DeliveryBoy::where('id',$delivery_boy->id)->first();

        if (is_object($del)) {
            $this->update_status($del->id,$del->delivery_boy_name);
          
            return response()->json([
                "result" => $del,
                "message" => 'Registered Successfully',
                "status" => 1
            ]);
        } else {
            return response()->json([
                "message" => 'Sorry, something went wrong !',
                "status" => 0
            ]);
        }

    }
    
    public function update_status($id,$nme){
        $factory = (new Factory())->withDatabaseUri(env('FIREBASE_DB'));
        $database = $factory->createDatabase();
        $newPost = $database
        ->getReference('delivery_partners/'.$id)
        ->update([
            'p_id' => $id,
            'nme' => $nme,
            'o_stat' => 0,
            'o_id' => 0,
            'on_stat' => 0,
            'lat' => 0,
            'lng' => 0,
            'bearing' => 0
        ]);
    }

      public function profile_update(Request $request)
     {

        $input = $request->all();
        $validator = Validator::make($input, [
            'id' => 'required'
            
        ]);
        if ($validator->fails()) {
            return $this->sendError($validator->errors());
        }
        if($request->password){
            $options = [
                'cost' => 12,
            ];
            $input['password'] = password_hash($input["password"], PASSWORD_DEFAULT, $options);
            $input['status'] = 1;
        }else{
            unset($input['password']);
        }

        if (DeliveryBoy::where('id',$input['id'])->update($input)) {
            return response()->json([
                "result" => DeliveryBoy::select('id','email','phone_number','delivery_boy_name','email','aadhar_no','license_no','status','zone_id')->where('id',$input['id'])->first(),
                "message" => 'Success',
                "status" => 1
            ]);
        } else {
            return response()->json([
                "message" => 'Sorry, something went wrong...',
                "status" => 0
            ]);
        }

    } 

    public function get_profile(Request $request){

        $input = $request->all();
        $validator = Validator::make($input, [
            'id' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->sendError($validator->errors());
        }

        $delivery_boy = DeliveryBoy::where('id',$input['id'])->first();
        $delivery_boy->zone_name = DB::table('zones')->where('id',$delivery_boy->zone_id)->value('name');
        if(is_object($delivery_boy)){
            return response()->json([
                "result" => $delivery_boy,
                "message" => 'Success',
                "status" => 1
            ]);
        }
        else{
            return response()->json([
                "message" => 'Something went wrong',
                "status" => 0
            ]);
        }
    }

        public function profile_picture(Request $request){

        $input = $request->all();
        $validator = Validator::make($input, [
            'image' => 'required'
        ]);

        if ($validator->fails()) {
            return $this->sendError($validator->errors());
        }

        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $name = time().'.'.$image->getClientOriginalExtension();
            $destinationPath = public_path('/uploads/delivery_boys');
            $image->move($destinationPath, $name);
            return response()->json([
                "result" => 'delivery_boys/'.$name,
                "message" => 'Success',
                "status" => 1
            ]);
            
        }
    }

    public function profile_picture_update(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'id' => 'required',
            'profile_picture' => 'required'
            
        ]);

        if ($validator->fails()) {
          return $this->sendError($validator->errors());
        }
        
        if (DeliveryBoy::where('id',$input['id'])->update($input)) {
            return response()->json([
                "message" => 'Success',
                "status" => 1
            ]);
        } else {
            return response()->json([
                "message" => 'Sorry, something went wrong...',
                "status" => 0
            ]);
        }

    }

    public function forget_password(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'phone_with_code' => 'required'
        ]);

        if ($validator->fails()) {
            return $this->sendError($validator->errors());
        }

        $delivery_boy = DeliveryBoy::where('phone_with_code',$input['phone_with_code'])->first();
        

        if(is_object($delivery_boy)){
            $data['id'] = $delivery_boy->id;
            $data['otp'] = rand(1000,9999);
            if(env('MODE') != 'DEMO'){
                $message = "Hi, from ".env('APP_NAME'). "  , Your OTP code is:".$data['otp'];
                $this->sendSms($input['phone_with_code'],$message);
            }
            return response()->json([
                "result" => $data,
                "message" => 'Success',
                "status" => 1
            ]);
        }else{
            return response()->json([
                "result" => 'Please enter valid phone number',
                "status" => 0
            ]);
            
        }
    }


    public function reset_password(Request $request){

        $input = $request->all();
        $validator = Validator::make($input, [
            'id' => 'required',
            'password' => 'required'
        ]);

        if ($validator->fails()) {
            return $this->sendError($validator->errors());
        }

        $options = [
            'cost' => 12,
        ];
        $input['password'] = password_hash($input["password"], PASSWORD_DEFAULT, $options);

        if(DeliveryBoy::where('id',$input['id'])->update($input)){
            return response()->json([
                "message" => 'Success',
                "status" => 1
            ]);
        }else{
            return response()->json([
                "message" => 'Sorry something went wrong',
                "status" => 0
            ]);
        }
    }

    public function change_online_status(Request $request){
        $input = $request->all();
        $del = DeliveryBoy::where('id',$input['id'])->first();
        //$vehicle_document_status = DB::table('drivers')->where('drivers.id',$driver->id)->where('driver_vehicles.vehicle_certificate_status',16)->count();
        if($del->approved_status == '4'){
            DeliveryBoy::where('id',$input['id'])->update([ 'online_status' => $input['online_status']]);
             $factory = (new Factory())->withDatabaseUri(env('FIREBASE_DB'));
            $database = $factory->createDatabase();
            $newPost = $database
            ->getReference('delivery_partners/'.$input['id'])
            ->update([
                'on_stat' => (int) $input['online_status']
            ]);
            return response()->json([
                "message" => 'Success',
                "status" => 1
            ]);
        }else{
                return response()->json([
                    "result" => $del,
                    "message" => 'Your profile still not approved',
                    "status" => 0
                ]);
            }
    }
    
    public function delivery_boy_earning(Request $request){
        
        $input = $request->all();
        $validator = Validator::make($input, [
            'id' => 'required'
        ]);
        if ($validator->fails()) {
            return $this->sendError($validator->errors());
        }
        
        $data['total_earnings'] = DeliveryBoyEarning::where('delivery_boy_id',$input['id'])->get()->sum("amount");
        $data['today_earnings'] = DeliveryBoyEarning::where('delivery_boy_id',$input['id'])->whereDay('created_at', now()->day)->sum("amount");
        $data['earnings'] = DeliveryBoyEarning::where('delivery_boy_id',$input['id'])->get();
        
        if($data){
            return response()->json([
                "result" => $data,
                "count" => count($data),
                "message" => 'Success',
                "status" => 1
            ]);
        }else{
            return response()->json([
                "message" => 'Something went wrong',
                "status" => 0
            ]);
        }

    }

    public function delivery_boy_wallet_histories(Request $request){
        
        $input = $request->all();
        $validator = Validator::make($input, [
            'id' => 'required'
        ]);
        if ($validator->fails()) {
            return $this->sendError($validator->errors());
        }
        
        $now = Carbon::now();
        //echo $now->endOfWeek();exit;
        $data['wallet_amount'] = DeliveryBoy::where('id',$input['id'])->value('wallet');
        $data['this_month_earnings'] = DeliveryBoyEarning::where('delivery_boy_id',$input['id'])->whereBetween('created_at', [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()])->sum('amount');
        
        $data['this_week_earnings'] = DeliveryBoyEarning::where('delivery_boy_id',$input['id'])->whereBetween('created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()])->sum('amount');
        
        $data['wallets'] = DeliveryBoyWalletHistory::where('delivery_boy_id',$input['id'])->orderBy('created_at', 'desc')->get();
        
        
        if($data){
            return response()->json([
                "result" => $data,
                "message" => 'Success',
                "status" => 1
            ]);
        }else{
            return response()->json([
                "message" => 'Something went wrong',
                "status" => 0
            ]);
        }

    }
    
    public function dashborad(Request $request){
        $input = $request->all();
        $validator = Validator::make($input, [
            'delivery_boy_id' => 'required'
        ]);

        if ($validator->fails()) {
            return $this->sendError($validator->errors());
        }
        
        $picked_orders = DB::table('orders')
            ->leftJoin('order_statuses', 'order_statuses.id', '=', 'orders.status')
            ->select('orders.*','order_statuses.status_for_deliveryboy','order_statuses.slug')
            ->where('orders.delivered_by',$input['delivery_boy_id'])
            ->where('order_statuses.slug','order_picked')
            ->get()->count();
        $completed_orders = DB::table('orders')
            ->leftJoin('order_statuses', 'order_statuses.id', '=', 'orders.status')
            ->select('orders.*','order_statuses.status_for_deliveryboy','order_statuses.slug')
            ->where('orders.delivered_by',$input['delivery_boy_id'])
            ->where('order_statuses.slug','delivered')
            ->get()->count();
        $pending_orders = DB::table('orders')
            ->leftJoin('order_statuses', 'order_statuses.id', '=', 'orders.status')
            ->select('orders.*','order_statuses.status_for_deliveryboy','order_statuses.slug')
            ->where('orders.delivered_by',$input['delivery_boy_id'])
            ->whereIn('order_statuses.slug',['ready_to_dispatch','reached_restaurant','order_picked','at_point'])
            ->get()->count();

        $data['picked_up'] = $picked_orders;
        $data['completed'] = $completed_orders;
        $data['pending'] = $pending_orders;
        
        $routes = DB::table('orders')->leftjoin('customer_addresses','customer_addresses.id','=','orders.address_id')->where('orders.delivered_by',$input['delivery_boy_id'])->whereIn('orders.status',[4,5,6,7])->select('customer_addresses.lat','customer_addresses.lng')->distinct()->get();
        
        $rt = "";
        foreach($routes as $key => $value){
            $rt = $rt.$value->lat.','.$value->lng.'/';
        }
        $data['routes'] = $rt;
        
        if ($data) {
            return response()->json([
                "result" => $data,
                "message" => 'Success',
                "status" => 1
            ]);
        } else {
            return response()->json([
                "message" => 'Sorry, something went wrong !',
                "status" => 0
            ]);
        }
        
    }
    
    public function get_pending_orders(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'delivery_boy_id' => 'required'
        ]);

        if ($validator->fails()) {
            return $this->sendError($validator->errors());
        }
    
        $orders = DB::table('orders')
            ->leftJoin('customer_addresses', 'customer_addresses.id', '=', 'orders.address_id')
            ->leftJoin('customers', 'customers.id', '=', 'orders.customer_id')
            ->leftJoin('restaurants', 'restaurants.id', '=', 'orders.restaurant_id')
            ->leftJoin('order_statuses', 'order_statuses.id', '=', 'orders.status')
            ->leftJoin('payment_modes', 'payment_modes.id', '=', 'orders.payment_mode')
            ->select('orders.*','order_statuses.status_for_restaurant','order_statuses.status','order_statuses.slug','payment_modes.payment_name','orders.created_at','orders.updated_at', 'customers.phone_number', 'customers.customer_name','customers.profile_picture','customer_addresses.address','restaurants.google_address','restaurants.manual_address','restaurants.lat','restaurants.lng','restaurants.zip_code','restaurants.restaurant_name','restaurants.restaurant_phone_number')
            ->where('orders.delivered_by',$input['delivery_boy_id'])
            ->whereIn('order_statuses.slug',['ready_to_dispatch','reached_restaurant','order_picked','at_point'])
            ->orderBy('orders.created_at', 'desc')
            ->get();
            
        foreach($orders as $key => $value){
                $orders[$key]->item_list = DB::table('order_items')
                ->leftJoin('items', 'items.id', '=', 'order_items.item_id')
                ->select('order_items.*')
                ->where('order_id',$value->id)
                ->get();
        }
            
        if ($orders) {
            return response()->json([
                "result" => $orders,
                "count" => count($orders),
                "message" => 'Success',
                "status" => 1
            ]);
        } else {
            return response()->json([
                "message" => 'Sorry, something went wrong !',
                "status" => 0
            ]);
        }
    }
    
    public function get_orders(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'delivery_boy_id' => 'required'
        ]);

        if ($validator->fails()) {
            return $this->sendError($validator->errors());
        }
        $orders = DB::table('orders')
            ->leftJoin('customer_addresses', 'customer_addresses.id', '=', 'orders.address_id')
            ->leftJoin('customers', 'customers.id', '=', 'orders.customer_id')
            ->leftJoin('restaurants', 'restaurants.id', '=', 'orders.restaurant_id')
            ->leftJoin('order_statuses', 'order_statuses.id', '=', 'orders.status')
            ->leftJoin('payment_modes', 'payment_modes.id', '=', 'orders.payment_mode')
            ->select('orders.*','order_statuses.status_for_restaurant','order_statuses.status','order_statuses.slug','payment_modes.payment_name','orders.created_at','orders.updated_at', 'customers.phone_number', 'customers.customer_name','customers.profile_picture','customer_addresses.address','customer_addresses.lat as cus_lat','customer_addresses.lng as cus_lng','restaurants.google_address','restaurants.manual_address','restaurants.lat as res_lat','restaurants.lng as res_lng','restaurants.zip_code','restaurants.restaurant_name','restaurants.restaurant_phone_number')
            ->where('orders.delivered_by',$input['delivery_boy_id'])
            ->orderBy('orders.created_at', 'desc')
            ->get();
            
        foreach($orders as $key => $value){
                $orders[$key]->item_list = DB::table('order_items')
                ->leftJoin('items', 'items.id', '=', 'order_items.item_id')
                ->select('order_items.*')
                ->where('order_id',$value->id)
                ->get();
        }
        
        if ($orders) {
            return response()->json([
                "result" => $orders,
                "count" => count($orders),
                "message" => 'Success',
                "status" => 1
            ]);
        } else {
            return response()->json([
                "message" => 'Sorry, something went wrong !',
                "status" => 0
            ]);
        }
    }
    
    public function get_deliveryboy_order_detail(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'order_id' => 'required'
        ]);

        if ($validator->fails()) {
            return $this->sendError($validator->errors());
        }
        $orders = DB::table('orders')
            ->leftJoin('customer_addresses', 'customer_addresses.id', '=', 'orders.address_id')
            ->leftJoin('customers', 'customers.id', '=', 'orders.customer_id')
            ->leftJoin('restaurants', 'restaurants.id', '=', 'orders.restaurant_id')
            ->leftJoin('delivery_boys', 'delivery_boys.id', '=', 'orders.delivered_by')
            ->leftJoin('promo_codes', 'promo_codes.id', '=', 'orders.promo_id')
            ->leftJoin('order_statuses', 'order_statuses.id', '=', 'orders.status')
            ->leftJoin('payment_modes', 'payment_modes.id', '=', 'orders.payment_mode')
            ->select('orders.*','order_statuses.status_for_deliveryboy','order_statuses.status','order_statuses.slug','payment_modes.payment_name','orders.created_at','orders.updated_at', 'restaurants.restaurant_phone_number','restaurants.restaurant_image', 'restaurants.licence_no', 'restaurants.restaurant_name','restaurants.manual_address','restaurants.google_address','restaurants.lat as res_lat','restaurants.lng as res_lng','restaurants.is_open','restaurants.contact_person_name','restaurants.overall_rating','restaurants.number_of_rating','customer_addresses.address','customer_addresses.lat as cus_lat','customer_addresses.lng as cus_lng','customer_addresses.landmark','customers.customer_name','customers.phone_with_code','customers.profile_picture','promo_codes.promo_name')
            ->where('orders.id',$input['order_id'])
            ->first();
        if($orders->delivered_by){
            $partner_details = DB::table('delivery_boys')->where('id',$orders->delivered_by)->first();
            $partner_order_count = DB::table('orders')->where('id',$orders->delivered_by)->count();
            if(is_object($partner_details)){
                $orders->delivery_boy_name = $partner_details->delivery_boy_name;
                $orders->delivery_boy_image = $partner_details->profile_picture;
                $orders->delivery_boy_phone_number = $partner_details->phone_number;
                $orders->delivery_boy_order_count = $partner_order_count;
                
            }
        }
        $new_status_id = DB::table('order_statuses')->where('status',$orders->status)->value('id');
        $new_status = $new_status_id + 1;
        $new_status_name = DB::table('order_statuses')->where('id',$new_status)->value('status');
        $new_status_for_deliveryboy = DB::table('order_statuses')->where('id',$new_status)->value('status_for_deliveryboy');
        $new_slug = DB::table('order_statuses')->where('id',$new_status)->value('slug');
        $orders->new_status = $new_status_name;
        $orders->new_slug = $new_slug;
        $orders->new_status_for_deliveryboy = $new_status_for_deliveryboy;
        $orders->item_list = DB::table('order_items')
                ->leftJoin('items', 'items.id', '=', 'order_items.item_id')
                ->select('order_items.*')
                ->where('order_id',$orders->id)
                ->get();
        
        if ($orders) {
            return response()->json([
                "result" => $orders,
                "message" => 'Success',
                "status" => 1
            ]);
        } else {
            return response()->json([
                "message" => 'Sorry, something went wrong !',
                "status" => 0
            ]);
        }
    }
    
    public function withdrawal_request(Request $request){
        $input = $request->all();
        $validator = Validator::make($input, [
            'delivery_boy_id' => 'required',
            'amount' => 'required'
            
        ]);
        if ($validator->fails()) {
            return $this->sendError($validator->errors());
        }
        $input['status'] = 6;
         $input['message'] = "Your withdrawal request successfully submitted";
        $del_wallet = DeliveryBoy::where('id',$input['delivery_boy_id'])->value('wallet');
        $new_wallet = $del_wallet-$input['amount'];
        $input['existing_wallet'] = $del_wallet;
        if($input['amount'] <= $del_wallet ){
          $delivery_boy = DeliveryBoyWithdrawal::create($input);  
          
        $status = DeliveryBoyWithdrawal::where('delivery_boy_id',$input['delivery_boy_id'])->where('id',$delivery_boy->id)->value('status');
            if($status==6){
                 DeliveryBoy::where('id',$input['delivery_boy_id'])->update([ 'wallet' => $new_wallet]);
            }
        if (is_object($delivery_boy)) {
            return response()->json([
                "result" => $delivery_boy,
                "message" => 'success',
                "status" => 1
            ]);
        } else {
            return response()->json([
                "message" => 'Sorry, something went wrong !',
                "status" => 0
            ]);
        }
        }else{
             return response()->json([
                "message" => 'Sorry, something went wrong !',
                "status" => 0
            ]);
        }
        
        
    }
    
    public function withdrawal_history(Request $request){
        
        $input = $request->all();
        $validator = Validator::make($input, [
            'id' => 'required'
        ]);
        if ($validator->fails()) {
            return $this->sendError($validator->errors());
        }
        
        $data['wallet_amount'] = DeliveryBoy::where('id',$input['id'])->value('wallet');
        
        $data['withdraw'] =  DB::table('delivery_boy_withdrawals')
                ->leftjoin('statuses', 'statuses.id', '=', 'delivery_boy_withdrawals.status')
                ->select('delivery_boy_withdrawals.*', 'statuses.status_name')
                ->where('delivery_boy_withdrawals.delivery_boy_id',$input['id'])
                ->orderBy('delivery_boy_withdrawals.created_at', 'desc')
                ->get();
        
        if($data){
            return response()->json([
                "result" => $data,
                "count" => count($data),
                "message" => 'Success',
                "status" => 1
            ]);
        }else{
            return response()->json([
                "message" => 'Something went wrong',
                "status" => 0
            ]);
        }
    }
    
    public function delivery_boy_earning_month_wise(Request $request){
        
        $input = $request->all();
        $validator = Validator::make($input, [
            'id' => 'required'
        ]);
        if ($validator->fails()) {
            return $this->sendError($validator->errors());
        }
        
        $data = DB::table('delivery_boy_earnings')
                ->selectRaw('
                    SUM(amount) AS earnings,
                    MONTH(created_at) as month
                ')->where('delivery_boy_id',$input['id'])
                ->groupByRaw('MONTH(created_at)')
                ->get()->toArray();
                
        $month = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];
        $x = [];
        $y = [];
        foreach($data as $key => $value){
            $x[$key] = $month[$value->month -1];
            $y[$key] = (int) $value->earnings;
        }
        
        
        return response()->json([
            "result" => ['x' => $x, 'y' => implode(",",$y)],
            "message" => 'Success',
            "status" => 1
        ]);
    }
    
    public function get_documents(Request $request){
        $input = $request->all();
        $validator = Validator::make($input, [
            'partner_id' => 'required',
        ]);
        if ($validator->fails()) {
            return $this->sendError($validator->errors());
        }
        $data = [];
        $driver_details = DeliveryBoy::where('id',$input['partner_id'])->first();
        $data[0]['path'] = $driver_details->aadhar;
        $data[0]['document_name'] = 'aadhar';
        $data[0]['status'] = $driver_details->aadhar_status;
        $data[1]['path'] = $driver_details->passbook;
        $data[1]['document_name'] = 'passbook';
        $data[1]['status'] = $driver_details->passbook_status;
        $data[0]['status_name'] = Status::where('id',$driver_details->aadhar_status)->value('status_name');
        $data[1]['status_name'] = Status::where('id',$driver_details->passbook_status)->value('status_name');
        $result['documents'] = $data;
        return response()->json([
            "result" => $result,
            "message" => 'Success',
            "status" => 1
        ]);
    }
    
    public function update_document(Request $request){
        $input = $request->all();
        $validator = Validator::make($input, [
            'table' => 'required',
            'update_field' => 'required',
            'update_value' => 'required',
            'find_field' => 'required',
            'find_value' => 'required',
            'status_field' => 'required'
        ]);
        if ($validator->fails()) {
            return $this->sendError($validator->errors());
        }
        DB::table($input['table'])->where($input['find_field'],'=',$input['find_value'])->update([
            $input['update_field'] => $input['update_value'], $input['status_field'] => 3
        ]);
        return response()->json([
            "message" => 'Success',
            "status" => 1
        ]);
    }
    
    public function image_upload(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'image' => 'required',
            'upload_path' => 'required'
        ]);
    
        if ($validator->fails()) {
            return $this->sendError($validator->errors());
        }
    
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $name = time().'.'.$image->getClientOriginalExtension();
            $destinationPath = public_path('/uploads/'.$input['upload_path']);
            $image->move($destinationPath, $name);
            return response()->json([
                "result" => $input['upload_path'].'/'.$name,
                "message" => 'Success',
                "status" => 1
            ]);
        }else{
            return response()->json([
                "message" => 'Sorry something went wrong',
                "status" => 0
            ]);
        }
    }
    
    public function get_zones(){
        $data = Zone::get();
        return response()->json([
            "result" => $data,
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


 