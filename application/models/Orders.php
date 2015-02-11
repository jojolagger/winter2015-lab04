<?php

/**
 * Data access wrapper for "orders" table.
 *
 * @author jim
 */
 
class Orders extends MY_Model {

    // constructor
    function __construct() {
        parent::__construct('orders', 'num');
		
		$CI = &get_instance(); $CI->load->model('orderitems'); $CI->load->model('menu');
    }

    // add an item to an order
    function add_item($num, $code) {
        if( ($item = $this->orderitems->get($num, $code)) != null ){
			$item->quantity += 1;
			$this->orderitems->update($item);
		} else {
			$item = $this->orderitems->create();
			$item->quantity = 1;
			$item->order = $num;
			$item->item = $code;
			$this->orderitems->add($item);
		}
		$this->orders->total($num);
    }

    // calculate the total for an order
    function total($num) {
		$tot = 0.0;
		$orderItems = $this->orderitems->some('order', $num);
		foreach ($orderItems as $entry){
			$item = $this->menu->get($entry->item);
			$tot += $item->price * $entry->quantity;
		}
		
		$order = $this->orders->get($num);
		$order->total = $tot;
		$this->orders->update($order);
        return $tot;
    }

    // retrieve the details for an order
    function details($num) {
		$itemDetails = $this->orderitems->some('order', $num);
		foreach($itemDetails as &$entry){
			$item = $this->menu->get($entry->item);
			$entry->picture = $item->picture;
			$entry->description = $item->description;
		}
        return $itemDetails;
    }

    // cancel an order
    function flush($num) {
		
        
    }

    // validate an order
    // it must have at least one item from each category
    function validate($num) {
		$stuff = $this->orderitems->some('order',$num);
		if( count($stuff) > 0){return true;}
        return false;
    }

}
