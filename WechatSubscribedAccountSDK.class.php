<?php

/**
 * 微信公众平台个人订阅号PHP-SDK,支持的官方API部分
 * @auther frehaiku <haiku888@foxmail.com>
 * @link https://github.com/frehaiku/wechatAPI
 * Using:
 * $wechat = new WechatSubscribedAccountSDK();
 * $wechat->checkSignature();
 *
 * $type = $wechat->getMsg()->getRecType();
 * switch($type) {
 *      case WechatSubscribedAccountSDK::MSGTEXT:
 *          $wechat->text('reply to you')->reply();
 *          break;
 *      case WechatSubscribedAccountSDK::MSGEVENT:
 *          ...
 *          break;
 * }
 */
class WechatSubscribedAccountSDK
{
    private $_receiveData;
    private $_sendData;
    private $token;
    const MSGTEXT = 'text';
    const MSGIMAGE = 'image';
    const MSGVOICE = 'voice';
    const MSGLOCA = 'location';
    const MSGEVENT = 'event';

    public function __construct($token)
    {
        $this->token = $token;
    }

    public function checkSignature()
    {
        // 在非服务器配置更改时，不验证签名
        if (empty($_GET['echostr'])) {
            return true;
        }
        $sign = isset($_GET['signature']) ? $_GET['signature'] : '';
        $ts = isset($_GET['timestamp']) ? $_GET['timestamp'] : '';
        $nonce = isset($_GET['nonce']) ? $_GET['nonce'] : '';
        $token = $this->token;

        $param = array($ts, $nonce, $token);
        sort($param, SORT_STRING);

        $param = sha1(implode('', $param));

        if ($param === $sign) {
            die($_GET['echostr']);

        } else {
            die('no access');
        }
    }

    public function xmlToArray($xml)
    {
        $arr = (array)$xml;
        foreach ($arr as $k => $item) {
            if ($xml instanceof SimpleXMLElement) {
                $arr[$k] = $this->xmlToArray($item);
            }
        }
        return $arr;
    }

    public function arrayToXml($lists)
    {
        $str = "<xml>";

        function inner($lists)
        {
            $innerStr = '';

            foreach ($lists as $k => $list) {

                if (!is_numeric($list) && !is_array($list)) {
                    $list = "<![CDATA[$list]]>";
                }
                // wipe the key is int
                if (is_array($list)):
                    if (!is_numeric($k))
                        $innerStr .= "<$k>" . inner($list) . "</$k>\r\n";
                    else
                        $innerStr .= inner($list);
                else:
                    if (!is_numeric($k))
                        $innerStr .= "<$k>" . $list . "</$k>\r\n";
                    else
                        $innerStr .= $list;
                endif;
            }
            return $innerStr;
        }

        $str .= inner($lists);
        $str .= "</xml>";

        return $str;
    }

    /** get send list
     * @return array
     */
    public function getRecMsg()
    {
        if (!empty($this->_receiveData)) {
            return $this->_receiveData;
        } else {
            return false;
        }
    }

    /**
     *  get sender’s msg(openid)
     * eg: oDsxCuBbbPPNjUES2vbNUKYH11D4
     */
    public function getRecFrom()
    {
        if (isset($this->_receiveData['FromUserName'])) {
            return $this->_receiveData['FromUserName'];
        } else {
            return false;
        }
    }

    /**
     *  get receive’s msg
     * eg: gh_c8da4ce1e9s
     */
    public function getRecTo()
    {
        if (isset($this->_receiveData['ToUserName'])) {
            return $this->_receiveData['ToUserName'];
        } else {
            return false;
        }
    }

    /**eg: text|news|...
     * @return array|bool
     */
    public function getRecType()
    {
        if (isset($this->_receiveData['MsgType'])) {
            return $this->_receiveData['MsgType'];
        } else {
            return false;
        }
    }

    public function getRecTime()
    {
        if (isset($this->_receiveData['CreateTime'])) {
            return $this->_receiveData['CreateTime'];
        } else {
            return false;
        }
    }

