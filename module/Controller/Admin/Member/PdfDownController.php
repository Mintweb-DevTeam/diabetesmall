<?php
namespace Controller\Admin\Member;

use App;
use Request;
use Component\Fpdf\PDFKorean;

class PdfDownController extends \Controller\Admin\Controller
{
	public function index()
	{
		$db=App::load(\DB::class);
		$in = Request::request()->all();
		$idx = $in['idx'];
		$content =iconv("utf-8","euc-kr",$in['pdf_content']);

		$sql="select * from wm_proxy_apply where idx=?";
		$apply_row = $db->query_fetch($sql,['i',$idx]);

		
		$Admin_Id =  \Session::get('manager.managerId');
		$ip = \Request::getRemoteAddress();
		$sql="insert into wm_proxy_downLog set apply_idx=?,memId=?,down_type='pdf',regDt=sysdate(),ip=?";
		$db->bind_query($sql,['iss',$in['idx'],$Admin_Id,$ip]);
		
		ob_end_clean();
		$pdf=new PDFKorean();

		$pdf->AddUHCFont();
		$pdf->AddPage();
		$pdf->SetFont('UHC','',18);


		$in['pdf_content']='<div class=\'info\'><span>■국민건강보험법 시행규칙(별지 제19호외6서식)</span></div><div class="title"><h4>요양비 지급청구 위임장</h4></div>
		<div class="content_body"><div class=\'info1\'><span>※뒤쪽의 유의사항을 읽고 작성해 주시기 바라며,【 】에는 해당되는 곳에 √표시를 합니다.</span><span >(앞쪽)</span></div><table class="Table"><colgroup><col width="20%"/><col width="20%"/><col width="20%"/><col/></colgroup><tbody><tr><th rowspan="6" class="borderTop borderRight">① 위임인</th><td rowspan="2" class="borderTop borderRight th">가입자 또는<br>피부양자</td><td class="borderTop borderRight">성명</td><td class="borderTop"><?=$pname?></td></tr><tr><td class="borderTop borderRight">주민(외국인)등록번호</td><td><?=$pjumin?></td></tr>
					<tr>
						<td rowspan="3"  class="borderTop borderRight th">법정대리인</br>또는 가족</td>
						<td class="borderTop borderRight">성명</td>
						<td class="borderTop"><?=$lname?></td>
					</tr>
					<tr>
						<td class="borderTop borderRight">생년월일</td>
						<td class="borderTop"><?=$birth?></td>
					</tr>
					<tr>
						<td class="borderTop borderRight">가입자·피부양자와의 관계</td>
						<td class="borderTop"><?=$conn?></td>
					</tr>
					<tr>
						<td class="borderTop borderRight th">전화번호</br>(위임사항 및<br>지급내역 수신용)</td>
						<td colspan="3" class="borderTop">
							<div class=\'tel1\'> <?=substr($cellPhone,0,3)?>-<?=substr($cellPhone,3,4)?>-<?=substr($cellPhone,7,4)?>【√】문자메시지 수신동의</div>
							<div class=\'tel2\'>위임처리결과 및 지급내역 등을 전송받을 연락처로 정확히 적어 주시기 바랍니다.</div>
							<div class=\'tel3\'>(문자메시지 수신을 동의한 경우에만 발송)</div>
						</td>
					</tr>
					<tr>
						<th rowspan="4" class="borderTop borderRight">② 준요양기관</th>
						<td class="borderTop borderRight th">상호</td>
						<td colspan="2" class="borderTop"><?=$companyNm?></td>
					</tr>
					<tr>
						<td class="borderTop borderRight th">사업자등록번호</br>(법인등록번호)</td>
						<td colspan="2" class="borderTop"><?=$businessNo?></td>
					</tr>
					<tr>
						<td class="borderTop borderRight th">대표자</td>
						<td colspan="2" class="borderTop">윤길호</td>
					</tr>
					<tr>
						<td class="borderTop borderRight th">전화번호</td>
						<td colspan="2" class="borderTop"><?=$phone?></td>
					</tr>

					<tr>
					
						<th rowspan="4" class="borderTop borderRight">③ 요양비 수령계좌</th>
						<td class="borderTop borderRight th">수령자</td>
						<td colspan="2" class="borderTop">아델라헬스케어</td>
					</tr>
					<tr>
						<td rowspan="3" class="borderTop borderRight th">수령계좌</td>
						<td class="borderTop borderRight">금융기관</td>
						<td class="borderTop">우리은행</td>
					</tr>
					<tr>
						<td class="borderTop borderRight">예금주</td>
						<td class="borderTop"><?=$companyNm?></td>
					</tr>
					<tr>
						<td class="borderTop borderRight">계좌번호</td>
						<td class="borderTop">1005-003-972156</td>
					</tr>

					<tr>
						<th class="borderTop borderRight">④ 위임사항</th>
						<td colspan="3" class="borderTop item">
							<div><span>1)【&nbsp;&nbsp;&nbsp;&nbsp;】자동복막투석 소모성 재료</span><span>【&nbsp;&nbsp;&nbsp;&nbsp;】복막관류액</span></div>
							<div><span>2)【&nbsp;&nbsp;&nbsp;&nbsp;】가정용 산소발생기</span><span>【&nbsp;&nbsp;&nbsp;&nbsp;】휴대용 산소발생기</span></div>
							<div><span>3)【√】당뇨병 소모성 재로</span><span>【√】연속혈당특정용 전극(센서)</span></div>
							<div><span>4)【&nbsp;&nbsp;&nbsp;&nbsp;】자가도뇨 소모성 재로</span></div>
							<div><span>5)【&nbsp;&nbsp;&nbsp;&nbsp;】인공호흡기 및 기본소모품</span><span>【&nbsp;&nbsp;&nbsp;&nbsp;】선택소모품</span></div>
							<div><span>6)【&nbsp;&nbsp;&nbsp;&nbsp;】기침유받기</span></div>
							<div><span>7)【&nbsp;&nbsp;&nbsp;&nbsp;】양압기 및 소모품</span></div>
							<div><span>8)【&nbsp;&nbsp;&nbsp;&nbsp;】연속할당측정기</span><span>【&nbsp;&nbsp;&nbsp;&nbsp;】인슐린자동주입기</span></div>
							<div><span>9)【&nbsp;&nbsp;&nbsp;&nbsp;】출산비</span></div>
							<div></br><span>※【√】표시,중복 표시가능</span></div>
						</td>
					</tr>
					<tr>
						<th class="borderTop borderRight">⑤ 위임기간</th>
						<td colspan="3" class="borderTop period">
							<span><?=substr($regDt,0,4)?>년 <?=substr($regDt,5,2)?>월 <?=substr($regDt,8,2)?>일부터</span><span> <?=$end_date?></span> 까지(최장5년)
						</td>
					</tr>
					<tr>
						<td colspan="4" class="borderTop">
							<div class="guide">
							<div>
								<span>「국민건강보험법」 제49조3항 및 같은 법 시행규칙 제23조제3항에 따라 요양비 지급 청구에 관한</span>
								<span>사항을 위와 같이 위임합니다.</span>
							</div>
							<div>
								<span><?=substr($regDt,0,4)?>년&nbsp;&nbsp;<?=substr($regDt,5,2)?>월&nbsp;&nbsp;<?=substr($regDt,8,2)?>일</span>
							</div>
							<div>
								

								<span>위임인</span> 
								<div class="pname_sign"><em><?=$pname?></em></div>
								<div class="sign1">
									<span>(서명 또는 인)</span>
									<div class="sign_img"><img src="<?=$sign?>"/></div>
								</div>
							</div>

							<div class="borderBottomStrong">
								
								<span>※</span>
								<span>국민건강보험법 시행규칙 제23조4항에 따라, 위임하는 사람의 신분증 사본과 준요양기관 대표자</span>
								<span>신분증 또는 사업자등록증 사본을 각각 제출하셔야 합니다.</span>
							</div>
							</div>
						</td>
					</tr>
				</tbody>
			</table>';
		$pdf->WriteHTML(iconv("utf-8","euc-kr",$in['pdf_content']));
		$file_name=date("Y_m_d").$apply_row[0]['pname']."_위임장.pdf";
		$pdf->Output($file_name,"I");


		exit;
	}

}