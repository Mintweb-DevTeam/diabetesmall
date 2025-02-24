<?php

namespace Controller\Front\Subscription;

use App;
use Request;

class CardRegisterOrderController extends \Controller\Front\Controller 
{

    public function index()
    {
			ini_set("session.cookie_samesite", "none");
			ini_set("session.cookie_secure", 1);


        $obj = App::load("\Component\Subscription\Subscription");
        $cfg = $obj->getCfg();

        $isAgree = Request::post()->get("isAgree");
        $isOrder = Request::get()->get("isOrder");
        if ($isOrder)
            $isAgree = 1;
        
        $this->setData("isOrder", $isOrder);
        
        //$template = "card_register_terms";
        //if ($isAgree)
            $template = "card_register_order";
        
        $this->getView()->setPageName("subscription/{$template}");
        
        $this->setData("chars", $obj->getShuffleChars());
        $this->setData("subCfg", $cfg);
    }
}