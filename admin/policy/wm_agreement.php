<form id="frm" action="./wm_agreement_ps.php" method="post">
<div class='page-header js-affix'>
    <h3><?=end($naviMenu->location)?></h3>
    <div class="btn-group">
        <input type="button" value="저장" class="btn btn-red wm-submit-btn">
    </div>
</div>

<div class="table-title">제3자 정보 제공(세일즈포스)</div>
<table class="table table-cols">
    <colgroup>
        <col class="width-md"/>
        <col/>
    </colgroup>
    <tbody>
        <tr>
            <th>내용</th>
            <td>
                <textarea name="contents" rows="20" class="form-control"><?=$data?></textarea>
            </td>
        </tr>
    </tbody>
</table>
<script>
    $(document).ready(function (){
        $('.wm-submit-btn').click(function (){
            const title = "저장";
            const frmNm = "frm";
            BootstrapDialog.confirm({
                type: BootstrapDialog.TYPE_WARNING,
                title: title,
                message: title + "하시겠습니까?",
                callback: function (result) {
                    if (result) {
                        $('#' + frmNm).attr('target', 'ifrmProcess');
                        $('#' + frmNm).submit();
                    }
                }
            });
        });
    });
</script>
</form>