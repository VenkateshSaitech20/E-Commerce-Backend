<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeliveryBoy extends Model
{
    use HasFactory;
    protected $fillable = [
        'id', 'status', 'online_status','wallet','order_id','order_status','delivery_boy_name','email','phone_number','phone_with_code','profile_picture','license_no','aadhar_no','password'
,'aadhar','aadhar_status','passbook','passbook_status','bank_name','account_name','account_number','ifsc','zone_id'    ];
}
