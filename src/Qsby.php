<?php
namespace qmq\qsby;

use Requests;
use Spatie\ArrayToXml\ArrayToXml;
use think\helper\Arr;

/**
 * 产生回调通知的接口 详细见文档
 * 3.4 移动参会者【走廊与会议室】接口 afterMoveLocation
 * 3.8 录音接口 afterRecord
 * 3.12 会议状态notify接口 需调用3.1后 全时会通知回调地址 afterConfState
 * 3.13 与会者状态notify接口 参会人入会或退出会议，通过异步通知消息告诉用户 afterPartyState
 */

class Qsby
{
    const URL = "https://ztcj.baiying.quanshi.com";
    private $pwd = "";
    private $accountPwd = ""; //32位md5密码
    /** 错误提示语一 */
    private $tipErrors1 = [
        "0" => "账号校验成功",
        "1" => "账号或站点错误",
        "2" => "数据库异常",
        "3" => "服务器端处理失败",
        "4" => "参数错误",
        "6" => "开始时间早于现在时间",
        "7" => "会议时长小于 1 分钟或大于 720 分钟",
        "8" => "参会方数小于 2 或大于 200",
        "9" => "获取会议密码失败",
        "12" => "修改后的会议开始时间早于现在时间",
        "13" => "修改后的会议时长小于 1 分钟或大于 720 分钟",
        "14" => "修改后的会议参会方数小于 2 或大于 200",
        "15" => "会议 ID 为 conferenceId 的会议不是未召开状态",
        "16" => "会议不存在",
        "17" => "会议状态为“未开始”",
        "18" => "会议状态为“进行中”(会中休息状态也为进行中)",
        "19" => "会议状态为“已结束”",
        "20" => "会议状态为“已过期”",
        "21" => "距离会议开始时间大于 30 分钟",
        "22" => "开始时间比结束时间晚",
        "23" => "会议没有录音",
        "24" => "会议状态是被取消的",
        "25" => "会议有录音",
    ];
    /** 错误提示语二 */
    private $tipErrors2 = [
        "0" => "账号校验成功",
        "1" => "账号或站点错误",
        "2" => "数据库异常",
        "3" => "会议时间尚未开始或已经过期",
        "4" => "服务器端处理失败",
        "5" => "参数错误",
        "11" => "该场会议未录音",
        "12" => "录音正在进行中",
        "13" => "录音生成中",
    ];

    /** 错误信息 */
    private $error;

    public function __construct($pwd)
    {
        $this->pwd = $pwd;
        $this->accountPwd = md5($pwd);
    }

    /**
     * 2.1 账号校验接口
     *
     * 校验站点以及帐号是否存在、是否对应并反馈校验结果
     *
     * @return bool
     * @author 秋月 414111907@qq.com
     */
    public function checkPwd()
    {
        $url = "/services/login/login/";
        $params = [
        ];
        $res = $this->get($url, $params);
        return $res ? true : false;
    }

