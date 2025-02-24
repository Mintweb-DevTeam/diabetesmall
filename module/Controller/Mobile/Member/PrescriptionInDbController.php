<?php
namespace Controller\Mobile\Member;

use Request;
use App;
use Session;
use Component\Policy\Policy;
class PrescriptionInDbController extends \Controller\Front\Member\PrescriptionInDbController
{

	public function index()
	{
		parent::index();
	}
}