<?php
namespace Controller\Admin\Member;

use App;
use Request;

class PrescriptionImgUploadIndbController extends \Controller\Admin\Controller
{


	public function index()
	{
		$in = Request::request()->all();
		$server = Request::server()->toArray();
		
		$db = App::load(\DB::class);
		
		switch($in['mode']){
			case "del":
				
				foreach($in['chk'] as $key => $val){
				
					$sql = "select * from wm_prescription_img where idx=?";
					$row = $db->slave()->query_fetch($sql,['i',$val]);
					
					$path = $server['DOCUMENT_ROOT']."/data/wm_upload/prescription_img/".$row[0]['file_name'];
					
					if(is_file($path) && !empty($row[0]['file_name'])){
					
						@unlink($path);
					}
					
					$sql="delete from wm_prescription_img where idx='{$val}'";
					$db->query($sql);
					
				}
				
				$data['success']=1;
				$this->json($data);
				
				break;
			case "excel_down":
				
				$strSQL=$in['qry'];
				$data = $db->slave()->query_fetch($strSQL);
				
				
				header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
				header('Content-Disposition: attachment;filename="처방전업로드리스트.xls"');
				header('Cache-Control: max-age=0');
				header('Cache-Control: max-age=1'); 
				?>
					<table border='1'>
						<thead>
							<tr>
								<th>회원명</th>
								<th>회원ID</th>
								<th>이메일</th>
								<th>휴대폰번호</th>
								<th>처방전파일</th>
								<th>처방전등록일</th>
							</tr>
						</thead>
						<tbody>
							<?php 
							
							foreach($data as $key => $val){?>
							<tr>
								<td align="center"><?=$val['memNm']?></td>
								<td align="center"><?=$val['memId']?></td>
								<td align="center"><?=$val['email']?></td>
								<td align="center"><?=$val['cellPhone']?></td>							
								<td><?=$val['ori_file_name']?></td>
								<td align="center"><?=$val['regDt']?></td>
							</tr>
							<?php }?>
						</tbody>
					</table>					
					
				<?php 

				break;
			
		}
		exit;
	}

}