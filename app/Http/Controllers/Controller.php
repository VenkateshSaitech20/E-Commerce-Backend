<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Twilio\Rest\Client;
use Mail;
use Illuminate\Routing\Controller as BaseController;
use App\Models\Zone;
class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;


    public function sendSms($phone_number,$message)
    {
        $sid    = env( 'TWILIO_SID' );
        $token  = env( 'TWILIO_TOKEN' );
        $client = new Client( $sid, $token );
        $client->messages->create($phone_number,[ 'from' => env( 'TWILIO_FROM' ),'body' => $message,]);
        return true;
    }
   
    public function send_order_mail($mail_header,$subject,$to_mail,$template){
    	Mail::send($template, $mail_header, function ($message)
		 use ($subject,$to_mail) {
			$message->from(env('MAIL_USERNAME'), env('APP_NAME'));
			$message->subject($subject);
			$message->to($to_mail);
		});
    }
    
    public function find_in_polygon($longitude_x,$latitude_y){
        $id = 1;
        $all_locations = Zone::all();
        foreach($all_locations as $key => $value){
            if($value->polygon){
                $polygon = explode(";",$value->polygon);
                $vertices_x = [];
                $vertices_y = [];
                foreach($polygon as $key => $value1){
                   $value1 = explode(",",$value1);
                   if(@$value1[1]){
                    $vertices_x[$key] = floatval($value1[0]);
                    $vertices_y[$key] = floatval($value1[1]);
                   }
                }

                $points_polygon = count($vertices_x); 

                if ($this->is_in_polygon($points_polygon, $vertices_x, $vertices_y, $longitude_x, $latitude_y)){
                  return $value->id;
                } 
            }
            
        }
        return 0;
    }

    public function is_in_polygon($points_polygon, $vertices_x, $vertices_y, $longitude_x, $latitude_y)
    {
      $i = $j = $c = 0;
      for ($i = 0, $j = $points_polygon-1 ; $i < $points_polygon; $j = $i++) {
        if ( (($vertices_y[$i] > $latitude_y != ($vertices_y[$j] > $latitude_y)) &&
        ($longitude_x < ($vertices_x[$j] - $vertices_x[$i]) * ($latitude_y - $vertices_y[$i]) / ($vertices_y[$j] - $vertices_y[$i]) + $vertices_x[$i]) ) ) 
            $c = !$c;
      }

      return $c;
    }
}
