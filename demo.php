$config = [
                'background'=>'./image/bg.png',
                'font'=>['size'=>'24','style'=>''],
                'imglist'=>[

                    [
                        'path'=>$userInfo->headimg,
                        'size'=>['x'=>56,'y'=>287,'w'=>120,'h'=>120],
                        'style'=>'circle'  //圆角图片
                    ],
                    [
                        'path'=>'./image/head.png',
                        'size'=>['x'=>62,'y'=>258],
                        'style'=>'normal'  //圆角图片
                    ],
                    [
                        'path'=>'./qrcode_t/'.$filename,
                        'size'=>['x'=>492,'y'=>820,'w'=>142,'h'=>142],
                        'style'=>'normal'  //圆角图片
                    ],
                ],
                'fontlist'=>[
                    [
                        'text'=>$userInfo->nickname,
                        'position'=>['x'=>222,'y'=>310],
                        'wordwarp'=>['len'=>5,'row'=>3,'placeholder'=>'...','hangjian'=>5],
//                    'font'=>['size'=>23,'style'=>'./image/PingFang.ttc','bold'=>true]
                        'font'=>['size'=>23,'style'=>'./image/PingFang.ttc']
                    ],
                    [
                        'text'=>'回答了'.$num.'道题',
                        'position'=>['x'=>222,'y'=>355],
                        'wordwarp'=>['len'=>15,'row'=>3,'placeholder'=>'...','hangjian'=>5],
                        'font'=>['size'=>23,'style'=>'./image/PingFang.ttc']
                    ]
                ],
            ];
            $model = new MergeImage();
            $name = $model->genrateActiveImg($config);
