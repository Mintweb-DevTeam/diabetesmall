<?php
namespace Controller\Front;

class TestController extends \Controller\Front\Controller
{
	public function index()
	{
		if ( in_array(\Request::getRemoteAddress(), ['182.216.219.157']) ) {
			$db = \App::load('DB');
			$db->fetch('SET SESSION group_concat_max_len = 102400');

			$sql = " 
				select 
					memNm, cellPhone, email, pharmacy_code, name
				from 
					co_abbottMember m
					left outer join co_pharmacy p on m.pharmacy_code = p.code
				order by
					m.sno desc
				limit 
					0, 10 
			";
			$result = $db->query_fetch($sql);
			$this->var_dump_table($result);

			exit;
		}
	}

	public function var_dump_table($arrList, $isPre=true) {
		if (!is_array($arrList)) echo 'null';
		echo '<table border="1">';
		$loop = 0;
		foreach($arrList as $arr) {
			if (!$loop) {
				echo '<thead><tr>';
				foreach($arr as $key => $val) {
					echo '<th>'.$key.'</th>';
				}
				echo '</tr></thead><tbody>';
			}

			echo '<tr>';
			foreach($arr as $key => $val) {
				if ($isPre) echo '<td><pre>'.$val.'</pre></td>';
				else echo '<td>'.$val.'</td>';
			}
			echo '</tr>';

			$loop += 1;
		}
		echo '</tbody></table>';
	}
}