<?php
namespace library;

class WX {

    public static $access_token = '';
    public static $appid = '';
    public static $appsecret = '';

    public static function getMaterials($type = 'news', $offset = 0, $count = 20) {
        //http://mp.weixin.qq.com/wiki/15/8386c11b7bc4cdd1499c572bfe2e95b3.html
        $url = "https://api.weixin.qq.com/cgi-bin/material/batchget_material?access_token=" . self::$access_token;
        $params = [
            'type' => $type,
            'offset' => $offset,
            'count' => $count,
        ];
        return json_decode(Curl::_($url, json_encode($params, JSON_UNESCAPED_UNICODE), 'post'), 1);
    }

    public static function send($tagid, $media_id, $msgtype = 'mpnews', $is_to_all = false) {
        //http://mp.weixin.qq.com/wiki/15/40b6865b893947b764e2de8e4a1fb55f.html#.E6.A0.B9.E6.8D.AE.E5.88.86.E7.BB.84.E8.BF.9B.E8.A1.8C.E7.BE.A4.E5.8F.91.E3.80.90.E8.AE.A2.E9.98.85.E5.8F.B7.E4.B8.8E.E6.9C.8D.E5.8A.A1.E5.8F.B7.E8.AE.A4.E8.AF.81.E5.90.8E.E5.9D.87.E5.8F.AF.E7.94.A8.E3.80.91
        $url = "https://api.weixin.qq.com/cgi-bin/message/mass/sendall?access_token=" . static::$access_token;
        $params = [
            'filter' => [
                'is_to_all' => $is_to_all,
                'tag_id' => $tagid
            ],
            'mpnews' => [
                'media_id' => $media_id,
            ],
            'msgtype' => $msgtype,
            /**
             * 图文消息被判定为转载时，是否继续群发。
                1为继续群发（转载），0为停止群发。
                该参数默认为0。
             */
            "send_ignore_reprint" => 0,
        ];

        return json_decode(Curl::_($url, json_encode($params, JSON_UNESCAPED_UNICODE), 'post'), 1);
    }

    public static function sendMaterials($tagid, $media_id) {
        $url = "https://api.weixin.qq.com/cgi-bin/message/mass/sendall?access_token=" . static::$access_token;

        $params = [
            "filter" => [
                "is_to_all" => false,
                "tag_id" => $tagid,
            ],
            "mpnews" => [
                'media_id' => $media_id,
            ],
            /**
             * 图文消息被判定为转载时，是否继续群发。
                1为继续群发（转载），0为停止群发。
                该参数默认为0。
             */
            "msgtype" => "mpnews",
            "send_ignore_reprint" => 0,
        ];
        
        return json_decode(Curl::_($url, json_encode($params, JSON_UNESCAPED_UNICODE), 'post'), 1);
    }
    
    

    public static function getAccessToken($appid, $secret) {
        static::$appid = $appid;
        static::$appsecret = $secret;

        $access_token = static::getAccessTokenFromCache();
        if ($access_token) {
            return $access_token;
        }


        $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=" . $appid . "&secret=" . $secret;
        $res = Curl::_($url, '', 'get');
        $ary = json_decode($res, 1);
        $access_token = $ary['access_token'];

        static::setCacheAccessToken($access_token, time() + $ary['expires_in'] - 10);
        return $access_token;
    }
    
    public static function getUsers($openid = ''){
        $url = "https://api.weixin.qq.com/cgi-bin/user/get?access_token=" . static::$access_token;
        if($openid){
            $url .= "&openid=".$openid;
        }
        $params = [];
        return json_decode(Curl::_($url, json_encode($params, JSON_UNESCAPED_UNICODE), 'get'), 1);
    }
    
    public static function createTag($name){
        $url = "https://api.weixin.qq.com/cgi-bin/tags/create?access_token=" . static::$access_token;
        
        $params = [
            "tag" => [
                "name" => $name,
            ]
        ];
        
        return json_decode(Curl::_($url, json_encode($params, JSON_UNESCAPED_UNICODE), 'post'), 1);
    }
    
