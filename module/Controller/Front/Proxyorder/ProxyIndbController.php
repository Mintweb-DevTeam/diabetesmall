<?php
namespace Controller\Front\Proxyorder;

use App;
use Request;
use Component\Validator\Validator;
use Framework\Debug\Exception\AlertOnlyException;
use Framework\Debug\Exception\AlertRedirectException;
use Exception;
use Framework\Utility\StringUtils;
use Component\Storage\Storage;
use UserFilePath;
class ProxyIndbController extends \Controller\Front\Controller
{

	public function index()
	{
		$session = \Session::all();
		if( !$session['member']['memNo'] ){
			echo '<script>alert("로그인이 필요합니다.");location.replace("/member/login.php?returnUrl=/service/mandate_set.php");</script>';
			exit;
		}
		$db = App::load(\DB::class);
		$memNo=\Session::get("member.memNo");
		$siteKey=\Session::get('siteKey');
		
		$request = Request::request()->all();
		$request = StringUtils::xssArrayClean($request);

		$pjumin=$request['pjumin1']."-".$request['pjumin2'];

		
		$sql="select count(idx) as cnt from wm_proxy_apply where pname=? and pjumin=? and substr(REPLACE(regDt,'-',''),1,8)=? and agree='y'";
		$row=$db->query_fetch($sql,['sss',$request['pname'],$pjumin,date("Ymd")]);

		if(!empty($row[0]['cnt'])){
			$data['result']='n';
            $data['message']="금일 이미신청된 내역이 있습니다.";
			$data['idx']=0;		
			exit;
		}

		$validator = new Validator();

		$arrCheckElement[]= array('element'  => 'pname','command'  => 'alphaHangul','required' => true,'name'     => __('가입자성명'),'implode'  => ' ',);
		$arrCheckElement[]= array('element'  => 'pjumin1','command'  => 'number','required' => true,'name'     => __('가입자주문(외국인)등록번호'),'implode'  => ' ',);
		$arrCheckElement[]= array('element'  => 'pjumin2','command'  => 'number','required' => true,'name'     => __('가입자주문(외국인)등록번호'),'implode'  => ' ',);
		$arrCheckElement[]= array('element'  => 'lname','command'  => 'alphaHangul','required' => false,'name'     => __('법정대리인성명'),'implode'  => ' ',);
		$arrCheckElement[]= array('element'  => 'birth','command'  => 'number','required' => false,'name'     => __('법정대리인생년월일'),'implode'  => ' ',);
		$arrCheckElement[]= array('element'  => 'conn','command'  => 'required','required' => false,'name'     => __('법정대리인관계'),'implode'  => ' ',);
		$arrCheckElement[]= array('element'  => 'cellPhone','command'  => 'number','required' => true,'name'     => __('전화번호'),'implode'  => ' ',);
		$arrCheckElement[]= array('element'  => 'item1','command'  => 'required','required' => true,'name'     => __('당뇨병소모성 재료'),'implode'  => ' ',);
		$arrCheckElement[]= array('element'  => 'item2','command'  => 'required','required' => true,'name'     => __('연속혈당측정용 전극(센서)'),'implode'  => ' ',);
		$arrCheckElement[]= array('element'  => 'signVal','command'  => 'required','required' => true,'name'     => __('위임인서명'),'implode'  => ' ',);


		$proxyObj= new \Component\Proxyservice\ProxyService();


		 foreach ($arrCheckElement as $validData) {
            if ($validData['required'] === true) {
                // 필수 값인 경우 값이 선언되어 있지 않으면 Exception 처리
                if (gd_isset($request[$validData['element']]) === false) {
                    throw new AlertOnlyException(sprintf(__('%s 항목이 잘못 되었습니다.'), $validData['name']));
                }
            } else {
                // 필수 값이 아닌 경우 선언되어 있지 않다면 빈값
                gd_isset($request[$validData['element']], '');
            }

            // 배열 값이면 implode 처리
            if (is_array($request[$validData['element']]) === true) {
                $request[$validData['element']] = ArrayUtils::removeEmpty($request[$validData['element']]);
                $request[$validData['element']] = implode($validData['implode'], $request[$validData['element']]);
            }

            // 이외 항목 처리를 위한 배열 값 생성
            $arrCheck[] = $validData['element'];

            // 항목별 Validation
			
            $validator->add($validData['element'], $validData['command'], $validData['required'], '{' . $validData['name'] . '}');		
		}

        // Validation 결과
		$data=[];
        if ($validator->act($request, true) === false) {
			$data['result']='n';
            $data['message']=implode("\n",$validator->errors);
			$data['idx']=0;
			
        }else{
			$filename=$this->fileUp();


			if(!empty($filename)){
				$data['result']='y';
				$data['message']=$filename;

				$pjumin=$request['pjumin1']."-".$request['pjumin2'];
				$pjumin=$proxyObj->Encrypt($pjumin);

				$item1=$request['item1'];
				$item2=$request['item2'];
				
				$lname = $request['lname'];
				$birth = $request['birth'];

				$sql="insert into wm_proxy_apply set memNo='{$memNo}',siteKey='{$siteKey}',pname='{$request['pname']}',pjumin='{$pjumin}',lname='{$lname}',birth='{$birth}',conn='{$request['conn']}',cellPhone='{$request['cellPhone']}',item1='{$item1}',item2='{$item2}',upfile='{$filename}',sign='{$request['signVal']}',regDt=sysdate()";
				$db->query($sql);
				$idx=$db->insert_id();
				$data['idx']=$idx;


			}else{
				$data['result']='n';
				$data['message']="파일업로드 오류입니다.";
				$data['idx']=0;
			}

		}

		$this->json($data);
		exit;
	
	}


