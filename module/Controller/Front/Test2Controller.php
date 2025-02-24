<?php

namespace Controller\Front;

use Exception;
/*
require_once dirname(__FILE__)."/../../../tcpdf/config/tcpdf_config.php";
require_once dirname(__FILE__)."/../../../tcpdf/tcpdf_autoconfig.php";
require_once dirname(__FILE__)."/../../../tcpdf/tcpdf.php";
*/
//use Component\Fpdf\Fpdf;
use Component\Fpdf\PDFKorean;
class TestController extends \Controller\Front\Controller
{
	
	public function index()
	{
		
		$server=\Request::server()->toArray();
		$pdf=new PDFKorean();

		$pdf->AddUHCFont();
		$pdf->AddPage();
		$pdf->SetFont('UHC','',18);
		$pdf->WriteHTML(iconv("utf-8","euc-kr",'<div class=\'info\'><span>■국민건강보험법 시행규칙(별지 제19호외6서식)</span></div><div class="title">요양비 지급청구 위임장</div><div class="content_body"><div class=\'info1\'><span>※뒤쪽의 유의사항을 읽고 작성해 주시기 바라며,【 】에는 해당되는 곳에 √표시를 합니다.</span><span >(앞쪽)</span></div><table class="Table"><colgroup><col width="20%"/><col width="20%"/><col width="20%"/><col/></colgroup><tbody><tr><th rowspan="6" class="borderTop borderRight">① 위임인</th><td rowspan="2" class="borderTop borderRight th">가입자 또는<br>피부양자</td><td class="borderTop borderRight">성명</td><td class="borderTop"><?=$pname?></td></tr><tr><td class="borderTop borderRight">주민(외국인)등록번호</td><td><?=$pjumin?></td></tr></table>'));
		$pdf->Output("kk.pdf","I");
		

		
				
		exit();
	
	}

}