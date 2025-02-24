<?php
namespace Controller\Mobile\Subscription;

use App;
use Request;

class CardRegisterController extends \Controller\Front\Controller 
{
    public function index()
    {
        $obj = App::load("\Component\Subscription\Subscription");
        $cfg = $obj->getCfg();

        $isAgree = Request::post()->get("isAgree");
        $isOrder = Request::get()->get("isOrder");
		 $order_page = Request::get()->get("order_page");
        if ($isOrder)
            $isAgree = 1;
        
        $this->setData("isOrder", $isOrder);
        $this->setData("order_page", $order_page);
        
        //$template = "card_register_terms";
        //if ($isAgree)
            $template = "card_register";
        
        $this->getView()->setPageName("subscription/{$template}");
        
        $this->setData("chars", $obj->getShuffleChars());
        $this->setData("subCfg", $cfg);
    }
}