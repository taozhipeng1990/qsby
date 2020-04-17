### 全时百应 php 接口文档

1. 使用了 tp 的 Arr 类库
2. 使用 Requests 类库
3. 使用了 ArrayToXml 类库
4. Callback 中有返回的数据格式 request 以使用的 php 框架为主

### composer

```
composer requirt qmq\qsby
```

### 以下是调用示例

```
$api = new \qmq\qsby\Qsby("密码");
```

#### 2.1 帐号检测

```
$res = $api->checkPwd();
```

#### 2.2 创建会议

```
$res = $api->confReserve([
            'confName' => "测试会议",
            'dateStart' => date("Y-m-d", strtotime("+10 days")),
            'timeStartH' => "10",
            "timeStartM" => "00",
            "confDuration" => "30",
            "partMax" => 5,
        ]);
```

#### 2.3 变更会议

```
$res = $api->confModify([
            'conferenceId' => '9838924',
            'confName' => '变更测试会议12',
            'dateStart' => date("Y-m-d", strtotime("+10 days")),
            'timeStartH' => "10",
            "timeStartM" => "00",
            "confDuration" => "30",
            "partMax" => "5",
        ]);
```

#### 2.5 会议状态查询

```
 $res = $api->confStatus([
            '9838924',
            '9838925',
        ]);
```

#### 2.6 会议取消

```
$res = $api->confDelete(9838924);
```

#### 2.7 会后报告接口

```
$res = $api->confReport(9840072);
```

#### 2.8 会议监控接口

```
$res = $api->confMonitor(9840072, '12312312');
```

#### 2.9 会议录音下载页面

```
$res = $api->confRecordDownload(9840072);
```

#### 2.10 会议列表接口

```
$res = $api->confList(['conferenceStatus' => [0]]);
```

#### 2.11 会议监控项设置接口

```
$res = $api->confFunction();
```

#### 2.12 会议平台归属查询接口

```
$res = $api->getplatform(79107999);
```

#### 3.1 订阅监控接口

```
        $res = $api->subscribe([
            'hostPwd' => '248663',
            'conferenceId' => '9840072',
            'needNotify' => "1",
            'returnUrl' => 'http://www.baidu.com',
        ]);
```

#### 3.2 走廊外呼接口

```
 $res = $api->hallwayCallout([
            'clientId' => "123",
            'hostPwd' => '248663',
            'conferenceId' => '9840072',
            "phone" => "86123456",
            "name" => "xxx",
            "location" => 1,
        ]);
```

#### 3.3 外呼接口

```
$res = $api->callout([
            'clientId' => "123",
            'hostPwd' => '248663',
            'conferenceId' => '9840072',
            'ifSubscribe' => 1,
            "partyList" => [
                ["phone" => "8618811111111", "role" => "1", "name" => "x1"],
                ["phone" => "8618811111112", "role" => "0", "name" => "x2"],
                ["phone" => "8618811111113", "role" => "0", "name" => "x3"],
                ["phone" => "8618811111114", "role" => "0", "name" => "x4"],
            ],
        ]);
```

#### 3.4 移动参会者【走廊与会议室】接口

```
 $res = $api->moveLocation([
            'hostPwd' => '248663',
            'conferenceId' => '9840072',
            "location" => "2",
            "partyList" => ['1', '2', '3', '4'],
        ]);
```

#### 3.5 挂断参会者接口

```
 $res = $api->hangup([
            'clientId' => "123",
            'hostPwd' => '248663',
            'conferenceId' => '9840072',
            "partyList" => ['1', '2', '3', '4'],
        ]);
```

#### 3.6 查询参会者状态接口

```
 $res = $api->partyStatus([
            'hostPwd' => '248663',
            'conferenceId' => '9840072',
            "partyList" => ['1', '2', '3', '4'],
        ]);
```

#### 3.7 播放会中语音接口

```
$res = $api->playMusic([
            'hostPwd' => '248663',
            'conferenceId' => '9840072',
            "status" => 1,
            "playDuration" => 30,
        ]);
```

#### 3.8 录音接口

```
 $res = $api->record([
            'clientId' => "123",
            'hostPwd' => '248663',
            'conferenceId' => '9840072',
            "status" => 1,
        ]);
```

#### 3.9 参会者静音接口

```
  $res = $api->muteparty([
            'clientId' => "123",
            'hostPwd' => '248663',
            'conferenceId' => '9840072',
            "type" => 0,
            "partyList" => [1, 2, 3, 4],
        ]);
```

#### 3.10 结束会议接口

```
 $res = $api->closeConf([
            'hostPwd' => '248663',
            'conferenceId' => '9840072',
            'status' => 1,
        ]);
```

#### 3.11 会中修改会议属性接口

```
$res = $api->alterConf([
            'hostPwd' => '248663',
            'conferenceId' => '9840072',
            'fieldList' => [
                ['fieldName' => "duration", "value" => 30],
            ],
            'timerList' => [
                ['time' => "60", 'timerAction' => "1", "actionValue" => null],
            ],
        ]);
```

#### 3.14 下载录音接口

```
  $res = $api->downloadRecord([
            'hostPwd' => '248663',
            'conferenceId' => '9840072',
        ]);
```

#### 3.15 会场静音接口

```
$res = $api->muteConf([
            'clientId' => "123",
            'hostPwd' => '248663',
            'conferenceId' => '9840072',
            'type' => 0,
        ]);
```

#### 3.16 获取会议在线人员列表接口

```
 $res = $api->getOnlineParties([
            'hostPwd' => '248663',
            'conferenceId' => '9840072',
            'type' => 0,
        ]);
```

#### 3.18 修改参会者角色接口

```
 $res = $api->modifyParty([
            'clientId' => "123",
            'hostPwd' => '248663',
            'conferenceId' => '9840072',
            "role" => 3,
            "partyList" => [1, 2, 3, 4],
        ]);
```

#### 4.1 查询联系人接口

```
$res = $api->queryContactList([
            'key' => "name",
            'value' => "%",
            'type' => 2,
        ]);
```

#### 4.2 删除联系人接口

```
$res = $api->deleteContact("11005066");
```

#### 4.3 添加联系人接口 接口无法使用

```
 $res = $api->addContact([
            ['name' => "xxx", "mobilePrefix" => "86", "mobilePhone" => "18898757011"],
            ['name' => "yyy", "officePrefix" => "86", "officePhone" => "18898757012"],
        ]);
```

#### 4.4 修改联系人接口

```
$res = $api->updateContact([
            ['contactId' => '11005408', 'name' => "x1"],
        ]);
```

#### 6.1 发送会议通知

```
 $res = $api->sendNotify([
            'hostPwd' => '248663',
            'conferenceId' => '9840072',
            'notifyType' => 1,
            'isSendSms' => "1",
            "isSendEmial" => 0,
            'partyList' => [
                ['phone' => "(86)18898757032", "role" => 0, "name" => "X先生", "email" => "414111907@qq.com"],
            ],
        ]);
```