    /**
     * 2.2 安排会议接口
     *
     * 通过调用账号校验接口检验账号正确性、生成会议并反馈结果信息
     *
     * @param array $params
     * 以下是params必填项
     * @param string $confName 会议主题
     * @param string $dateStart 开始时间 yyyy-MM-dd
     * @param string $timeStartH 开始时间小时 01~23
     * @param string $timeStartM 开始时间分钟 00、05~55
     * @param string $confDuration 会议时长 1、2、3~720
     * @param string $partMax 参会最大方数 2、3、4~200 代码中默认给200
     *
     * 以下是params选项项
     * @param string $founder 发起人 不能超过50个字
     * @param string $founderPhone 发起人电话 必须是数字
     * @param string $aheadTime 参会人可提前入会时间 05、10~60 默认30
     * @param string $ifNeedAccount 分账信息标识 0:不需要 1:需要
     * @param Array $dispartBillList 分账信息
     * @param string $overTime 是否自动延时 0:自动延长，1:手动延长(会议结束前5分钟后播放提示音 按*号延长15分钟...) 8:手动延长(播放提示音：会议还有5分钟结束)
     * @param string $ifNeedArtificial 是否需要人工协助 0:不需要 1:需要 当需要时 $founder与$founderPhone必填
     * @param string $ifSubscribe 是否订阅 0:不订阅 1:订阅
     * @param string $needNotify 是否需要回调状态 0:不需要 1:需要
     * @param string $returnUrl 监控返回结果的客户端地址  客户接收全时返回的通知消息的地址，在会中唯一且固定不变
     *
     * dispartBillList字段 必填
     * @param string $dispartId 分帐id 必须是数字且唯一
     * @param string $content 分帐条目 不超过100字符 不要有特殊符号
     * @return array
     *
     * 以下是 返回array中的键
     * @param string aheadtime 提前入会时间  30
     * @param string beforetimecall 提前外呼分钟 0
     * @param string beginhour 开始时间-小时  10
     * @param string beginminute 开始时间-分钟 0
     * @param string billingcode 会议BillingCode 79107999
     * @param string confdate 会议日期 2020-04-26T00:00:00+08:00
     * @param string conferencecode 会议BillingCode 30092013
     * @param string conferenceid 会议ID 9838164
     * @param string conferencename 会议标题 测试会议
     * @param string confstatus 会议状态 0:正常会议 -1:取消会议 9:已结束会议 0
     * @param string conftype 会议类型 2:预约会议 2
     * @param string duration 会议时长(分钟) 30
     * @param string guestpasscode 参会人密码 826273
     * @param string hostpasscode 主持人密码 486024
     * @param string iffixedcode 是否固定密码 0:一次性密码 1:固定密码 0
     * @param string ifneedpin 是否需要PIN 0:否 1:是 0
     * @param string iftimecall 是否定时外呼 0:否 1:是 0
     * @param string jointype 加入会议类型 0:自行拨号入会为主 1:统一外呼入会为主 0
     * @param string partiesmax 最大方数 5
     * @param string ifwechat 不在文档中？ 0
     * @param string ifneeddartificial 返回值不在文档中？ 0
     * @param string createtime 返回值不在文档中 2020-04-16T09:19:41+08:00
     * @param string updatetime 不在文档中？ 2020-04-16T09:19:41+08:00
     * @author 秋月 414111907@qq.com
     */
    public function confReserve($params)
    {
        $url = "/services/confReserve/reserve";
        $defalut = [
            "partMax" => 200,
        ];
        $params = array_merge($defalut, $params);
        $xml = $this->arrayToXml($params, 'confReserveParams');

        $result = $this->postxml($url, $xml);
        if (!$result) {
            return false;
        }
        return Arr::get($result, 'confReserveEntity', []);
    }

    /**
     * 2.3 变更会议接口
     *
     * 通过调用账号校验接口检验账号正确性、变更会议并反馈结果信息
     *
     * @param array $params
     *
     * 以下是params必填项
     * @param string $conferenceId 会议ID 长度不能超过18
     * @param string $confName 会议主题 不能超过100个字
     * @param string $dateStart 开始时间日期 yyyy-MM-dd
     * @param string $timeStartH 开始时间小时 01、02~23
     * @param string $timeStartM 开始时间分钟 00、05~55
     * @param string $confDuration 会议时长 1、2~720
     * @param string $partMax 参会方数 2、3~200
     *
     *
     * 以下是params选项项
     * @param string $founder 发起人 不能超过50个字
     * @param string $founderPhone 发起人电话 必须是数字
     * @param string $aheadTime 参会人可提前入会时间 05、10~60 默认30
     * @param string $ifNeedAccount 分账信息标识 0:不需要 1:需要
     * @param Array $dispartBillList 分账信息
     * @param string $overTime 是否自动延时 0:自动延长，1:手动延长(会议结束前5分钟后播放提示音 按*号延长15分钟...) 8:手动延长(播放提示音：会议还有5分钟结束)
     * @param string $ifNeedArtificial 是否需要人工协助 0:不需要 1:需要 当需要时 $founder与$founderPhone必填
     * @return bool
     * @author 秋月 414111907@qq.com
     */
    public function confModify($params)
    {
        $url = "/services/confModify/modify";
        $defalut = [
        ];
        $params = array_merge($defalut, $params);
        $xml = $this->arrayToXml($params, 'confModifyParams');
        $result = $this->postxml($url, $xml);
        return $result ? true : false;
    }