    public static function updateTag($id, $name){
        $url = "https://api.weixin.qq.com/cgi-bin/tags/update?access_token=" . static::$access_token;
        
        $params = [
            "tag" => [
                "id" => $id,
                "name" => $name,
            ]
        ];
        
        return json_decode(Curl::_($url, json_encode($params, JSON_UNESCAPED_UNICODE), 'post'), 1);
    }
    
    public static function delTag($id){
        $url = "https://api.weixin.qq.com/cgi-bin/tags/delete?access_token=" . static::$access_token;
        
        $params = [
            "tag" => [
                "id" => $id,
            ]
        ];
        
        return json_decode(Curl::_($url, json_encode($params, JSON_UNESCAPED_UNICODE), 'post'), 1);
    }
    
    public static function getTags(){
        $url = "api.weixin.qq.com/cgi-bin/tags/get?access_token=" . static::$access_token;
        
        $params = [
        ];
        
        return json_decode(Curl::_($url, json_encode($params, JSON_UNESCAPED_UNICODE), 'post'), 1);
    }
    
    public static function batchTagging($openids, $tagid){
        $url = "https://api.weixin.qq.com/cgi-bin/tags/members/batchtagging?access_token=" . static::$access_token;
        
        $params = [
            "openid_list" => [
                $openids,
            ],
            "tagid" => $tagid,
        ];
        
        return json_decode(Curl::_($url, json_encode($params, JSON_UNESCAPED_UNICODE), 'post'), 1);
    }
    
    public static function batchUntagging($openids, $tagid){
        $url = "https://api.weixin.qq.com/cgi-bin/tags/members/batchuntagging?access_token=" . static::$access_token;
        
        $params = [
            "openid_list" => [
                $openids,
            ],
            "tagid" => $tagid,
        ];
        
        return json_decode(Curl::_($url, json_encode($params, JSON_UNESCAPED_UNICODE), 'post'), 1);
    }
    
    public static function getUserFromTag($tagid, $next_openid){
        $url = "https://api.weixin.qq.com/cgi-bin/user/tag/get?access_token=" . static::$access_token;
        $params = [
            "tagid" => $tagid,
            "next_openid" => $next_openid,
        ];
        return json_decode(Curl::_($url, json_encode($params, JSON_UNESCAPED_UNICODE), 'post'), 1);
    }
    
    public static function getTagsFromUser($openid){
        $url = "https://api.weixin.qq.com/cgi-bin/tags/getidlist?access_token=" . static::$access_token."&openid=".$openid;
        $params = [
            "openid" => $openid,
        ];
        return json_decode(Curl::_($url, json_encode($params, JSON_UNESCAPED_UNICODE), 'post'), 1);
    }


    public static function getUserFromOpenid($openid){
        $url = "https://api.weixin.qq.com/cgi-bin/user/info?access_token=" . static::$access_token."&openid=".$openid;

        return json_decode(Curl::_($url, json_encode($params, JSON_UNESCAPED_UNICODE), 'get'), 1);
    }
    
    public static function sendTmplMsg($openid, $template_id, $gourl, $data){
        $url = "https://api.weixin.qq.com/cgi-bin/message/template/send?access_token=" . static::$access_token;
        $params = [
            "touser" => $openid,
            "template_id" => $template_id,
            "url" => $gourl,
            "data" =>$data
        ];

        return json_decode(Curl::_($url, json_encode($params, JSON_UNESCAPED_UNICODE), 'post'), 1);
    }

    public static function sendCustomMsg($openid, $mediaid, $cardtype = 'mpnews') {
        $url = "https://api.weixin.qq.com/cgi-bin/message/custom/send?access_token=" . static::$access_token;
        switch ($cardtype){
            case 'wxcard':
                $key = 'card_id';
                break;
            default:
                $key = 'media_id';
                
        }
        $params = [
            "touser" => $openid,
            "msgtype" => $cardtype,
            $cardtype =>
            [
                $key => $mediaid
            ]
        ];
        
//        var_dump($params);exit;

        return json_decode(Curl::_($url, json_encode($params, JSON_UNESCAPED_UNICODE), 'post'), 1);
    }

    public static function getAccessTokenFromCache() {
        $cache = self::loadCache();
        if (!isset($cache[static::$appid]) || $cache[static::$appid]['expires_time'] < time()) {
            return '';
        }

        return $cache[static::$appid]['access_token'];
    }

