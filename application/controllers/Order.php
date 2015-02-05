<?php

/**
 * Order handler
 * 
 * Implement the different order handling usecases.
 * 
 * controllers/welcome.php
 *
 * ------------------------------------------------------------------------
 */
class Order extends Application {

    function __construct() {
        parent::__construct();
    }

    // start a new order
    function neworder() {
		$order_num = $this->orders->highest() + 1;
        $new = $this->orders->create();
		$new->num = $order_num;
		$new->date = date("Y-m-d H:i:s");
		$new->status = 'a';
		$new->total = 0.0;
		$this->orders->add($new);
		
        redirect('/order/display_menu/' . $order_num);
    }

    // add to an order
    function display_menu($order_num = null) {
        if ($order_num == null){
            redirect('/order/neworder');
		}
		$order = $this->orders->get($order_num);
        $this->data['pagebody'] = 'show_menu';
		$this->data['title'] = 'Order ' . $order->num . ': Total = $' . sprintf('%0.2f', $order->total);
        $this->data['order_num'] = $order->num;
		
        // Make the columns
        $this->data['meals'] = $this->make_column('m', $order->num);
        $this->data['drinks'] = $this->make_column('d', $order->num);
        $this->data['sweets'] = $this->make_column('s', $order->num);
		
		
        $this->render();
    }

    // make a menu ordering column
    function make_column($category, $num) {
        $items = $this->menu->some('category',$category);
		foreach($items as &$item){
			$item->order_num = $num;
		}
        return $items;
    }

    // add an item to an order
    function add($order_num, $item) {
        $this->orders->add_item($order_num, $item);
        redirect('/order/display_menu/' . $order_num);
    }

    // checkout
    function checkout($order_num) {
        $this->data['title'] = 'Checking Out';
        $this->data['pagebody'] = 'show_order';
        $this->data['order_num'] = $order_num;
		$this->data['total'] = sprintf('$ %0.2f',$this->orders->total($order_num));
		
        //FIXME
		$this->data['items'] = $this->orders->details($order_num);
		
		
		$this->data['okornot'] = $this->orders->validate($order_num);

        $this->render();
    }

    // proceed with checkout
    function commit($order_num) {
		if(!$this->orders->validate($order_num)){
			redirect('/order/display_menu/' . $order_num);
		}
		$record = $this->orders->get($order_num);
		$record->date = date("Y-m-d H:i:s");
		$record->status = 'c';
		$record->total = $this->orders->total($order_num);
		$this->orders->update($record);
		
        redirect('/');
    }

    // cancel the order
    function cancel($order_num) {
        $this->orderitems->delete_some($order_num);
		$record = $this->orders->get($order_num);
		$record->status = 'x';
		$record->total = 0.0;
		$this->orders->update($record);
        redirect('/');
    }

}
