<?php

namespace app\admin\controller;

use app\common\controller\Backend;
use Exception;
use think\Db;
use think\exception\PDOException;
use think\exception\ValidateException;

/**
 * 客户问题管理
 *
 * @icon fa fa-circle-o
 */
class Customermessage extends Backend
{

    /**
     * Customermessage模型对象
     * @var \app\admin\model\Customermessage
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\Customermessage;

    }

    public function index($ids = null)
    {
        //设置过滤方法
        $this->request->filter(['strip_tags']);
        if ($this->request->isAjax()) {
            //如果发送的来源是Selectpage，则转发到Selectpage
            if ($this->request->request('keyField')) {
                return $this->selectpage();
            }
            list($where, $sort, $order, $offset, $limit) =
                $this->buildparams(['u.username', 'realname', 'is_overcome'], true);

            $total = $this->model
                ->alias('customermessage')
                ->join('user u', 'customermessage.user_id = u.id', 'LEFT')
                ->field('customermessage.*, u.username')
                ->where($where)
                ->count();

            $list = $this->model
                ->alias('customermessage')
                ->join('user u', 'customermessage.user_id = u.id', 'LEFT')
                ->field('customermessage.*, u.username')
                ->where($where)
                ->order($sort, $order)
                ->limit($offset, $limit)
                ->select();

            $list = collection($list)->toArray();

            $result = array("total" => $total, "rows" => $list);

            return json($result);
        }

        return $this->view->fetch();
    }

    public function edit($ids = null)
    {
        if (!$this->request->isPost()) {
//            $autoAnswerList = [
//                '您好，已经通知技术给您补上，今日晚些时候您再看一下。',
//                '添加客服微信：13105717751，让客服发给您。',
//                '添加客服微信：13105717751，让客服给您解决。',
//                '扶贫的申请，升级视频里，已经说了，会在大家都升级完以后，统一开启申请通道。',
//                "1不想下载，点击跳过，或者上划关闭菲梵宝，然后重新进入签到。\n 2可以点击下载，今天的签到结束以后，再直接卸载。",
//                '老师您好，您的建议我们一定采纳，同时也希望您给我们一些时间，让我们去改进，您的宝贵建议，已经反馈给有关部门去改进。',
//                '点击我的，点击退出APP ，重新再打开。',
//                '您的签到方式，可能存在一些问题，点击--签到有礼--签到有问题点这里，看教学视频：点击播放编号是2的视频，学习一下如何快速的签到。',
//                '老师您好 12月份的记录的记录暂时查询不了 已经向有关部门反馈 后续可以查询 请耐心等待。',
//            ];
            // 直接读取配置文件
            $autoAnswerList = config('auto_answer.auto_answer_list');
            $this->view->assign('autoAnswerList', $autoAnswerList);
        } elseif ($this->request->isPost()) {
//            if (3098 == $ids) {
//
//            }
        }
        return parent::edit($ids);
    }
}