    public static function loadCache() {
        $file = __DIR__ . '/../runtime/cache/access_token';
        if (!file_exists($file))
            return [];
        return include $file;
    }

    public static function setCacheAccessToken($access_token, $expires_time) {
        $cache = self::loadCache();

        $cache[static::$appid] = [
            'access_token' => $access_token,
            'expires_time' => $expires_time,
        ];

        $str = '<?php return ' . var_export($cache, 1) . '; ?>';
        $file = __DIR__ . '/../runtime/cache/access_token';
        file_put_contents($file, $str);
    }

    public static function addMedia($type, $path, $title, $introduction) {
        $url = "https://api.weixin.qq.com/cgi-bin/material/add_material?access_token=" . static::$access_token . '&type=' . $type;
        $description = [
            'title' => $title,
            'introduction' => $introduction,
        ];

        $params = [
            'description' => json_encode($description),
        ];

        $multi = [
            'media' => $path,
        ];

        return Curl::_($url, $params, 'post', $multi);
    }

    public static function addNews($articles) {
        $url = "https://api.weixin.qq.com/cgi-bin/material/add_news?access_token=" . static::$access_token;
        $params = [
            'articles' => $articles,
        ];

        return json_decode(Curl::_($url, json_encode($params, JSON_UNESCAPED_UNICODE), 'post'), 1);
    }

    public static function mvUserToGroup($openid, $to_groupid) {
        $url = "https://api.weixin.qq.com/cgi-bin/groups/members/update?access_token=" . static::$access_token;
        $params = [
            'openid' => $openid,
            'to_groupid' => $to_groupid,
        ];

        return json_decode(Curl::_($url, json_encode($params, JSON_UNESCAPED_UNICODE), 'post'), 1);
    }

    public static function getGroups() {
        $url = "https://api.weixin.qq.com/cgi-bin/groups/get?access_token=" . static::$access_token;

        return json_decode(Curl::_($url, '', 'get'), 1);
    }

    public static function sendMsgToOpenids($openids, $media_id) {
        $url = "https://api.weixin.qq.com/cgi-bin/message/mass/send?access_token=" . static::$access_token;

        $params = [
            "touser" => [
                $openids
            ],
            "mpnews" => [
                'media_id' => $media_id,
            ],
            "msgtype" => "mpnews",
        ];

        return json_decode(Curl::_($url, json_encode($params, JSON_UNESCAPED_UNICODE), 'post'), 1);
    }

    public static function addGroup($name) {
        $url = "https://api.weixin.qq.com/cgi-bin/groups/create?access_token=" . static::$access_token;

        $params = [
            'group' => [
                'name' => $name
            ]
        ];

        return json_decode(Curl::_($url, json_encode($params, JSON_UNESCAPED_UNICODE), 'post'), 1);
    }