    /**
     * 2.5 会议状态查询接口
     *
     * 查询会议状态 只对-1，0，7，8，9 这几个状态  不在当中则认为会议不存在
     *
     * @param array $ids 会议ID数组
     *
     * @return array
     *
     * 返回值数组结构
     * @param string $conferenceId 会议ID
     * @param string $conferenceStatus 会议状态
     * @param string $confIsHasRecord 似乎也是会议状态
     * @author 秋月 414111907@qq.com
     */
    public function confStatus($ids = [])
    {
        $url = "/services/confStatus/batchConfStatus";
        $params = [
            "conferenceIds" => $ids,
        ];
        $xml = $this->arrayToXml($params, 'confStatusParams');
        $result = $this->postxml($url, $xml);
        if (!$result) {
            return false;
        }
        return Arr::get($result, "confStatusEntity.conference");
    }

    /**
     * 2.6 会议取消接口
     *
     * @param string $id 会议ID
     * @return bool
     * @author 秋月 414111907@qq.com
     */
    public function confDelete($id)
    {
        $url = "/services/confDelete/";
        $params = [
            'conferenceId' => $id,
        ];
        $res = $this->get($url, $params);
        return $res ? true : false;
    }

    /**
     * 2.7 会后报告接口
     *
     * @param string $id 会议ID
     * @return bool|string 返回报告页面url
     * @author 秋月 414111907@qq.com
     */
    public function confReport($id)
    {
        $url = "/services/confReport/";
        $params = [
            'conferenceId' => $id,
        ];
        $res = $this->get($url, $params);
        return Arr::get($res, 'confReportEntity', false);
    }

    /**
     * 2.8 会议监控接口
     *
     * @param string $id 会议ID
     * @param string $code 会议的billingCode
     * @return bool|string 监控页面 url 地址
     * @author 秋月 414111907@qq.com
     */
    public function confMonitor($id, $code)
    {
        $url = "/services/confMonitor/";
        $params = [
            'conferenceId' => $id,
            'conferenceCode' => $code,
        ];
        $res = $this->get($url, $params);
        return Arr::get($res, 'confMonitorEntity', false);
    }

    /**
     * 2.9 会议录音下载页面接口
     *
     * @param string $id 会议ID
     * @return bool|string
     * @author 秋月 414111907@qq.com
     */
    public function confRecordDownload($id)
    {
        $url = "/services/confRecordDownload/";
        $params = [
            'conferenceId' => $id,
        ];
        $res = $this->get($url, $params);
        return Arr::get($res, 'confRecordDownloadEntity', false);
    }

    /**
     * 2.10 会议列表接口
     *
     * @param array $params
     *
     * @param array $conferenceIds 会议ID数组 可以为空
     * @param array $timeSlot 会议所属日期段 最多两个值 为空时查找所有会议 1个值时,指定日期之后的会议 2个值时 时间段内的会议 日期格式 yyyy-MM-dd HH:mm:ss
     * @param array $conferenceStatus 会议状态数组 -1:取消会议 0:正常会议 1:需要审批会议 7:已过期会议 8:正在召开会议 9:已结束会议 不设置返回所有状态
     * @return bool|array 返回值格式参照创建会议返回值
     * @author 秋月 414111907@qq.com
     */
    public function confList($params = [])
    {
        $url = "/services/confList/getConfList";
        $xml = $this->arrayToXml($params, 'conferenceListParams');
        $res = $this->postxml($url, $xml);
        if (!$res) {
            return false;
        }

        $list = Arr::get($res, 'conferenceListEntity.conference', []);
        if (Arr::has($list, 'conferenceid')) {
            return [$list];
        } else {
            return $list;
        }
    }

    /**
     * 2.11 会议监控项设置接口
     *
     * @param [type] $params
     * 以下是params结构 N:未设置 Y:设置
     * @param string $isQAndA 是否设置“问答”功能
     * @param string $isVote 是否设置“投票”功能
     * @param string $isGroupDiscussion 是否设置“分组讨论”功能
     * @param string $isPlayConferenceAudio 是否设置“插播会场语音”功能
     * @param string $isAutoCalltracking 是否设置“自动追呼”功能
     * @return bool
     * @author 秋月 414111907@qq.com
     */
    public function confFunction($params = [])
    {
        $url = "/services/confFunction/saveConfFuntion";
        $defalut = [
            'isQAndA' => "N",
            'isVote' => "N",
            'isGroupDiscussion' => "N",
            'isPlayConferenceAudio' => "N",
            'isAutoCalltracking' => "N",
        ];
        $params = array_merge($defalut, $params);
        $xml = $this->arrayToXml($params, 'setConfFunctionParams');
        $res = $this->postxml($url, $xml);
        return $res ? true : false;
    }

