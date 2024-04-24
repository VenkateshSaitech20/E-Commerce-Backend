<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Faq;
use App\Models\FaqCategory;
use Illuminate\Support\Facades\DB;
use Validator;


class FaqController extends Controller
{

	/**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function customer_faq()
    {

        $data = [];
        $faq_category = FaqCategory::where('faq_type',1)->get();

        foreach($faq_category as $key => $value){
            $faq_category[$key]['data'] = DB::table('faqs')
            ->leftJoin('faq_categories', 'faq_categories.id', '=', 'faqs.faq_category_id')
            ->select('faqs.*', 'faq_categories.category_name')
            ->where('faqs.faq_category_id', $value->id)
            ->get();
            
        }
        $data = $faq_category;
        return response()->json([
            "result" => $data,
            "count" => count($data),
            "message" => 'Success',
            "status" => 1
        ]);
    }


     public function restaurant_faq(Request $request)
    {	
    	
        $data = [];
        $faq_category = FaqCategory::where('faq_type',2)->get();

        foreach($faq_category as $key => $value){
            $faq_category[$key]['data'] = DB::table('faqs')
            ->leftJoin('faq_categories', 'faq_categories.id', '=', 'faqs.faq_category_id')
            ->select('faqs.*', 'faq_categories.category_name')
            ->where('faqs.faq_category_id', $value->id)
            ->get();
            
        }
        $data = $faq_category;
        
        return response()->json([
            "result" => $data,
            "count" => count($data),
            "message" => 'Success',
            "status" => 1
        ]);
    }


    public function delivery_boy_faq(Request $request)
    {	
    	 
        $data = [];
        $faq_category = FaqCategory::where('faq_type',3)->get();

        foreach($faq_category as $key => $value){
            $faq_category[$key]['data'] = DB::table('faqs')
            ->leftJoin('faq_categories', 'faq_categories.id', '=', 'faqs.faq_category_id')
            ->select('faqs.*', 'faq_categories.category_name')
            ->where('faqs.faq_category_id', $value->id)
            ->get();
            
        }
        $data = $faq_category;

        return response()->json([
            "result" => $data,
            "count" => count($data),
            "message" => 'Success',
            "status" => 1
        ]);
    }

    public function get_faq_category()
    {
        $data = Faqcategory::where('status',1)->get();
        
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