    public static function addCard() {

        $url = "https://api.weixin.qq.com/card/create?access_token=" . static::$access_token;
        $params = [
            'card' => [
                "card_type" => "MEMBER_CARD",
                "member_card" => [
                    'background_pic_url' => 'http://mmbiz.qpic.cn/mmbiz_jpg/zLl2DeZTsMOVRcf6v6VO9nAouFYiczicEPB5eiauMFIsJCPc2rsINicZjBwJPB4BGyEA4TfWvBWv4JWiagibibaELL2ibQ/0?wx_fmt=jpeg',
                    "base_info" => [
                        'logo_url' => "http://mmbiz.qpic.cn/mmbiz_png/zLl2DeZTsMOVRcf6v6VO9nAouFYiczicEPrbsickZVPke12foarabCMShblQduejtEMUaypbzcY5iblnGCD6yzHtBA/0?wx_fmt=png",
                        'brand_name' => '测试0227-2',
                        /**
                         * Code展示类型，
                         * "CODE_TYPE_TEXT"，文本；
                         * "CODE_TYPE_BARCODE"，一维码 ；
                         * "CODE_TYPE_QRCODE"，二维码；
                         * "CODE_TYPE_ONLY_QRCODE",二维码无code显示；
                         * "CODE_TYPE_ONLY_BARCODE",一维码无code显示；
                         */
                        "code_type" => "CODE_TYPE_QRCODE",
                        "title" => "仅显示二维码",
                        /**
                         * 券颜色。按色彩规范标注填写Color010-Color100。详情见获取颜色列表接口http://mp.weixin.qq.com/wiki/19/39f3e3d4d9442ed77aa27257b38eda37.html#.E8.8E.B7.E5.8F.96.E9.A2.9C.E8.89.B2.E5.88.97.E8.A1.A8.E6.8E.A5.E5.8F.A3
                         */
                        "color" => "Color080",
                        "notice" => "这个是测试内容notice",
                        "service_phone" => '15910708920',
                        "description" => "测试-会员卡2\n此会员卡为测试使用，不可用作其他",
                        "date_info" => [
                            "type" => "DATE_TYPE_PERMANENT"
                        ],
                        "sku" => [
                            "quantity" => 100000000,
                        ],
                        /**
                         * 每人可领券的数量限制。默认值为50。
                         */
                        "get_limit" => 1,
                        /**
                         * 是否自定义Code码。填写true或false，默认为false。通常自有优惠码系统的开发者选择自定义Code码，在卡券投放时带入。
                         */
                        "use_custom_code" => false,
                        "can_give_friend" => false,
//                        "custom_url_name" => "立即使用",
//                        "custom_url" => "http://weixin.ka.social-touch.com:30811//lijishiyong.html",
//                        "custom_url_sub_title" => "6个汉字tips",
//                        "promotion_url_name" => "营销入口1",
//                        "promotion_url" => "http://weixin.ka.social-touch.com:30811//yingxiaorukou1.html",
                        /**
                         * 填写true为用户点击进入会员卡时推送事件，默认为false。详情见进入会员卡事件推送
                         */
                        "need_push_on_view" => true,
                        "center_title" => "会员中心",
                        "center_url" => "http://www.baidu.com",
                    ],
                    /**
                     * 显示积分，填写true或false，如填写true，积分相关字段均为必填。
                     */
                    "supply_bonus" => false,
                    /**
                     * 是否支持储值，填写true或false。如填写true，储值相关字段均为必填。
                     */
                    "supply_balance" => false,
                    /**
                     * 会员卡专属字段，表示特权说明。
                     */
                    "prerogative" => " this is just test_prerogative",
                    /**
                     * 设置为true时用户领取会员卡后系统自动将其激活，无需调用激活接口，详情见自动激活。http://mp.weixin.qq.com/wiki/15/de148cc4b5190c80002eaf4f6f26c717.html#.E8.87.AA.E5.8A.A8.E6.BF.80.E6.B4.BB
                     * 则必须调用这个函数设置微信激活字段， 详见 activateuserform 方法
                     */
//                    "wx_activate" => false,
                    
                    /**
                     * 建议开发者activate_url、auto_activate和wx_activate只填写一项。
                     */
                    "auto_activate" => true,
                    /**
                     * 激活url
                     */
//                    "activate_url" => "http://weixin.ka.social-touch.com:30811//activate.php",
                    /**
                     * 自定义会员信息类目，会员卡激活后显示。
                     */
//                    "custom_field1" => [
//                        /**
//                         * 会员信息类目名称。
//                         * FIELD_NAME_TYPE_LEVEL 等级；
//                         * FIELD_NAME_TYPE_COUPON 优惠券；
//                         * FIELD_NAME_TYPE_STAMP 印花；
//                         * FIELD_NAME_TYPE_DISCOUNT 折扣；
//                         * FIELD_NAME_TYPE_ACHIEVEMEN 成就；
//                         * FIELD_NAME_TYPE_MILEAGE 里程。
//                         */
//                        "name_type" => "FIELD_NAME_TYPE_LEVEL",
//                        "url" => "http://weixin.ka.social-touch.com:30811//dengji.html"
//                    ],
                    /**
                     * 自定义会员信息类目，会员卡激活后显示。
                     */
                    "custom_cell1" => [
                        "name" => "我的积分",
                        "tips" => "",
                        "url" => "http://wechat.ka.social-touch.com:30815/wap/points-list"
                    ],
                    "custom_cell2" => [
                        "name" => "我的优惠券",
                        "tips" => "",
                        "url" => "http://wechat.ka.social-touch.com:30815/wap/coupon"
                    ],
                    /**
                     * 积分规则。用于微信买单功能。http://mp.weixin.qq.com/wiki/15/de148cc4b5190c80002eaf4f6f26c717.html#.E8.AE.BE.E7.BD.AE.E5.BE.AE.E4.BF.A1.E4.B9.B0.E5.8D.95.E6.8E.A5.E5.8F.A3
                     */
                    /*
                    "bonus_rule" => [
                        //消费金额。以分为单位。
                        "cost_money_unit" => 100,
                        //对应增加的积分。
                        "increase_bonus" => 1,
                        //用户单次可获取的积分上限。
                        "max_increase_bonus" => 200,
                        //初始设置积分。
                        "init_increase_bonus" => 10,
                        //cost_bonus_unit
                        "cost_bonus_unit" => 5,
                        //抵扣xx元，（这里以分为单位）
                        "reduce_money" => 100,
                        //抵扣条件，满xx元（这里以分为单位）可用。
                        "least_money_to_use_bonus" => 1000,
                        //抵扣条件，单笔最多使用xx积分。
                        "max_reduce_bonus" => 50
                    ],
                    //*/
                    "discount" => 10
                ],
            ]
        ];
        
//        echo json_encode($params, JSON_UNESCAPED_UNICODE);exit;
        return json_decode(Curl::_($url, json_encode($params, JSON_UNESCAPED_UNICODE), 'post'), 1);
    }