    /**
     * 2.12 会议平台归属查询接口
     *
     * @param string $code 会议BillingCode 必须为数字
     * @return bool|string  summit7  平台地[不知道什么意思]
     * @author 秋月 414111907@qq.com
     */
    public function getplatform($code)
    {
        $url = "/services/summit/getplatform";
        $params = [
            'conferenceCode' => $code,
        ];
        $xml = $this->arrayToXml($params, 'confParams');
        $res = $this->postxml($url, $xml);
        return Arr::get($res, 'platform', false);
    }

    /**
     * 3.1 订阅监控接口
     *
     * 客户通过接口订阅对某一场会议的监控，服务器开始对该会议进行监控并返回状态。注意此接口只能在会议开始前提前时间范围内使用。一场会议，如果订阅多个客户端，则多个客户端多可以监控本场会议，以返回地址完全匹配区分。
     *
     *
     * @param array $params
     * 以下是$params结构
     * @param string $hostPwd 会议密码hostpasscode
     * @param string $conferenceId 会议ID
     * @param string $needNotify 是否需要回调状态 当不需要时，不会收到其他任 何消息的回调函数和 notify 消息的通知 1:需要 0:不需要
     * @param string $returnUrl 监控返回结果的客户端地址 客户接收全时返回的通知消息的地址，在会中唯一且固定不变
     * @return bool|string 唯一客户端id
     * @author 秋月 414111907@qq.com
     */
    public function subscribe($params)
    {
        $url = '/services/monitor/subscribe';
        $res = $this->postjson($url, $params);
        if (!$res) {
            return false;
        }
        return Arr::get($res, 'clientId');
    }

    /**
     * 3.2 走廊外呼接口
     *
     * 客户通过该接口，可以使主持人在在走廊里外呼一个参会者，可以单独听到外呼的参会者的提示音，并且不影响会议中其他参与人。也可以将已经在会议中的一个主持人和一个参会人移到走廊中通话
     * @param array $params
     * 以下是$params结构
     * @param string $clientId 唯一客户端id 3.1接口获取
     * @param string $hostPwd 会议密码hostpasscode
     * @param string $conferenceId 会议ID
     * @param string $phone 电话号码 国家码要用() 只能传递没有外呼的号码，已外呼的需要传递ID 国际码+区号+电话，比如 固 话 (86)1059933636，(86)1059933636-3636 手机(86)138****6666
     * @param string $name 参会人名称
     * @param string $callId 已经外呼过的参会人ID  此参数和 phone 字段只能二选一
     * @param string $location 外呼接通后的处理 0保留在外呼走廊里 1返回主会议室
     * @return bool
     * @author 秋月 414111907@qq.com
     */
    public function hallwayCallout($params)
    {
        $url = "/services/monitor/hallwayCallout";
        $res = $this->postjson($url, $params);
        return $res ? true : false;
    }

    /**
     * 3.3 外呼接口
     *
     * 客户通过该接口，外呼一个或者多个参会者加入会议
     *
     * @param array $params
     * 以下是$params结构
     * @param string $clientId 唯一客户端id 3.1接口获取
     * @param string $hostPwd 会议密码hostpasscode
     * @param string $conferenceId 会议ID
     * @param string $ifSubscribe 是否订阅 1 订阅，0 不订阅
     * @param array $partyList 传递多个电话号码，最多不超过 50 个电话
     * 以下是$partyList结构
     * @param string $phone 国际码+区号+电话
     * @param string $role 入会角色 1:主持人 0:参与人
     * @param string $name 参会人姓名
     * @return bool
     * @author 秋月 414111907@qq.com
     */
    public function callout($params)
    {
        $url = "/services/monitor/callout";
        $res = $this->postjson($url, $params);
        return $res ? true : false;
    }

    /**
     * 3.4 移动参会者【走廊与会议室】接口
     *
     * 客户通过该接口，可以将在会议中的参会者移动到听音乐状态，也可以将听音乐状态的参会者返回会议室。 还可以将在走廊的主持人或者参与人，一起返回会议。到音乐状态相当于禁言禁听。
     *
     * @param array $params
     * 以下是$params结构
     * @param string $clientId 唯一客户端id 3.1接口获取
     * @param string $hostPwd 会议密码hostpasscode
     * @param string $conferenceId 会议ID
     * @param string $location 移动的目标位置  1 为主会议室，2 为听音乐状态
     * @param array $partyList 传递多个参会人ID 最多50个
     * @return bool 调用接口成功并不代表操作成功见回调
     * @author 秋月 414111907@qq.com
     */
    public function moveLocation($params)
    {
        $url = "/services/monitor/moveLocation";
        $res = $this->postjson($url, $params);
        return $res ? true : false;
    }

