<?php

namespace App\Admin\Controllers;

use App\Models\Item;
use App\Models\SubCategory;
use App\Models\Restaurant;
use App\Models\RestaurantCategory;
use App\Models\Tag;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Admin;

class ItemController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Products';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Item());
        
        if(!Admin::user()->isAdministrator()){
            $grid->model()->where('restaurant_id', Restaurant::where('admin_user_id',Admin::user()->id)->value('id'));
        }
        $grid->column('id', __('Id'));
        $grid->column('restaurant_id', __('Kirana Store'))->display(function($res_id){
            $res_name = Restaurant::where('id',$res_id)->value('restaurant_name');
            return $res_name;
        });
        $grid->column('sub_category_id', __('Sub Category '))->display(function($sub_category_id){
            return SubCategory::where('id',$sub_category_id)->value('sub_category_name');
        });
        $grid->column('item_name', __('Product Name'));
        $grid->column('base_price', __('Base Price'));
        $grid->column('serves', __('Serves'));
        $grid->column('preparation_time', __('Preparation Time'));
        $grid->column('item_tag', __('Item tag'))->display(function($tag_name){
            $tag_name = Tag::where('id',$tag_name)->value('tag_name');
            return $tag_name;
        });
        $grid->column('is_recommand_tag', __('Is recommand tag'))->display(function($type){
            if ($type == 0) {
                return "<span class='label label-success'>No</span>";
            } if ($type == 1) {
                return "<span class='label label-info'>Yes</span>";
             } 
        });
        $grid->column('in_stock', __('In Stock'))->display(function($type){
            if ($type == 0) {
                return "<span class='label label-success'>No</span>";
            } if ($type == 1) {
                return "<span class='label label-info'>Yes</span>";
             } 
        });
        if(Admin::user()->isAdministrator()){
            $grid->column('is_approved', __('Is Approved'))->display(function($type){
                if ($type == 0) {
                    return "<span class='label label-success'>No</span>";
                } if ($type == 1) {
                    return "<span class='label label-info'>Yes</span>";
                 } 
            });
        }
       
      

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
        $sub_categories = SubCategory::pluck('sub_category_name', 'id');
        $restaurants = Restaurant::pluck('restaurant_name', 'id');
        
        if(!Admin::user()->isAdministrator()){
            $filter->equal('in_stock', __('In stock'))->select(['0' => 'No', '1'=> 'Yes'])->default('m');
            $filter->equal('is_approved', __('Is Approved'))->select(['0' => 'No', '1'=> 'Yes'])->default('m');
            $filter->like('item_name', __('Item Name'));
        }else{
            $filter->equal('restaurant_id', __('Restaurant id'))->select($restaurants);
            $filter->equal('in_stock', __('In stock'))->select(['0' => 'No', '1'=> 'yes'])->default('m');
            $filter->equal('is_approved', __('Is Approved'))->select(['0' => 'No', '1'=> 'Yes'])->default('m');
            $filter->like('item_name', __('Item Name'));
        }

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
        $show = new Show(Item::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('category_id', __('Category id'));
        $show->field('sub_category_id', __('Sub category id'));
        $show->field('item_name', __('Item name'));
        $show->field('item_description', __('Item description'));
        $show->field('food_type', __('Food type'));
        $show->field('base_price', __('Base price'));
        $show->field('serves', __('Serves'));
        $show->field('item_tag', __('Item tag'));
        $show->field('is_recommand_tag', __('Is recommand tag'));
        $show->field('in_stock', __('In stock'));
        $show->field('item_image', __('Item image'));
        $show->field('is_approved', __('Is approved'));
        $show->field('restaurant_id', __('Restaurant id'));
        $show->field('cuisine_id', __('Cuisine id'));
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
        $form = new Form(new Item());
        $form->tab('Item Details', function ($form) {
            $sub_categories = SubCategory::pluck('sub_category_name', 'id');
            $restaurants = Restaurant::pluck('restaurant_name', 'id');
            $tag_name = Tag::pluck('tag_name', 'id');
            $restaurant_id = Restaurant::where('admin_user_id',Admin::user()->id)->value('id');
            
            if(!Admin::user()->isAdministrator()){
                $form->hidden('restaurant_id')->value($restaurant_id);
                $form->select('sub_category_id', __('Sub Category'))->options($sub_categories);
            }else{
                $form->select('restaurant_id', __('Restaurant'))->options($restaurants)->rules('required');
                $form->select('sub_category_id', __('Sub Category'))->options($sub_categories)->rules('required');
            }
    
            $form->text('item_name', __('Item Name'))->rules(function ($form) {
                return 'required|max:150';
            });
            $form->textarea('item_description', __('Item Description'))->rules('required');
            $form->decimal('base_price', __('Base Price'))->rules('required');
            $form->text('serves', __('Serves'))->rules(function ($form) {
                return 'required|max:150';
            });
    
            $form->select('item_tag', __('Item Tag'))->options($tag_name)->rules(function ($form) {
                return ;
            });
            $form->image('item_image', __('Item Image'))->uniqueName()->move('items/')->rules('required');
            $form->number('preparation_time', __('Preparation Time'))->rules('required');
            $form->select('is_recommand_tag', __('Is Recommanded'))->options(['0' => 'No', '1'=> 'Yes'])->default('1')->rules('required');
            $form->select('in_stock', __('In Stock'))->rules('required')->options(['0' => 'No', '1'=> 'Yes'])->default('1');
            if(Admin::user()->isAdministrator()){
               $form->select('is_approved', __('Is Approved'))->rules('required')->options(['0' => 'No', '1'=> 'Yes'])->default('1');
            }
        })->tab('Item Options', function ($form) {
             $form->hasMany('item_options', function (Form\NestedForm $form) {
                $form->text('option_name', __('Option Name'))->rules(function ($form) {
                    return 'required|max:150';
                });
                $form->decimal('price', __('Price'))->rules('required');
            });
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