	public function fileUp()
	{
	
		$Files=Request::files()->toArray();

		$img_type = explode("/",$Files['upfile']['type']);

		$img_type2 = explode(".",$Files['upfile']['name'],2);

		$in =\Request::request()->all();


		
		$types=array("pdf","jpg","jpeg","gif","png","bmp");
		
		if(in_array(strtolower($img_type[1]),$types)===false){
			if(in_array(strtolower($img_type2[1]),$types)===false){
				return false;
			}else{
				$img_type[1]=$img_type2[1];
			}
		}
		
	
		$option=['width'=>0,'quality'=>'high','overWrite'=>true];//옵션값은 width값이 0이상이면 썸네일, 퀄리티, 동일한 파일명이 있는경우 덮어쓰기여부

		$storage = Storage::disk(Storage::PATH_CODE_DEFAULT,'local');    //파일저장소세팅
		
		if($img_type[1]=="jpeg")
			$img_type[1]="jpg";

		$user_file=time()."_".uniqid().".".strtolower($img_type[1]);	
		$zip_file=time()."_".uniqid().".zip";	

		$tmpFilePath = UserFilePath::data('excel', $this->fileConfig['menu'], $v)->getRealPath();
        $fileName = pathinfo($tmpFilePath)['filename'];

		$zipFilePath = UserFilePath::data('upfile', $this->fileConfig['menu'], $fileName . ".zip")->getRealPath();
				
		try{

			$proxyObj= new \Component\Proxyservice\ProxyService();

			$cfg=$proxyObj->getCfg();

			$path = $Files['upfile']['tmp_name'];
			$storage->upload($path, "upfile/".$user_file,$option);
			
			$server = \Request::server()->toArray();

			$base_dir = $server['DOCUMENT_ROOT']."/data/upfile";
			$data_path=$server['DOCUMENT_ROOT']."/data/upfile/".$user_file;

			$zipFilePath=$server['DOCUMENT_ROOT']."/data/upfile/".$zip_file;
			
			exec('cd ' . $base_dir .' && zip -jP ' . $cfg['filePw'] . ' -r "' .$zipFilePath.'" "'.$data_path.'"');
			@unlink($base_dir."/".$user_file);

			return $zip_file;

		}catch(Exception $e){
			return false;
		}

		exit;
	
	
	}

}