    /**
     * 3.5 挂断参会者接口
     *
     * 客户通过该接口，可以将在会议中的参会者从会中挂断并移除
     *
     * @param array $params
     * 以下是$params结构
     * @param string $clientId 唯一客户端id 3.1接口获取
     * @param string $hostPwd 会议密码hostpasscode
     * @param string $conferenceId 会议ID
     * @param array $partyList 传递多个参会人ID 最多50个
     * @return bool
     * @author 秋月 414111907@qq.com
     */
    public function hangup($params)
    {
        $url = "/services/monitor/hangup";
        $res = $this->postjson($url, $params);
        return $res ? true : false;
    }

    /**
     * 3.6 查询参会者状态接口
     *
     * 客户通过该接口，可以查询一个或者多个参会人在会中的状态
     *
     * @param array $params
     * 以下是$params结构
     * @param string $hostPwd 会议密码hostpasscode
     * @param string $conferenceId 会议ID
     * @param array $partyList 传递多个参会人ID 最多50个
     * @return bool|array
     * 以下是返回值结构
     * @param string $callId 参会人ID
     * @param string $phone 号码
     * @param string $role 入会角色 1:主持人 0:参与人
     * @param string $connectState 入会状态 0:挂断 1:挂断中 2:正在连接 3:会中等待 4:静音 5:可听可讲 6:未加入 7:*0 服务中
     * @param string $disconnectReason 1:被主持人挂断 2:远端挂断 3:无应答 4:线路忙 5:其他 6:未知原因 7:会议方数满
     * @param string $joinTime 猜测是加入时间 但示例返回null
     * @author 秋月 414111907@qq.com
     */
    public function partyStatus($params)
    {
        $url = "/services/monitor/partyStatus";
        $res = $this->postjson($url, $params);
        if (!$res) {
            return false;
        }
        return Arr::get($res, 'partyList', []);
    }

    /**
     * 3.7 播放会中语音接口
     *
     * 客户通过该接口，可以将在会议中的播放一段系统或者预先系统设置好的客户音乐。此调用到最终播放，大约有 20 多秒的时长，所以如果想一分钟播放，需要在提前 1’30”时调用
     *
     * @param array $params
     * 以下是$params结构
     * 必填
     * @param string $hostPwd 会议密码hostpasscode
     * @param string $conferenceId 会议ID
     * @param string $status 开启或者关闭音乐 0:关闭，1:开启
     * @param string $playDuration 播放时长 单位秒 最小值为 1
     * 选填
     * @param string $musicId 音乐线ID,可以空，表示用系统默认的，个性化的 ID 由全时负债提供
     * @return bool
     * @author 秋月 414111907@qq.com
     */
    public function playMusic($params)
    {
        $url = "/services/monitor/playMusic";
        $defalut = [
            "musicId" => "",
        ];
        $params = array_merge($defalut, $params);
        $res = $this->postjson($url, $params);
        return $res ? true : false;
    }

    /**
     * 3.8 录音接口
     *
     * 客户通过该接口，开启或者关闭会议录音
     *
     * @param array $params
     * 以下是$params结构
     * @param string $clientId 唯一客户端id 3.1接口获取
     * @param string $hostPwd 会议密码hostpasscode
     * @param string $conferenceId 会议ID
     * @param string $status 控制开启和关闭录音 0:关闭，1:开启
     * @return bool 这里成功只是调用接口成功并不代表操作成功 具体见回调
     * @author 秋月 414111907@qq.com
     */
    public function record($params)
    {
        $url = "/services/monitor/record";
        $res = $this->postjson($url, $params);
        return $res ? true : false;
    }

