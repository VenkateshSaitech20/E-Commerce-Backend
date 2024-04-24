<?php

namespace App\Admin\Controllers;

use App\Models\PromoCode;
use App\Models\Status;
use App\Models\PromoType;
use App\Models\Restaurant;
use App\Models\Customer;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Admin;
class PromoCodeController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Promo Code';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new PromoCode());
        if(!Admin::user()->isAdministrator()){
            $grid->model()->where('restaurant_id', Restaurant::where('admin_user_id',Admin::user()->id)->value('id'));
        }
        $grid->column('id', __('Id'));
        $grid->column('customer_id', __('Customer'))->display(function($customer_id){
            if($customer_id == 0){
                return "NILL";
            }else{
                $customer_id = Customer::where('id',$customer_id)->value('phone_number');
            return $customer_id;
            }
        });
        $grid->column('restaurant_id', __('Restaurant'))->display(function($restaurant_id){
            if($restaurant_id == 0){
                return "NILL";
            }else{
                $restaurant_id = Restaurant::where('id',$restaurant_id)->value('restaurant_name');
                return $restaurant_id;
            }
        });
        $grid->column('promo_name', __('Promo Name'));
        $grid->column('promo_code', __('Promo Code'));
        $grid->column('min_purchase_price', __('Minimum Purchase Price'));
        $grid->column('max_discount_value', __('Maximum Discount Value'));
        $grid->column('redemptions', __('Redemptions'));
        $grid->column('promo_type', __('Promo Type'))->display(function($promo_id){
            $promo_id = PromoType::where('id',$promo_id)->value('type_name');
            return $promo_id;
        });
        $grid->column('discount', __('Discount'));
        $grid->column('status', __('Status'))->display(function($status){
            $status_name = Status::where('id',$status)->value('status_name');
            if ($status == 1) {
                return "<span class='label label-success'>$status_name</span>";
            } if ($status == 2) {
                return "<span class='label label-danger'>$status_name</span>";
            } 
        });

        
        $grid->disableExport();
        if(env('MODE') == 'DEMO'){
            $grid->disableCreateButton();
            $grid->disableActions();
        }else{
            $grid->actions(function ($actions) {
                $actions->disableView();
            });
        }

        $grid->filter(function ($filter) {
            //Get All status
        $statuses = Status::pluck('status_name', 'id');
        $promo_types = PromoType::pluck('type_name', 'id');

        $filter->like('promo_name', __('Promo name'));
        $filter->like('promo_code', __('Promo code'));
        $filter->like('description', __('Description'));
        $filter->equal('promo_type', __('Promo type'))->select($promo_types);

        $filter->like('discount', __('Discount'));
        $filter->equal('status', __('Status'))->select($statuses);


        });







        return $grid;
    }

    /**
     * Make a show builder.
     *
     * @param mixed $id
     * @return Show
     */
    protected function detail($id)
    {
        $show = new Show(PromoCode::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('promo_name', __('Promo name'));
        $show->field('promo_code', __('Promo code'));
        $show->field('description', __('Description'));
        $show->field('promo_type', __('Promo type'));
        $show->field('discount', __('Discount'));
        $show->field('status', __('Status'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new PromoCode());
        
        $statuses = Status::pluck('status_name', 'id');
        $promo_types = PromoType::pluck('type_name', 'id');
        $restaurants = Restaurant::pluck('restaurant_name', 'id');
        $customers = Customer::pluck('phone_number', 'id');
        $restaurant_id = Restaurant::where('admin_user_id',Admin::user()->id)->value('id');
        
        
        if(!Admin::user()->isAdministrator()){
            $form->hidden('restaurant_id')->value($restaurant_id);
            $form->hidden('customer_id')->value(0);
        }else{
            $form->select('restaurant_id', __('Restaurant'))->options($restaurants)->rules(function ($form) {
                return 'required';
            });
            $form->select('customer_id', __('Customer'))->options($customers);
        }
        $form->text('promo_name', __('Promo Name'))->rules(function ($form) {
            return 'required|max:250';
        });

        $form->text('promo_code', __('Promo Code'))->rules(function ($form) {
            return 'required|max:250';
        });

        $form->textarea('description', __('Description'))->rules('required');
        $form->textarea('long_description', __('Long Description'))->rules('required');
        $form->select('promo_type', __('Promo Type'))->options($promo_types)->default(1)->rules(function ($form) {
            return 'required';
        });
        $form->decimal('discount', __('Discount'))->rules('required');
        $form->text('min_purchase_price', __('Minimum Purchase Price'))->rules('required')->help('Minimum order total');
        $form->text('max_discount_value', __('Maximum Discount Value'))->rules('required')->help('Maximum discount amount for this promo');
        $form->text('redemptions', __('Redemptions'))->rules('required|numeric');
        $form->select('status', __('Status'))->options(Status::where('slug','general')->pluck('status_name','id'))->rules(function ($form) {
            return 'required';
        });
        $form->saving(function ($form) {
           if(!$form->customer_id){
              $form->customer_id = 0;
           }
           if(!$form->restaurant_id){
              $form->restaurant_id = 0;
           }
        });
        $form->tools(function (Form\Tools $tools) {
            $tools->disableDelete(); 
            $tools->disableView();
        });
        $form->footer(function ($footer) {
            $footer->disableViewCheck();
            $footer->disableEditingCheck();
            $footer->disableCreatingCheck();
        });



        return $form;
    }
}
