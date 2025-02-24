<?php
namespace Controller\Admin\Member;
class PharmacyExcelController extends \Controller\Admin\Controller {
    public function index(){
        $this->callMenu('member', 'pharmacy', 'pharmacy_excel');
    }
}