    /**
     * 3.9 参会者静音接口
     *
     * 客户通过该接口，可以将在会议中的一个或多个参会者静音或者解除静音。
     *
     * @param array $params
     * 以下是$params结构
     * @param string $clientId 唯一客户端id 3.1接口获取
     * @param string $hostPwd 会议密码hostpasscode
     * @param string $conferenceId 会议ID
     * @param string $type 操作类型 0:静音 1:取消静音
     * @param array $partyList 参会者callid数组 最大50个
     * @return bool
     * @author 秋月 414111907@qq.com
     */
    public function muteparty($params)
    {
        $url = "/services/monitor/muteparty";
        $res = $this->postjson($url, $params);
        return $res ? true : false;
    }

    /**
     * 3.10 结束会议接口
     *
     * 客户通过该接口，结束本次会议
     *
     * @param array $params
     * 以下是$params结构
     * @param string $hostPwd 会议密码hostpasscode
     * @param string $conferenceId 会议ID
     * @param string $status 关闭方式 1:直接关闭本次会议 2:关闭且删除会议(只对预约类型生效)
     * @return bool
     * @author 秋月 414111907@qq.com
     */
    public function closeConf($params)
    {
        $url = "/services/monitor/closeConf";
        $res = $this->postjson($url, $params);
        return $res ? true : false;
    }

    /**
     * 3.11 会中修改会议属性接口
     *
     * 客户通过该接口，可以修改会中的属性，包括会议名称，时长，提示音等
     *
     * @param array $params
     * 以下是$params结构
     * @param string $hostPwd 会议密码hostpasscode
     * @param string $conferenceId 会议ID
     * @param array $fieldList 传递要修改的字段和值
     * @param array $timerList
     *
     * 以下是$fieldList结构
     * @param string fieldName 要修改的字段名 duration
     * @param string value 要修改的值 15
     * 以下是$timerList结构
     * @param string time
     * @param string timerAction 1播放提示音，2 播放提示音到点结束会议
     * @param string actionValue 提示音 ID
     * @return bool
     * @author 秋月 414111907@qq.com
     */
    public function alterConf($params)
    {
        $url = "/services/monitor/alterConf";
        $res = $this->postjson($url, $params);
        return $res ? true : false;
    }

    /**
     * 3.14 下载录音接口
     *
     * 客户通过该接口，可以下载某场会议的录音。
     *
     * @param array $params
     * 以下是$params结构
     * @param string $hostPwd 会议密码hostpasscode
     * @param string $conferenceId 会议ID
     * @return array 录音下载地址集合 一场会议可能有多个录音
     * @author 秋月 414111907@qq.com
     */
    public function downloadRecord($params)
    {
        $url = "/services/monitor/downloadRecord";
        $res = $this->postjson($url, $params);
        if (!$res) {
            return false;
        }
        return Arr::get($res, 'recordUrlList', []);
    }

    /**
     * 3.15 会场静音接口
     *
     * 客户通过该接口，可以将在会议中所有参会者静音或者解除静音
     *
     * @param array $params
     * 以下是$params结构
     * @param string $clientId 唯一客户端id 3.1接口获取
     * @param string $hostPwd 会议密码hostpasscode
     * @param string $conferenceId 会议ID
     * @param string $type 操作类型 0:静音 1:取消静音
     * @return bool
     * @author 秋月 414111907@qq.com
     */
    public function muteConf($params)
    {
        $url = "/services/monitor/muteConf";
        $res = $this->postjson($url, $params);
        return $res ? true : false;
    }

    /**
     * 3.16 获取会议在线人员列表接口
     *
     * 客户通过该接口，可以获取当前会议所有在线参会人信息
     *
     * @param array $params
     * 以下是$params结构
     * @param string $hostPwd 会议密码hostpasscode
     * @param string $conferenceId 会议ID
     * @param string $type 获取类型 0:获取在线人员集合和人数 1:只获取在线人员人数
     * @return bool|array
     * 以下是返回值partyList结构 如果type=0 返回值就是partyList
     * @param string $callId 参会人ID
     * @param string $callId 参会人名称
     * @param string $callId 电话
     * @param string $callId 加入时间
     * @param string $callId Pin 码
     * @author 秋月 414111907@qq.com
     */
    public function getOnlineParties($params)
    {
        $url = "/services/monitor/getOnlineParties";
        $res = $this->postjson($url, $params);
        if (!$res) {
            return false;
        }
        if (Arr::get($params, 'type') == 0) {
            return Arr::get($res, 'partyList', []);
        }
        return $res;
    }