    /**
     * 检查会员卡状况
     * @return type
     */
    public static function checkCard($card_id) {
        $url = "https://api.weixin.qq.com/card/get?access_token=" . static::$access_token;

        $params = [
            'card_id' => $card_id,
        ];
        return json_decode(Curl::_($url, json_encode($params, JSON_UNESCAPED_UNICODE), 'post'), 1);
    }

    /**
     * 二维码投放会员卡
     * @return type
     */
    public static function getCardQRCode($card_id, $openid='') {
        $url = "https://api.weixin.qq.com/card/qrcode/create?access_token=" . static::$access_token;
        $params = [
            "action_name" => "QR_CARD",
            /**
             * 指定二维码的有效时间，范围是60 ~ 1800秒。不填默认为永久有效。
             */
//            "expire_seconds" => 1800,
            "action_info" => [
                "card" => [
                    "card_id" => $card_id,
                    //卡券Code码,use_custom_code字段为true的卡券必须填写，非自定义code不必填写。。
                    "code" => "huiyuanka1",
                    "openid" => $openid,
                    //指定下发二维码，生成的二维码随机分配一个code，领取后不可再次扫描。填写true或false。默认false
                    "is_unique_code" => false,
                    //领取场景值，用于领取渠道的数据统计，默认值为0，字段类型为整型，长度限制为60位数字。用户领取卡券后触发的事件推送中会带上此自定义场景值。
                    "outer_id" => 1
                ]
            ]
        ];

        return json_decode(Curl::_($url, json_encode($params, JSON_UNESCAPED_UNICODE), 'post'), 1);
    }
    
    /**
     * 激活会员卡
     */
    public static function activate($params){
        $url = "https://api.weixin.qq.com/card/membercard/activate?access_token=" . static::$access_token;
        

        return json_decode(Curl::_($url, json_encode($params, JSON_UNESCAPED_UNICODE), 'post'), 1);
    }
    
    /**
     * 绑定会员卡激活字段，注册会员卡
     * wx_activate = true ,则必须调用这个函数设置微信激活字段。
     * 
     * @param type $cart_id
     * @param type $required_form
     * @param type $optional_form
     * @return type
     */
    public static function activateuserform($cart_id, $required_form, $optional_form){
        $url = "https://api.weixin.qq.com/card/membercard/activateuserform/set?access_token=" . static::$access_token;
        $params = [
            "card_id" => $cart_id,
            /**
             * 指定二维码的有效时间，范围是60 ~ 1800秒。不填默认为永久有效。
             */
            "required_form" => $required_form,
            "optional_form" => $optional_form
        ];

        return json_decode(Curl::_($url, json_encode($params, JSON_UNESCAPED_UNICODE), 'post'), 1);
    }

