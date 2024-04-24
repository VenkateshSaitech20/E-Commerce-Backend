<?php

namespace App\Admin\Controllers;

use App\Models\OrderItem;
use App\Models\Order;
use App\Models\Item;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class OrderItemController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Order Item';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new OrderItem());

        $grid->column('id', __('Id'));
        $grid->column('order_id', __('Order '));
        $grid->column('item_id', __('Item '))->display(function($item_id){
            $item_name = Item::where('id',$item_id)->value('item_name');
            return $item_name;
        });
        $grid->column('item_name', __('Item Name'));
        $grid->column('quantity', __('Quantity'));
        $grid->column('price_per_item', __('Price Per Item'));
        $grid->column('total', __('Total'));
     

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
        $orders = Order::pluck('id','id');
        $items = Item::pluck('item_name', 'id');

        $filter->equal('order_id', __('Order id'))->select($orders);
        $filter->equal('item_id', __('Item id'))->select($items);
        $filter->like('item_name', __('Item name'));
        $filter->like('quantity', __('Quantity'));
        $filter->like('price_per_item', __('Price per item'));
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
        $show = new Show(OrderItem::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('order_id', __('Order id'));
        $show->field('item_id', __('Item id'));
        $show->field('item_name', __('Item name'));
        $show->field('quantity', __('Quantity'));
        $show->field('price_per_item', __('Price per item'));
        $show->field('total', __('Total'));
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
        $form = new Form(new OrderItem());
        $orders = Order::pluck('id','id');
        $items = Item::pluck('item_name', 'id');

        $form->select('order_id', __('Order'))->options($orders)->rules(function ($form) {
            return 'required';
        });
        $form->select('item_id', __('Item'))->options($items)->rules(function ($form) {
            return 'required';
        });
        $form->text('item_name', __('Item Name'))->rules(function ($form) {
            return 'required|max:150';
        });
        $form->text('quantity', __('Quantity'))->rules(function ($form) {
            return 'required|max:150';
        });
        $form->decimal('price_per_item', __('Price Per Item'))->rules('required');
        $form->decimal('total', __('Total'))->rules('required');

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