    /**
     * 3.18 修改参会者角色接口
     *
     * 客户通过该接口，可以将在会议中的参会者修改为新的角色，可以修改为参与人和主持人
     *
     * @param array $params
     * 以下是$params结构
     * @param string $clientId 唯一客户端id 3.1接口获取
     * @param string $hostPwd 会议密码hostpasscode
     * @param string $conferenceId 会议ID
     * @param string $role 修改角色的类型 0:主持人 1:主讲人 2:参与人 3:来宾
     * @param array $partyList 参会人ID集合 最多50
     * @return bool
     * @author 秋月 414111907@qq.com
     */
    public function modifyParty($params)
    {
        $url = "/services/monitor/modifyParty";
        $res = $this->postjson($url, $params);
        return $res ? true : false;

    }

    /**
     * 4.1 查询联系人接口
     *
     * 通过该接口，查询出指定参会人集合
     *
     * 查询所有 可以value=% type=2
     *
     * @param array $params
     * 以下是$params结构
     * @param string $key 查询条件 单选一个 name:姓名  email:邮箱 phone:电话  company:公司 department:部门 position:职位
     * @param string $value 查询值[多个用;号隔开 最多50]
     * @param string $type 查询类型 1:精确查询(默认) 2:模糊查询(value值只能是一个)
     * @return bool|array
     * 以下返回值结构
     * @param string $defaultRole 默认参会人角色 0:主持人 1:主讲人 2:参会人 3:嘉宾
     * @param string $callChoice 外呼首选 1:手机；2:办公电话；3:家庭电话
     * @param string $officePhone 工作电话
     * @param string $officeExt 工作电话分机
     * @param string $homePrefix 家庭电话国家码/区号
     * @param string $homePhone 家庭电话
     * @param string $homeExt 家庭电话分机
     * @param string $mobilePrefix 手机国家码
     * @param string $officePrefix 工作电话国家码/区号
     * @param string $email 邮箱
     * @param string $mobilePhone 手机
     * @param string $department 部门
     * @param string $company 公司
     * @param string $position 职位
     * @param string $contactId 联系人ID
     * @param string $name 参会人名称
     * @author 秋月 414111907@qq.com
     */
    public function queryContactList($params)
    {
        $url = "/services/address/queryContactList";
        $res = $this->postjson($url, $params, true);
        if (!$res) {
            return false;
        }
        return Arr::get($res, 'contactList', []);
    }

    /**
     * 4.2 删除联系人接口
     *
     * 通过接口，删除账号下指定联系人
     *
     * @param string $id 联系人ID 多个用;号隔开 最多50个
     * @return bool
     * @author 秋月 414111907@qq.com
     */
    public function deleteContact($id)
    {
        $url = "/services/address/deleteContact";
        $params = [
            'contactId' => $id,
        ];
        $res = $this->postjson($url, $params, true);
        return $res ? true : false;
    }

    /**
     * 4.3 添加联系人接口 接口方回复无法使用
     *
     * 通过接口给指定账号添加联系人
     *
     * @param array $users
     * 以下是$user结构 name必填 联系方式必填一个
     * @param string $name 参会人名称
     * @param string $email 邮箱
     * @param string $mobilePrefix 手机国家码
     * @param string $mobilePhone 手机
     * @param string $officePrefix 工作电话国家码/区号
     * @param string $officePhone 工作电话
     * @param string $officeExt 工作电话分机
     * @param string $homePrefix 家庭电话国家码/区号
     * @param string $homePhone 家庭电话
     * @param string $homeExt 家庭电话分机
     * @param string $callChoice 外呼首选 1:手机；2:办公电话；3:家庭电话
     * @param string $company 公司
     * @param string $department 部门
     * @param string $position 职位
     * @param string $defaultRole 默认参会人角色  0:主持人 1:主讲人 2:参会人 3:嘉宾
     * @param string $contactGroupName 组名 多个时用;分隔
     * @return bool|array
     * @author 秋月 414111907@qq.com
     */
    public function addContact($users)
    {
        $params = [
            'contactList' => $users,
        ];
        $url = "/services/address/addContact";
        $res = $this->postjson($url, $params, true);
        if (!$res) {
            return false;
        }
        return Arr::get($res, 'contactList', []);
    }

    /**
     * 4.4 修改联系人接口 无法使用
     *
     * 通过接口修改账号联系人信息
     *
     * @param array $users
     * @param array $user结构参照4.3添加接口
     * @return bool
     * @author 秋月 414111907@qq.com
     */
    public function updateContact($users)
    {
        $url = "/services/address/updateContact";
        $params = [
            'contactList' => $users,
        ];
        $res = $this->postjson($url, $params, true);
        return $res ? true : false;
    }

