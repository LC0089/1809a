<?php

namespace App\Admin\Controllers;

use App\Model\OrderModel;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Show;

class OrderController extends Controller
{
    use HasResourceActions;

    /**
     * Index interface.
     *
     * @param Content $content
     * @return Content
     */
    public function index(Content $content)
    {
        return $content
            ->header('Index')
            ->description('description')
            ->body($this->grid());
    }

    /**
     * Show interface.
     *
     * @param mixed $id
     * @param Content $content
     * @return Content
     */
    public function show($id, Content $content)
    {
        return $content
            ->header('Detail')
            ->description('description')
            ->body($this->detail($id));
    }

    /**
     * Edit interface.
     *
     * @param mixed $id
     * @param Content $content
     * @return Content
     */
    public function edit($id, Content $content)
    {
        return $content
            ->header('Edit')
            ->description('description')
            ->body($this->form()->edit($id));
    }

    /**
     * Create interface.
     *
     * @param Content $content
     * @return Content
     */
    public function create(Content $content)
    {
        return $content
            ->header('Create')
            ->description('description')
            ->body($this->form());
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new OrderModel);

        $grid->order_id('Order id');
        $grid->order_number('Order number');
        $grid->user_id('User id');
        $grid->order_amount('Order amount');
        $grid->pay_type('Pay type');
        $grid->pay_status('Pay status');
        $grid->order_message('Order message');
        $grid->order_status('Order status');
        $grid->is_order('Is order');
        $grid->pay_time('Pay time');
        $grid->create_time('Create time');
        $grid->order_count('Order count');
        $grid->is_del('Is del');

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
        $show = new Show(OrderModel::findOrFail($id));

        $show->order_id('Order id');
        $show->order_number('Order number');
        $show->user_id('User id');
        $show->order_amount('Order amount');
        $show->pay_type('Pay type');
        $show->pay_status('Pay status');
        $show->order_message('Order message');
        $show->order_status('Order status');
        $show->is_order('Is order');
        $show->pay_time('Pay time');
        $show->create_time('Create time');
        $show->order_count('Order count');
        $show->is_del('Is del');

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new OrderModel);

        $form->number('order_id', 'Order id');
        $form->text('order_number', 'Order number');
        $form->number('user_id', 'User id');
        $form->decimal('order_amount', 'Order amount');
        $form->number('pay_type', 'Pay type');
        $form->switch('pay_status', 'Pay status');
        $form->text('order_message', 'Order message');
        $form->switch('order_status', 'Order status')->default(1);
        $form->text('is_order', 'Is order')->default('1');
        $form->number('pay_time', 'Pay time');
        $form->number('create_time', 'Create time');
        $form->text('order_count', 'Order count')->default('无');
        $form->text('is_del', 'Is del');

        return $form;
    }
}
