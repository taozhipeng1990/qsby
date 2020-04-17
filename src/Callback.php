<?php
namespace qmq\qsby;

/**
 * 全时百应回调
 *
 * @author 秋月 414111907@qq.com
 */
class Callback
{
    protected $request;

    public function __construct($request)
    {
        $this->request = $request;
    }

    /**
     * 3.4 移动参会者【走廊与会议室】接口 回调
     *
     * @return array
     * @author 秋月 414111907@qq.com
     */
    public function afterMoveLocation()
    {
        $res = $this->request->post();
        // $res = [
        //     'conferenceId' => "12323", //会议ID
        //     'reason' => "", //返回详细错误原因等，成功为空串
        //     'partyList' => [
        //         ['callId' => '12', 'location' => 1, 'moveTime' => "2020-01-05 01:20:35", 'connectState' => "5"],
        //         ['callId' => '45', 'location' => 2, 'moveTime' => "2020-01-05 01:20:35", 'connectState' => "5"],
        //     ],
        // ];
        return $res;
    }

    /**
     * 3.8 录音接口 回调
     *
     * @return array
     * @author 秋月 414111907@qq.com
     */
    public function afterRecord()
    {
        $res = $this->request->post();
        // $res = [
        //     'conferenceId' => "12312", //会议ID
        //     'status' => "1", //开启或者关闭录音 0 关闭，1 开启
        //     'statusBeginTime' => '2020-04-17 10:15:25', //开始或结束录音时间YYYY-MM-DDHH:MM:SS
        //     'resultStatus' => "0", //0 成功 1 失败
        // ];
        return $res;
    }

    /**
     * 3.12 会议状态 notify 接口回调
     *
     * 会议的开始和结束消息，通过异步通知消息告诉用户。前提是调用 3.1 开始监控接口。
     *
     * 此接口需要返回一个json
     * ['statusCode'=>0]  0:客户端接收成功 1:客户端接收失败
     *
     * @return array
     * @author 秋月 414111907@qq.com
     */
    public function afterConfState()
    {
        $res = $this->request->post();
        // $res = [
        //     'conferenceId' => '123', //会议的id
        //     'status' => '1', //会议状态 1:会议开始 0:会议结束
        //     'statustime' => '2020-04-17 10:26:01', //会议的开始结束时间
        //     'timeStamp' => '2020-04-17 10:26:01', //处理时间
        // ];
        return $res;
    }

    /**
     * 3.13 与会者状态 notify 接口(全时调用客户)
     *
     * 参会人入会或退出会议，通过异步通知消息告诉用户。前提是调用 3.1 开始监控接口
     *
     * 此接口需要返回一个json
     * ['statusCode'=>0]  0:客户端接收成功 1:客户端接收失败
     *
     * @return array
     * @author 秋月 414111907@qq.com
     */
    public function afterPartyState()
    {
        $res = $this->request->post();
        // $res = [
        //     'conferenceId' => '123',
        //     'timeStamp' => "2020-04-17 10:26:01",
        //     'partyList' => [
        //         'callId' => '12324', //参会人ID
        //         'name' => '张三', //参会人名称
        //         'phone' => '(86)18898757451', //电话
        //         'status' => '1', //入会状态  0:挂断 1:加入
        //         'statusTime' => '2020-04-17 10:26:01', //挂断或者加入时间
        //         'disconnectReason' => '0', //挂断原因  0:未知原因 1:被主持人挂断 2:终端挂断 3:无应答 4:线路忙 5:平台忙 6:号码无效 7:会议预约方数已满 8:以上 不允许外呼
        //     ],
        // ];
        return $res;
    }
}