    /**
     * 设置会员卡测试白名单
     */
    public static function setCartWhitelist($openids = [], $username = []) {
        $url = "https://api.weixin.qq.com/card/testwhitelist/set?access_token=" . static::$access_token;
        $params = [];
        if ($openids) {
            $params = array_merge($params, [
                "openid" => $openids
            ]);
        }

        if ($username) {
            $params = array_merge($params, [
                "username" => $username
            ]);
        }

//        var_dump($params);exit;
        return json_decode(Curl::_($url, json_encode($params, JSON_UNESCAPED_UNICODE), 'post'), 1);
    }

    public static function getUser($card_id, $code) {
        $url = "https://api.weixin.qq.com/card/membercard/userinfo/get?access_token=" . static::$access_token;
        $params = [
            "card_id" => $card_id,
            'code' => $code
        ];

        return json_decode(Curl::_($url, json_encode($params, JSON_UNESCAPED_UNICODE), 'post'), 1);
    }

    public static function updateUser($params) {
        $url = "https://api.weixin.qq.com/card/membercard/updateuser?access_token=" . static::$access_token;
        
        return json_decode(Curl::_($url, json_encode($params, JSON_UNESCAPED_UNICODE), 'post'), 1);
    }

    public static function getUserCarts($openid) {
        $url = "https://api.weixin.qq.com/card/user/getcardlist?access_token=" . static::$access_token;
        $params = [
            "openid" => $openid,
        ];

        return json_decode(Curl::_($url, json_encode($params, JSON_UNESCAPED_UNICODE), 'post'), 1);
    }

    /**
     * 设置卡券失效
     * @param type $openid
     * @return type
     */
    public static function unavailable($code, $card_id) {
        $url = "https://api.weixin.qq.com/card/code/unavailable?access_token=" . static::$access_token;
        $params = [
            "code" => $code,
            "card_id" => $card_id
        ];

        return json_decode(Curl::_($url, json_encode($params, JSON_UNESCAPED_UNICODE), 'post'), 1);
    }

    /**
     * 设置更新后提示消息
     * @return type
     */
    public static function setUpdateAfter() {
        $url = "https://api.weixin.qq.com/card/update?access_token=" . static::$access_token;
//        $params = [
//            "card_id" => "pWBxTw-kjIly8J_WDT-MhZPHQz_s",
//            "member_card" => [
//                "modify_msg_operation" => [
//                    "url_cell" => [
//                        "end_time" => 1476775800,
//                        "text" => "2016-10-18 15:30:00自动消失",
//                        "url" => "www.qq.com"
//                    ]
//                ]
//            ]
//        ];

        $params = [
            "card_id" => "pWBxTw-kjIly8J_WDT-MhZPHQz_s",
            "member_card" => [
                "modify_msg_operation" => [
                    "card_cell" => [
                        "end_time" => 1476777600,
                        "card_id" => "pWBxTw39pwEsglo4kjo9XxU-qttE",
                    ]
                ]
            ]
        ];

        return json_decode(Curl::_($url, json_encode($params, JSON_UNESCAPED_UNICODE), 'post'), 1);
    }

    public function getCards() {
        $url = "https://api.weixin.qq.com/card/batchget?access_token=" . static::$access_token;
        $params = [
            "offset" => 0,
            "count" => 20,
            /**
             * “CARD_STATUS_NOT_VERIFY”,待审核；
             * “CARD_STATUS_VERIFY_FALL”,审核失败；
             * “CARD_STATUS_VERIFY_OK”，通过审核；
             * “CARD_STATUS_USER_DELETE”，卡券被用户删除；
             * “CARD_STATUS_USER_DISPATCH”，在公众平台投放过的卡券
             */
            "status_list" => ["CARD_STATUS_VERIFY_OK", "CARD_STATUS_DISPATCH", "CARD_STATUS_NOT_VERIFY"]
        ];

        return json_decode(Curl::_($url, json_encode($params, JSON_UNESCAPED_UNICODE), 'post'), 1);
    }

    public function getCardsHtml($card_id) {
        $url = "https://api.weixin.qq.com/card/mpnews/gethtml?access_token=" . static::$access_token;
        $params = [
            "card_id" => $card_id,
        ];

        return json_decode(Curl::_($url, json_encode($params, JSON_UNESCAPED_UNICODE), 'post'), 1);
    }

    public static function 绑定服务器() {
        include __DIR__ . '/wx_server.php';
    }

}
