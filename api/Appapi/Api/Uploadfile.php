<?php
//session_start();
class Api_Uploadfile extends PhalApi_Api
{

    public function getRules()
    {
        return array(
            'uploadImg' => array(
                'uid' => array('name' => 'uid', 'type' => 'int', 'min' => 1, 'require' => true, 'desc' => '用户ID'),
                'token' => array('name' => 'token', 'type' => 'string', 'require' => true, 'desc' => '用户token'),
                'file' => array('name' => 'file', 'type' => 'file', 'require' => true, 'min' => 0, 'range' => array('image/jpg', 'image/jpeg', 'image/png'), 'ext' => array('jpg', 'jpeg', 'png')),
            ),
            'updateVideo' => array(
                'uid' => array('name' => 'uid', 'type' => 'int', 'min' => 1, 'require' => true, 'desc' => '用户ID'),
                'token' => array('name' => 'token', 'type' => 'string', 'require' => true, 'desc' => '用户token'),
                'file' => array('name' => 'file', 'type' => 'file', 'require' => true, 'desc' => '视频文件'),
            ),
            'uploadPicture' => array(
                'uid' => array('name' => 'uid', 'type' => 'int', 'min' => 1, 'require' => true, 'desc' => '用户ID'),
                'token' => array('name' => 'token', 'type' => 'string', 'require' => true, 'desc' => '用户token'),

            ),
        );
    }

    /**
     * 图像上传
     * @desc 用于图像上传【POST】
     * @return int code 操作码，0表示成功
     * @return array info.img 图像
     * @return string msg 提示信息
     */
    public function uploadImg()
    {
        $rs = array('code' => 0, 'msg' => '上传图片成功', 'info' => array());

        $language_id = $_REQUEST['language_id'];
        if (empty($language_id)) {
            $language_id = 101;
        }
        $checkToken = checkToken($this->uid, $this->token);
        if ($checkToken == 700) {
            $rs['code'] = $checkToken;
            $language = DI()->config->get('language.tokenerror');
            $rs['msg'] = $language[$language_id];
            return $rs;
        }

        if (!isset($_FILES['file'])) {
            $rs['code'] = 1001;
            $rs['msg'] = T('miss upload file');
            return $rs;
        }

        if ($_FILES["file"]["error"] > 0) {
            $rs['code'] = 1002;
            $rs['msg'] = T('failed to upload file with error: {error}', array('error' => $_FILES['file']['error']));
            DI()->logger->debug('failed to upload file with error: ' . $_FILES['file']['error']);
            return $rs;
        }

        $result = upload_file($_FILES['file']);

        if (!isset($result['url']) || empty($result['url'])) {
            $rs['code'] = 1003;
            $rs['msg'] = '上传图片失败，请稍候重试';
            return $rs;
        }

        $rs['info']['img'] = $result['url'];

        return $rs;

    }
    /**
     * 视频上传
     * @desc 视频上传
     * @return int code 操作码，0表示成功
     * @return string msg 提示信息
     * @return string info.href 视频链接
     *  @return string info.thumb 封面图
     * @return string info.duration 时长
     */
    public  function updateVideo()
    {
        $rs = array('code' => 0, 'msg' => '上传视频成功', 'info' => array());

        $tenant_id = getTenantId();
        $videoinfo = getCutvideo($tenant_id);
        if (isset($videoinfo['data']['fileStoreKey'])){
            $rs['info'] = [$videoinfo['data']['fileStoreKey']];
            return  $rs;
        }

        $rs['code'] = 1000;
        $rs['msg'] = '上传失败，请稍候重试';
        return $rs;

    }
    /**
     * 视频上传
     * @desc
     * @return int code 操作码，0表示成功
     * @return string msg 提示信息
     * @return string info.href 视频链接
     *  @return string info.thumb 封面图
     * @return string info.duration 时长
     */
    public  function uploadPicture()
    {

        $rs = array('code' => 0, 'msg' => '上传图片成功', 'info' => array());

        $number  =  count($_FILES['file']['name']) ;
        $url = 'http://16.162.206.41:9999/api/img/upload';
        for ($i= 0 ;$i<$number ;$i++){

            $file = $_FILES['file'];        //文件信息
            $filename = $file['name'][$i];      //本地文件名

            $tmpFile = $file['tmp_name'][$i];   //临时文件名
            $fileType = $file['type'][$i];      //文件类型
            $uplodainfo = postUploadFile($url,$filename,$tmpFile,$type = 'text/plain');
            $uplodainfo =  json_decode($uplodainfo,true);
            if ($uplodainfo['code'] ==200){
                $data =  parse_url($uplodainfo['data']['fileStoreKey']);
                $rs['info'][] = $data['path'];
             }else{
                $rs['info'] = [];
                $rs['msg'] = $uplodainfo['msg'];
            }
        }

        if ($rs['info']){
            return $rs;
        }
        $rs['code'] = 1000;
        return $rs;

    }


}
