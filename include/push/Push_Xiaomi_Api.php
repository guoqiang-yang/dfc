<?php
/**
 * Created by PhpStorm.
 * User: qihua
 * Date: 16/9/8
 * Time: 11:12
 */

use xmpush\Builder;
use xmpush\HttpBase;
use xmpush\Sender;
use xmpush\Constants;
use xmpush\Stats;
use xmpush\Tracer;
use xmpush\Feedback;
use xmpush\DevTools;
use xmpush\Subscription;
use xmpush\TargetedMessage;

include_once INCLUDE_PATH . '/push/autoload.php';;

class Push_Xiaomi_Api extends Base_Api
{
    const HAOCAI_APP = 1;
    const HAOCAI_DRIVER = 2;
    const HAOCAI_CRM = 3;
    public static $SECRET = array(
        self::HAOCAI_APP => 'InyHX1P2Xuit/wccxMUSvA==',
        self::HAOCAI_DRIVER => 'bcQFUYma0ZX0HZBFGKhNZQ==',
        self::HAOCAI_CRM => 'mckQo9XxfzfkxsQTVl4ubg==',
    );
    public static $PACKAGE = array(
        self::HAOCAI_APP => 'com.haocai.app',
        self::HAOCAI_DRIVER => 'com.haocai.driver',
        self::HAOCAI_CRM => 'com.haocai.crm',
    );

    public static function pushToCrm($regid, $title, $desc, $msgType, $ext = array())
    {
        $payload = array(
            "msgtype" => $msgType,
        );
        if (!empty($ext))
        {
            foreach ($ext as $k => $v)
            {
                $payload[$k] = $v;
            }
        }

        $payload = json_encode($payload);

        return self::pushToUser($regid, $title, $desc, $payload, self::HAOCAI_CRM);
    }

    public static function pushToUserMessage($regid, $title, $desc, $app = self::HAOCAI_APP, $orderState = 0)
    {
        $payload = array(
            "msgtype" => 0,
            "needShare" => "false",
            "url" => "http://www.baidu.com",
            "title" => "",
            "shareLogo" => "",
            "shareTitle" => "",
            "shareDesc" => "",
            "shareUrl" => "",
            "orderState" => $orderState,
        );

        $payload = json_encode($payload);

        self::pushToUser($regid, $title, $desc, $payload, $app);
    }

    public static function pushToUserWebpage($regid, $title, $desc, $app = self::HAOCAI_APP)
    {
        $payload = array(
            "msgtype" => 2,
            "needShare" => "false",
            "url" => "http://www.baidu.com",
            "title" => "",
            "shareLogo" => "",
            "shareTitle" => "",
            "shareDesc" => "",
            "shareUrl" => "",
        );

        $payload = json_encode($payload);
        self::pushToUser($regid, $title, $desc, $payload, $app);
    }

    public static function pushToAllMessage($title, $desc, $app = self::HAOCAI_APP)
    {
        $payload = array(
            "msgtype" => 1,
            "needShare" => "false",
            "url" => "http://www.baidu.com",
            "title" => "",
            "shareLogo" => "",
            "shareTitle" => "",
            "shareDesc" => "",
            "shareUrl" => "",
        );

        $payload = json_encode($payload);

        self::pushToAll($title, $desc, $payload, $app);
    }

    public static function pushToAllWebpage($title, $desc, $app = self::HAOCAI_APP)
    {
        $payload = array(
            "msgtype" => 2,
            "needShare" => "false",
            "url" => "http://www.baidu.com",
            "title" => "",
            "shareLogo" => "",
            "shareTitle" => "",
            "shareDesc" => "",
            "shareUrl" => "",
        );

        $payload = json_encode($payload);
        self::pushToAll($title, $desc, $payload, $app);
    }

    public static function pushToAll($title, $desc, $payload, $app = self::HAOCAI_APP)
    {
        // 常量设置必须在new Sender()方法之前调用
        $package = self::$PACKAGE[$app];
        $secret = self::$SECRET[$app];
        Constants::setPackage($package);
        Constants::setSecret($secret);

        $sender = new Sender();
        $message = self::_createMsg($title, $desc, $payload);

        $sender->broadcastAll($message)->getRaw();
    }

    public static function pushToUser($regid, $title, $desc, $payload, $app = self::HAOCAI_APP)
    {
        //		$regid = 'Oec8Et3derSpdgy93nUQURr96aT9gxpDf5H8ULcXV7g=';
        // 常量设置必须在new Sender()方法之前调用
        $package = self::$PACKAGE[$app];
        $secret = self::$SECRET[$app];
        Constants::setPackage($package);
        Constants::setSecret($secret);

        $sender = new Sender();
        $message = self::_createMsg($title, $desc, $payload);

        $targetMessage2 = new TargetedMessage();
        $targetMessage2->setTarget($regid, TargetedMessage::TARGET_TYPE_REGID);
        $targetMessage2->setMessage($message);

        return $sender->send($message, $regid)->getRaw();
    }

    private static function _createMsg($title, $desc, $payload)
    {
        $message = new Builder();

        $message->title($title);  // 通知栏的title
        $message->description($desc); // 通知栏的descption
        $message->passThrough(0);  // 这是一条通知栏消息，如果需要透传，把这个参数设置成1,同时去掉title和descption两个参数
        $message->payload($payload); // 携带的数据，点击后将会通过客户端的receiver中的onReceiveMessage方法传入。
        $message->extra(Builder::notifyForeground, 0); // 应用在前台是否展示通知，如果不希望应用在前台时候弹出通知，则设置这个参数为0
        $message->notifyType(5);
        $message->notifyId(0); // 通知类型。最多支持0-4 5个取值范围，同样的类型的通知会互相覆盖，不同类型可以在通知栏并存
        $message->build();

        return $message;
    }
}