<?php

namespace app\admin\model;

use think\Model;

class RedisNameConfigModel extends Model
{
    public function getCustomerMessagesKey($userId, $msgId)
    {
        return sprintf("shb:customer_messages:userId:%s:msgId:%s", $userId, $msgId);
    }
}
