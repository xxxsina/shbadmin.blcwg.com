<?php

namespace app\admin\model;

use think\Cache;
use think\Config;
use think\Model;


class Customermessage extends Model
{
    // 表名
    protected $name = 'customer_message';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'integer';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
    protected $deleteTime = false;

    // 追加属性
    protected $append = [

    ];

    protected static $redis = null;
    protected $redisKeyModel = null;

    protected static function init() {
        self::$redis = Cache::store('redis')->handler();

    }

    public function __construct($data = [])
    {
        parent::__construct($data);
        $this->redisKeyModel = new RedisNameConfigModel();
    }

    // 在Customermessage模型中
    public function getImageAttr($value)
    {
        if (empty($value)) {
            return '';
        }

        return Config::get("web_host") . '/data/images/' . $value;
    }

    public function getVideoAttr($value)
    {
        if (empty($value)) {
            return '';
        }

        return Config::get("web_host") . '/data/videos/' . $value;
    }

    /**
     * @return object|\think\cache\Driver
     * @author LEE
     * @Date 2025-07-15 11:49
     */
    private function ORedis()
    {

    }

    public function cacheCustomerMessage($userId, $msgId, $param)
    {
        $msg = $this->get($msgId);
        $key = $this->redisKeyModel->getCustomerMessagesKey($userId, $msgId);
        $result = self::$redis->hgetAll($key);
        if (!empty($result)) {
            if (isset($param['user_id'])) unset($param['user_id']);
            if (!isset($param['answer_image'])) $param['answer_image'] = $msg->answer_image;
            if (!isset($param['answer_video'])) $param['answer_video'] = $msg->answer_video;
            self::$redis->hMSet($key, $param);
        }

        return null;
    }
}