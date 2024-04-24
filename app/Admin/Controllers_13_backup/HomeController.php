<?php

namespace App\Admin\Controllers;

use App\Models\Customer;
use App\Models\Order;
use App\Models\Restaurant;
use App\Models\DeliveryBoy;
use App\Models\RestaurantEarning;
use Carbon\Carbon;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\Dashboard;
use Encore\Admin\Layout\Column;
use Encore\Admin\Layout\Content;
use Encore\Admin\Layout\Row;
use Encore\Admin\Facades\Admin;
use Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Zone;

class HomeController extends Controller
{
    public function index(Content $content)
    {
        return Admin::content(function (Content $content) {

            $content->header('Dashboard');
            $data = array();
            $current_year = date("Y");
            
            if(Admin::user()->isRole('Restaurant')){
                $id = Auth::user()->id;
                $rest_id = Restaurant::where('admin_user_id',$id)->value('id');
                //echo($rest_id);exit;
                $data['total_orders'] = Order::where('restaurant_id',$rest_id)->count();
                $data['completed_orders'] = Order::where('restaurant_id',$rest_id)->where('status','=',8)->count();
                $data['pending_orders'] = Order::where('restaurant_id',$rest_id)->where('status','!=',8)->count();
                $data['earnings'] = RestaurantEarning::where('restaurant_id',$rest_id)->sum('amount');
            
            $earnings = RestaurantEarning::where('restaurant_id',$rest_id)->select('amount', 'created_at')
                ->get()
                ->groupBy(function ($val) {
                    return Carbon::parse($val->created_at)->format('M');
                });
                //echo($earnings);exit;
            $orders = Order::select('id', 'created_at')
                ->get()
                ->groupBy(function ($val) {
                    return Carbon::parse($val->created_at)->format('M');
                });
            $month = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];
            $temp = [];
            foreach ($earnings as $c) {
                $temp[Carbon::parse($c[0]->created_at)->format('M')] = count($c);
            }
            //echo($c[amount]);exit;
            $growth = [];
            foreach ($month as $m) {
                if (isset($temp[$m])) {
                    $growth[] = $temp[$m];
                } else {
                    $growth[] = 0;
                }

            }
            $temp_orders = [];
            foreach ($orders as $o) {
                $temp_orders[Carbon::parse($o[0]->created_at)->format('M')] = count($o);
            }
            $growth_orders = [];
            foreach ($month as $m) {
                if (isset($temp_orders[$m])) {
                    $growth_orders[] = $temp_orders[$m];
                } else {
                    $growth_orders[] = 0;
                }

            }
            $data['earnings_chart'] = implode(",", $growth);
            $data['orders_chart'] = implode(",", $growth_orders);
            
            $content->body(view('admin.restaurant_dashboard', $data));
                
            }else{
                 $data['customers'] = Customer::where('status','!=',0)->count();
                $data['total_orders'] = Order::count();
                $data['completed_orders'] = Order::where('status','=',8)->count();
                $data['pending_orders'] = Order::where('status','!=',8)->count();
                $data['delivery_boys'] = DeliveryBoy::where('status','!=',0)->count();
            
            $customers = Customer::select('id', 'created_at')
                ->get()
                ->groupBy(function ($val) {
                    return Carbon::parse($val->created_at)->format('M');
                });
            $orders = Order::select('id', 'created_at')
                ->get()
                ->groupBy(function ($val) {
                    return Carbon::parse($val->created_at)->format('M');
                });
            $month = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];
            $temp = [];
            foreach ($customers as $c) {
                $temp[Carbon::parse($c[0]->created_at)->format('M')] = count($c);
            }
            $growth = [];
            foreach ($month as $m) {
                if (isset($temp[$m])) {
                    $growth[] = $temp[$m];
                } else {
                    $growth[] = 0;
                }

            }
            $temp_orders = [];
            foreach ($orders as $o) {
                $temp_orders[Carbon::parse($o[0]->created_at)->format('M')] = count($o);
            }
            $growth_orders = [];
            foreach ($month as $m) {
                if (isset($temp_orders[$m])) {
                    $growth_orders[] = $temp_orders[$m];
                } else {
                    $growth_orders[] = 0;
                }

            }
            $data['customers_chart'] = implode(",", $growth);
            $data['orders_chart'] = implode(",", $growth_orders);
            
                $content->body(view('admin.dashboard', $data));
            }
        });
       

    }
    
    public function create_zone($id){
        return Admin::content(function (Content $content) use ($id) {
            $content->header('Draw Zone');
            $data['id'] = $id;
            $row = DB::table('customer_app_settings')->first();
            $data['capital_lat'] = $row->capital_lat;
            $data['capital_lng'] = $row->capital_lng;
            $content->body(view('zones.create_zones', $data));
        });
    }

    public function view_zone($id){
        return Admin::content(function (Content $content) use ($id) {
            $content->header('Draw Zone');
            $data['id'] = $id;
            $row = DB::table('customer_app_settings')->first();
            $data['capital_lat'] = $row->capital_lat;
            $data['capital_lng'] = $row->capital_lng;
            $polygon = Zone::where('id',$id)->value('polygon');
            $polygon = explode(";",$polygon);
            $data['polygon'] = [];
            foreach($polygon as $key => $value){
               $value = explode(",",$value);
               if(@$value[1]){
                $data['polygon'][$key]['lat'] = floatval($value[0]);
                $data['polygon'][$key]['lng'] = floatval($value[1]);
               }
            }

            $data['polygon'] = json_encode($data['polygon'],TRUE);
            $content->body(view('zones.view_service_zones', $data));
        });
    }
    
     public function live_chat(){
        
        return Admin::content(function (Content $content) {
            $data['users'] = Customer::where('status', 1)->orderBy('id', 'DESC')->get();
            $data['messages'] = null;
            $content->header('Customers Chat');
            $content->body(view('admin.chat', $data));
        });
    } 
}
