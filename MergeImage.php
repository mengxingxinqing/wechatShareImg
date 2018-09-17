<?php


namespace App\Models;


class MergeImage
{
    public function genrateActiveImg($config)
    {

        $imgList = [];
        $delList = [];
        if(isset($config['imglist']) && count($config['imglist'])>0){
            foreach ($config['imglist'] as $k=>$v){
                if(strpos($v['path'],'http://') !== false || strpos($v['path'],'https://') !== false){
                    $path = $this->downloadImg($v['path'],'./download/','');
                    $imgList[] = $path;
                    $delList[] = $path;
                }else{
                    $imgList[] = $v['path'];
                }
            }
        }

        // 创建图片对象
        $image_1 = $this->getImgObj($config['background']);
        imagesavealpha($image_1, true);
        $size = $this->getImgAttr($config['background']);
        if(count($imgList)>0){
            foreach ($imgList as $k=>$v){
                $image_2 = $this->getImgObj($v);
                if(empty($image_2)){
                    continue;
                }
                $size_2 = $this->getImgAttr($v);
                if(!isset($config['imglist'][$k]['size'])){
                    die('size属性必填');
                }
//                根据配置重置图片大小
                if(isset($config['imglist'][$k]['size']['w']) && isset($config['imglist'][$k]['size']['h'])){
                    if($config['imglist'][$k]['size']['w'] != $size_2['w'] || $config['imglist'][$k]['size']['h'] != $size_2['h']){
                        $image_2 = $this->imageResize($image_2,$size_2['w'],$size_2['h'],$config['imglist'][$k]['size']['w'],$config['imglist'][$k]['size']['h']);
                        $size_2 = ['w'=>$config['imglist'][$k]['size']['w'],'h'=>$config['imglist'][$k]['size']['h']];
                    }
                }
//                判定是否需要设置圆角
                if(isset($config['imglist'][$k]['style']) && $config['imglist'][$k]['style'] == 'circle'){
                    $image_2 = $this->radius_img($image_2,$size_2['w'],$size_2['h'],60);
                }
                $pos = $this->getPos($size,$size_2,$config['imglist'][$k]['size']);
//                imagecopymerge($image_1, $image_2, $pos['x'], $pos['y'], 0, 0, imagesx($image_2), imagesy($image_2), 100);
                imagecopy($image_1, $image_2, $pos['x'], $pos['y'], 0, 0, imagesx($image_2), imagesy($image_2));
                imagedestroy($image_2);
            }
        }

//        绘制文字
        if(isset($config['fontlist'])){
            $fontList = $config['fontlist'];
            if(count($fontList)>0){
                foreach ($fontList as $k=>$v){
                    $this->drawFont($image_1,$v);
                }
            }
        }

        $name = $this->genrateName();
        imagepng($image_1, './create/'.$name.'.png');
//        imagedestroy($image_1);
        $this->delTmpFile($delList);
//        header('Content-Type: image/png');
//        imagepng($image_1);die;
        return 'create/'.$name.'.png';
    }

    public function delTmpFile($list)
    {
        foreach ($list as $v){
            unlink ( $v);
        }
    }

//    public function drawFont($font_config)
//    {
//        $txt = $font_config['text'];
//        $txtArr = $this->getTextArr($txt,$font_config['wordwarp']['row'],$font_config['wordwarp']['len'],
//            $font_config['wordwarp']['hangjian'],$font_config['font']['size'],$font_config['wordwarp']['placeholder']
//            );
//
////        var_dump($txtArr);
////        if(count($txtArr)>1){
////            die;
////        }
//        $total_w = $font_config['wordwarp']['len']*$font_config['font']['size'];
//        $total_h = $font_config['wordwarp']['row']*$font_config['font']['size']+($font_config['wordwarp']['row']-1)*$font_config['wordwarp']['hangjian'];
////        echo $total_h.'--'.$total_w;
//        $img = imagecreatetruecolor($total_w, $total_h);
//        //这一句一定要有
//        imagesavealpha($img, true);
//        //拾取一个完全透明的颜色,最后一个参数127为全透明
//        $bg = imagecolorallocatealpha($img, 255, 255, 255, 127);
//        imagefill($img, 0, 0, $bg);
//        $textcolor = imagecolorallocate($img, 0, 0, 255);
//        foreach ($txtArr as $v){
////            $str = iconv('gb2312','utf-8',$v['text']);//解决乱码问题
//            $str = $v['text'];
//            imagettftext($img,16,0,$v['position']['x'],$v['position']['y'],$textcolor,$font_config['font']['style'],$str);
////            imagestring($img,5,$v['position']['x'],$v['position']['y'],$v['text'],$textcolor);
//        }
//        return $img;
//    }