    /**get MsgId
     * @return array|bool
     */
    public function getRecID()
    {
        if (isset($this->_receiveData['MsgId'])) {
            return $this->_receiveData['MsgId'];
        } else {
            return false;
        }
    }

    /**get sender reply's content
     * @return array|bool
     */
    public function getRecContent()
    {
        if (isset($this->_receiveData['Content'])) {
            return $this->_receiveData['Content'];
        } else if (isset($this->_receiveData['Recognition'])) {
            return $this->_receiveData['Recognition'];
        } else {
            return false;
        }
    }

    /**eg: subscribe
     * @return array|bool
     */
    public function getRecEvent()
    {
        if (isset($this->_receiveData['Event'])) {
            $arr['Event'] = $this->_receiveData['Event'];
        }
        if (isset($this->_receiveData['EventKey']) &&
            empty($this->_receiveData['EventKey'])
        ) {
            $arr['EventKey'] = $this->_receiveData['EventKey'];
        }

        if (isset($arr) && sizeof($arr)) {
            return $arr;
        } else {
            return false;
        }
    }

    private static function arrayToString($arr)
    {
        if (!is_array($arr)) {
            return false;
        }
        $str = '';
        foreach ($arr as $key => $value) {
            if (is_array($value)) {
                $str .= $key . ":" . self::arrayToString($value);
            } else {
                $str .= " " . $key . ":" . $value . " ";
            }
        }
        return '[' . $str . ']';
    }

    /**
     * @param $msgthe log what you want look
     */
    private function log($msg)
    {
        $fd = fopen('error.txt', 'a');

        $str = '[' . date('Y-m-d H:i:s') . ']' . $msg . "\r\n";
        fwrite($fd, $str);
        fclose($fd);
    }

    public function getMsg()
    {
        $data = $GLOBALS['HTTP_RAW_POST_DATA'];
        $this->_receiveData = (array)(simplexml_load_string($data, 'SimpleXMLElement', LIBXML_NOCDATA));
        $this->log(self::arrayToString($this->_receiveData));
        return $this;
    }

    // For Subscribed Account not support media implement,
    // reply temporarily write text and multi-news

    /** Active response text information
     * @param $content
     */
    public function text($content)
    {
        $xml = $this->arrayToXml(
            array(
                'ToUserName' => $this->_receiveData['FromUserName'],
                'FromUserName' => $this->_receiveData['ToUserName'],
                'CreateTime' => time(),
                'MsgType' => 'text',
                'Content' => $content,
            )
        );
        $this->_sendData = $xml;

        return $this;
    }

    /** the params structure
     *
     * array(
     * array(
     * 'Title'=> 'a',
     * 'Description'=>'b',
     * 'PicUrl'=>'c',
     * 'Url'=> 'd'
     * ),
     * array(
     * 'Title'=> '...',
     * 'Description'=>'...',
     * 'PicUrl'=>'...',
     * 'Url'=> '...'
     * ),
     * )
     * @param $arr
     */
    public function news($arr)
    {
        $sendArr = array(
            'ToUserName' => $this->_receiveData['FromUserName'],
            'FromUserName' => $this->_receiveData['ToUserName'],
            'CreateTime' => time(),
            'MsgType' => 'news',
            'ArticleCount' => sizeof($arr),
            'Articles' => array()
        );
        foreach ($arr as $item) {
            array_push($sendArr['Articles'], array('item' => $item));
        }
        $this->_sendData = $this->arrayToXml($sendArr);
//        $this->log($this->_sendData);

        return $this;
    }

    /**
     * Example: $this->text('msg')->reply(); or $this->reply('msg')
     * @param $msg set _sendData,directly excute reply,default with empty
     */
    public function reply($msg = '')
    {
        // reset _sendData
        if (!empty($msg)) {
            if (is_string($msg)) {
                $this->text($msg);
            } else {
                $this->news($msg);
            }
        }
        if (empty($this->_sendData)) {
            return false;
        }

        echo $this->_sendData;
    }
}