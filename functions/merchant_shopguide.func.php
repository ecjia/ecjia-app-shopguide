<?php

    /*
     * 获取店铺基本信息
     */
    function get_merchant_info(){
        if(empty($_SESSION['store_id'])){
            return array();
        }

        $data = array(
            'shop_nav_background'		=> '', //店铺导航背景图
            'shop_logo'                 => '', // 默认店铺页头部LOGO
            'shop_banner_pic'           => '', // banner图
            'shop_trade_time'           => '', // 营业时间
            'shop_description'          => '', // 店铺描述
            'shop_notice'               => '', // 店铺公告
        );

        $data = get_merchant_config('', $data);
        $shop_time = unserialize($data['shop_trade_time']);
        unset($data['shop_trade_time']);
        $data['shop_trade_time'] = implode(',', $shop_time);
        $data['shop_nav_background']= !empty($data['shop_nav_background'])? RC_Upload::upload_url($data['shop_nav_background']) : '';
        $data['shop_logo']          = !empty($data['shop_logo'])? RC_Upload::upload_url($data['shop_logo']) : '';
        $data['shop_banner_pic']    = !empty($data['shop_banner_pic'])? RC_Upload::upload_url($data['shop_banner_pic']) : '';
        return $data;

        // $data['shop_kf_logo'] = ($data['shop_kf_type'] == 1)? 1 : 2;

        // $data 键
        // 'shop_title'                => '', // 店铺标题
        // 'shop_kf_mobile'            => '', // 客服手机号码
        // 'shop_kf_email'             => '', // 客服邮件地址
        // 'shop_kf_qq'                => '', // 客服QQ号码
        // 'shop_kf_ww'                => '', // 客服淘宝旺旺
        // 'shop_kf_online_ident'      => '', // 在线客服账号
        // 'shop_kf_appkey'            => '', // 在线客服appkey
        // 'shop_kf_secretkey'         => '', // 在线客服secretkey
        // 'shop_kf_logo'              => '', // 在线客服头像LOGO
        // 'shop_kf_welcomeMsg'        => '', // 在线客服欢迎信息
        // 'shop_kf_type'              => '', // 客服样式
        // 'shop_qrcode_logo'          => '', // 二维码中间Logo
        // 'shop_thumb_logo'           => '', // Logo缩略图
        // 'shop_front_logo'           => '', // 店铺封面图

        // $data['shop_thumb_logo']    = !empty($data['shop_thumb_logo'])? RC_Upload::upload_url($data['shop_thumb_logo']) : '';
        // $data['shop_qrcode_logo']   = !empty($data['shop_qrcode_logo'])? RC_Upload::upload_url($data['shop_qrcode_logo']) : '';
        // $data['shop_front_logo']    = !empty($data['shop_front_logo'])? RC_Upload::upload_url($data['shop_front_logo']) : '';
        // $data['shop_kf_logo']       = !empty($data['shop_kf_logo'])? RC_Upload::upload_url($data['shop_kf_logo']) : '';

    }

    /*
     * 获取店铺配置信息
     */
    function get_merchant_config($code, $arr){
        $merchants_config = RC_Model::model('merchant/merchants_config_model');
        if(empty($code)){
            if(is_array($arr)){
                $config = RC_DB::table('merchants_config')->where('store_id', $_SESSION['store_id'])->select('code','value')->get();
                foreach ($config as $key => $value) {
                    $arr[$value['code']] = $value['value'];
                }
                return $arr;
            }else{
                return ;
            }
        }else{
            $config = $merchants_config->where(array('store_id' => $_SESSION['store_id'], 'code' => $code))->get_field('value');
            return $config;
        }
    }

    /*
     * 上传图片
     *  @param string $path 上传路径
     *  @param string $code 接收图片参数
     *  @param string $old_images 旧图片
     */
    function file_upload_info($path, $code, $old_images){
        $code = empty($code)? $path : $code;
        $upload = RC_Upload::uploader('image', array('save_path' => 'merchant/'.$_SESSION['store_id'].'/data/'.$path, 'auto_sub_dirs' => true));
        $file = $_FILES[$code];

        if (!empty($file)&&((isset($file['error']) && $file['error'] == 0) || (!isset($file['error']) && $file['tmp_name'] != 'none'))) {
            // 检测图片类型是否符合
            if (!$upload->check_upload_file($file)){
               return ecjia_admin::$controller->showmessage($upload->error(), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
            }else{
                $image_info = $upload->upload($file);
                if (empty($image_info)) {
                	return ecjia_admin::$controller->showmessage($upload->error(), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
                }
                // 删除旧的图片
                if (!empty($old_images)) {
                   $upload->remove($old_images);
                }
                $img_path = $upload->get_position($image_info);
            }

            return $img_path;
        }
    }

    /*
     * 设置店铺配置信息
     */
    function set_merchant_config($code, $value, $arr){
        $merchants_config = RC_Model::model('merchant/merchants_config_model');
        if(empty($code)){
            if(is_array($arr)){
                foreach ($arr as $key => $val) {
                    $count = $merchants_config->where(array('store_id' => $_SESSION['store_id'], 'code' => $key))->count();
                    if(empty($count)){
                        $merchants_config->insert(array('store_id' => $_SESSION['store_id'], 'code' => $key, 'value' => $val));
                    }else{
                        $merchants_config->where(array('store_id' => $_SESSION['store_id'], 'code' => $key))->update(array('value' => $val));
                    }
                }
                return true;
            }else{
                return new ecjia_error(101, '参数错误');
            }
        }else{
            $count = $merchants_config->where(array('store_id' => $_SESSION['store_id'], 'code' => $code))->count();
            if(empty($count)){
                $merchants_config->insert(array('store_id' => $_SESSION['store_id'], 'code' => $code, 'value' => $value));
            }else{
                $merchants_config->where(array('store_id' => $_SESSION['store_id'], 'code' => $code))->update(array('value' => $value));
            }
            return true;
        }
    }

    /*
     * 获取地区名称
     */
    function get_region_name($id){
        $db_region = RC_Model::model('merchant/region_model');
        return $db_region->where(array('region_id' => $id))->get_field('region_name');
    }

    /*
     * 管理员操作对象和动作
     */
    function assign_adminlog_contents(){
        ecjia_admin_log::instance()->add_object('merchant', '我的店铺');
    }

// end