    public function drawFont(&$img,$font_config)
    {
        $txt = $font_config['text'];
        $txtArr = $this->getTextArr($txt,$font_config['wordwarp']['row'],$font_config['wordwarp']['len'],
            $font_config['wordwarp']['hangjian'],$font_config['font']['size'],$font_config['wordwarp']['placeholder']
            );
        if(isset($font_config['font']['color'])){
            $black = imagecolorallocate($img, $font_config['font']['color'][0], $font_config['font']['color'][1], $font_config['font']['color'][2]);
        }else{
            $black = imagecolorallocate($img, 0, 0, 0);
        }

        $font =$font_config['font']['style'];
        $x = $font_config['position']['x'];
        $y = $font_config['position']['y'];
        foreach ($txtArr as $v){
            imagettftext($img, $font_config['font']['size'], 0, $x, $y, $black, $font, $v['text']);
            if(isset($font_config['font']['bold']) && $font_config['font']['bold']){
                imagettftext($img, $font_config['font']['size'], 0, $x+1, $y+1, $black, $font, $v['text']);
            }
        }
    }
    public function getImgObj($path)
    {
//        $type = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        try{
            $info=getimagesize($path);
            if(!$info){
//                echo $path;die;
                return null;
            }
            $ename=explode('/',$info['mime']);
            $ext=$ename[1];
            switch ($ext){
                case 'png':
                    return imagecreatefrompng($path);
                case 'jpg':
                case 'jpeg':
                    return imagecreatefromjpeg($path);
            }
            return imagecreatefrompng($path);
        }catch (\Exception $ex){
            echo $path;
            echo $ex->getMessage();die;
        }

    }

    public function getImgAttr($path,$type = 'wh')
    {
        $info = getimagesize($path);
        if($type == 'w'){
            return $info[0];
        }else if($type == 'h'){
            return $info[1];
        }else{
            return ['w'=>$info[0],'h'=>$info[1]];
        }
    }

    public function radius_img($src_img,$w,$h, $radius = 15) {
        $img = imagecreatetruecolor($w, $h);
        //这一句一定要有
        imagesavealpha($img, true);
        //拾取一个完全透明的颜色,最后一个参数127为全透明
        $bg = imagecolorallocatealpha($img, 255, 255, 255, 127);
        imagefill($img, 0, 0, $bg);
        $r = $radius; //圆 角半径
        for ($x = 0; $x < $w; $x++) {
            for ($y = 0; $y < $h; $y++) {
                $rgbColor = imagecolorat($src_img, $x, $y);
                if (($x >= $radius && $x <= ($w - $radius)) || ($y >= $radius && $y <= ($h - $radius))) {
                    //不在四角的范围内,直接画
                    imagesetpixel($img, $x, $y, $rgbColor);
                } else {
                    //在四角的范围内选择画
                    //上左
                    $y_x = $r; //圆心X坐标
                    $y_y = $r; //圆心Y坐标
                    if (((($x - $y_x) * ($x - $y_x) + ($y - $y_y) * ($y - $y_y)) <= ($r * $r))) {
                        imagesetpixel($img, $x, $y, $rgbColor);
                    }
                    //上右
                    $y_x = $w - $r; //圆心X坐标
                    $y_y = $r; //圆心Y坐标
                    if (((($x - $y_x) * ($x - $y_x) + ($y - $y_y) * ($y - $y_y)) <= ($r * $r))) {
                        imagesetpixel($img, $x, $y, $rgbColor);
                    }
                    //下左
                    $y_x = $r; //圆心X坐标
                    $y_y = $h - $r; //圆心Y坐标
                    if (((($x - $y_x) * ($x - $y_x) + ($y - $y_y) * ($y - $y_y)) <= ($r * $r))) {
                        imagesetpixel($img, $x, $y, $rgbColor);
                    }
                    //下右
                    $y_x = $w - $r; //圆心X坐标
                    $y_y = $h - $r; //圆心Y坐标
                    if (((($x - $y_x) * ($x - $y_x) + ($y - $y_y) * ($y - $y_y)) <= ($r * $r))) {
                        imagesetpixel($img, $x, $y, $rgbColor);
                    }
                }
            }
        }
        return $img;
    }

    public function drawText($text, $conf)
    {

    }

    /** 将字符串拆成数组
     * @param $text 字符串
     * @param $row 行数
     * @param $len 每行字数
     * @param $padding 超出的填充...
     */
    public function getTextArr($text, $row,$len,$hangjian,$font, $padding='...')
    {

        $str_len = mb_strlen($text);
        if($str_len>$len){
            $left = $str_len%$len;
            $str_row = ceil($str_len/$len);
            $res = [];
            for($i=0;$i<$str_row;$i++){
//                echo "text=".$text." start=".($i*$len)."  len=".$len.'  text='.mb_substr($text,$i*$len,$len,'utf8')."<br>";
                $res[] = ['text'=>mb_substr($text,$i*$len,$len,'utf8'),'position'=>[
                    'x'=>0,'y'=>($i)*($hangjian+$font)
                ]];
            }
            $res = array_slice($res,0,$row);
            if($row*$len<$str_len){
                $last_line = $res[$row-1]['text'];
                $res[$row-1]['text'] = mb_substr($last_line,0,$len-2,'utf8').$padding;
            }
            return $res;
        }
        return [['text'=>$text,'position'=>['x'=>0,'y'=>0]]];
    }



    public function getPos($bgSize, $imgSize, $sizeConfig)
    {
        if($sizeConfig['x'] == 'center'){
            $data['x'] = ($bgSize['w'] - $imgSize['w'])/2;
        }else{
            $data['x'] = intval($sizeConfig['x']);
        }
        if($sizeConfig['y'] == 'center'){
            $data['y'] = ($bgSize['h'] - $imgSize['h'])/2;
        }else{
            $data['y'] = intval($sizeConfig['y']);
        }
        return $data;
    }

    public function imageResize($source_image,$source_width,$source_height,$target_width, $target_height)
    {
        $source_ratio  = $source_height / $source_width;
        $target_ratio  = $target_height / $target_width;

        // 源图过高
        if ($source_ratio > $target_ratio)
        {
            $cropped_width  = $source_width;
            $cropped_height = $source_width * $target_ratio;
            $source_x = 0;
            $source_y = ($source_height - $cropped_height) / 2;
        }
        // 源图过宽
        elseif ($source_ratio < $target_ratio)
        {
            $cropped_width  = $source_height / $target_ratio;
            $cropped_height = $source_height;
            $source_x = ($source_width - $cropped_width) / 2;
            $source_y = 0;
        }
        // 源图适中
        else
        {
            $cropped_width  = $source_width;
            $cropped_height = $source_height;
            $source_x = 0;
            $source_y = 0;
        }

        $target_image  = imagecreatetruecolor($target_width, $target_height);
        $bg = imagecolorallocatealpha($target_image, 255, 255, 255, 127);
        imagesavealpha($target_image, true);
        imagefill($target_image, 0, 0, $bg);
        $cropped_image = imagecreatetruecolor($cropped_width, $cropped_height);
        imagesavealpha($cropped_image, true);
        $bg = imagecolorallocatealpha($cropped_image, 255, 255, 255, 127);
        imagefill($cropped_image, 0, 0, $bg);
        // 图片裁剪
        imagecopy($cropped_image, $source_image, 0, 0, $source_x, $source_y, $cropped_width, $cropped_height);
        // 图片缩放
        imagecopyresampled($target_image, $cropped_image, 0, 0, 0, 0, $target_width, $target_height, $cropped_width, $cropped_height);

        imagedestroy($source_image);
        imagedestroy($cropped_image);
        return $target_image;
    }

    public function downloadImg($url,$path, $default='')
    {
        $header = array(
            'User-Agent: Mozilla/5.0 (Windows NT 6.1; Win64; x64; rv:45.0) Gecko/20100101 Firefox/45.0',
            'Accept-Language: zh-CN,zh;q=0.8,en-US;q=0.5,en;q=0.3',
            'Accept-Encoding: gzip, deflate',);
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($curl, CURLOPT_ENCODING, 'gzip');
        curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
        $data = curl_exec($curl);
        $code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);
        if ($code == 200) {//把URL格式的图片转成base64_encode格式的！
            $imgBase64Code = "data:image/jpeg;base64," . base64_encode($data);
        }else{
            return $default;
        }
        $img_content=$imgBase64Code;//图片内容
        //echo $img_content;exit;
        if (preg_match('/^(data:\s*image\/(\w+);base64,)/', $img_content, $result))
        {
            $name = $this->genrateName();
            $type = $result[2];//得到图片类型png?jpg?gif?
            $new_file = $path.$name.'.'.$type;
            $res = file_put_contents($new_file, base64_decode(str_replace($result[1], '', $img_content)));
            if ($res)
            {
                return $new_file;
            }
        }
        return $default;
    }

    public function genrateName()
    {
        return md5(uniqid(rand()));
    }

}