    /**
     * 6.1 发送会议通知
     *
     * 通过该接口，给指定参会人发送会议相应通知
     *
     * @param array $params
     * 以下是$params结构
     * @param string $hostPwd 会议密码hostpasscode
     * @param string $conferenceId 会议ID
     * @param string $notifyType 通知类型 1:邀请通知 2:变更通知 3:取消通知
     * @param string $isSendSms 是否发送短信通知 1:是 0:否
     * @param string $isSendEmial 是否发送邮件通知  1:是 0:否
     * @param array $partyList 传递多个参会人，最多不超过200个
     * 以下是$partyList结构
     * @param string $phone 电话号码 可空，发送短信时必须为手机号；当发送邮件时也可以输入固话。
     * @param string $role 入会角色 1:主持人 0:参与人
     * @param string $name 参会人姓名
     * @param string $email 参会人邮箱
     * @return bool
     * @author 秋月 414111907@qq.com
     */
    public function sendNotify($params)
    {
        $url = "/services/confNotify/sendNotify";
        $res = $this->postjson($url, $params);
        return $res ? true : false;
    }

    public function getError()
    {
        return $this->error;
    }

    private function xmlToArray($xml)
    {
        return json_decode(json_encode(simplexml_load_string($xml)), true);
    }

    private function arrayToXml($params, $root = "root")
    {
        if (!Arr::exists($params, 'accountPwd')) {
            $params = Arr::prepend($params, $this->accountPwd, 'accountPwd');

        }

        return ArrayToXml::convert($params, $root, true, 'UTF-8', '1.0', [
            'standalone' => true,
        ]);
    }

    private function postxml($url, $xml)
    {
        $this->error = null;

        $url = self::URL . $url;
        $response = Requests::post($url, [
            'Content-Type' => 'application/xml',
        ], $xml);
        if ($response->status_code != 200) {
            $this->error = "api接口请求失败 错误码:" . $response->status_code;
            return false;
        }
        $res = $this->xmlToArray($response->body);
        $code = Arr::get($res, 'statusCode', -1);
        if ($code != 0) {
            //设置错误信息
            $error = Arr::get($res, 'reason');
            //如果reason存在 表示错误提示2类 否则用一类错误
            if (!$error) {
                $error = Arr::get($this->tipErrors1, $code, "未知错误");
            }
            $this->error = $error;
            return false;
        }
        return $res;
    }

    private function postjson($url, $json, $addPwd = false)
    {
        $url = self::URL . $url;
        if (!is_string($json)) {
            if ($addPwd && !Arr::exists($json, 'accountPwd')) {
                $json['accountPwd'] = $this->accountPwd;
            }
            $json = json_encode($json);
        }

        $response = Requests::post($url, [
            'Content-Type' => 'application/json',
        ], $json);
        if ($response->status_code != 200) {
            $this->error = "api接口请求失败 错误码:" . $response->status_code;
            return false;
        }
        $res = json_decode($response->body, true);
        $code = Arr::get($res, 'statusCode', -1);
        if ($code != 0) {
            $error = Arr::get($this->tipErrors2, $code) . " " . Arr::get($res, 'reason');
            $this->error = $error;
            return false;
        }
        return $res;
    }

    private function get($url, $params)
    {
        if (!Arr::exists($params, 'accountPwd')) {
            //渣渣 get参数要按顺序 否则404
            $params = Arr::prepend($params, $this->accountPwd, 'accountPwd');
        }

        $this->error = null;
        $url = self::URL . $url . http_build_query($params, null, '/');

        $response = Requests::get($url);
        if ($response->status_code != 200) {
            $this->error = "api接口请求失败 错误码:" . $response->status_code;
            return false;
        }
        $res = $this->xmlToArray($response->body);
        $code = Arr::get($res, 'statusCode', -1);
        if ($code != 0) {
            //设置错误信息
            $error = Arr::get($res, 'reason');
            //如果reason存在 表示错误提示2类 否则用一类错误
            if (!$error) {
                $error = Arr::get($this->tipErrors1, $code, "未知错误");
            }
            $this->error = $error;
            return false;
        }
        return $res;
    }
}
