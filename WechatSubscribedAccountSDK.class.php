<?php

/**
 * 微信公众平台个人订阅号PHP-SDK,支持的官方API部分
 * @auther frehaiku <haiku888@foxmail.com>
 * @link https://github.com/frehaiku/wechatAPI
 * Using:
 * $wechat = new WechatSubscribedAccountSDK();
 * $wechat->checkSignature();
 *
 * $type = $wechat->getRevType();
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
    const MSGTEXT = 'text';
    const MSGIMAGE = 'image';
    const MSGVOICE = 'voice';
    const MSGLOCA = 'location';
    const MSGEVENT = 'event';

    private function checkSignature()
    {
        $sign = $_GET['signature'];
        $ts = $_GET['timestamp'];
        $nonce = $_GET['nonce'];
        $token = $_GET['token'];

        $param = array($ts, $nonce, $token);
        ksort($param);

        $param = sha1(implode('', $param));

        if ($param === $sign) {
            echo $_GET['echostr'];

            $this->getMsg();
        } else {
            echo 'no access';
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
                        $innerStr .= "<$k>" . inner($list) . "</$k>\n";
                    else
                        $innerStr .= inner($list);
                else:
                    if (!is_numeric($k))
                        $innerStr .= "<$k>" . $list . "</$k>\n";
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

    /**
     *  get sender’s msg
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
     */
    public function getRecTo()
    {
        if (isset($this->_receiveData['ToUserName'])) {
            return $this->_receiveData['ToUserName'];
        } else {
            return false;
        }
    }

    public function getRevType()
    {
        if (isset($this->_receiveData['MsgType'])) {
            return $this->_receiveData['MsgType'];
        } else {
            return false;
        }
    }

    public function getRevTime()
    {
        if (isset($this->_receiveData['CreateTime'])) {
            return $this->_receiveData['CreateTime'];
        } else {
            return false;
        }
    }

    public function getRevID()
    {
        if (isset($this->_receiveData['MsgId'])) {
            return $this->_receiveData['MsgId'];
        } else {
            return false;
        }
    }

    public function getRecContent()
    {
        if (isset($this->_receiveData['Content'])) {
            return $this->_receiveData['Content'];
        } else if (isset($this->_receiveData['Recongnition'])) {
            return $this->_receiveData['Recongnition'];
        } else {
            return false;
        }
    }

    public function getRecEvent()
    {
        if (isset($this->_receiveData['Event'])) {
            $arr[] = $this->_receiveData['Event'];
        } else if (isset($this->_receiveData['EventKey'])) {
            $arr[] = $this->_receiveData['EventKey'];
        }

        if (isset($arr) && sizeof($arr)) {
            return $arr;
        } else {
            return false;
        }
    }

    private function getMsg()
    {
        $data = $GLOBALS['HTTP_RAW_POST_DATA'];
        $xml = simplexml_load_string($data);
        $this->_receiveData = $xml;

        return $this->xmlToArray($xml);
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
            array_push($sendArr['Articles'], array('Item' => $item));
        }

        $this->_sendData = $this->arrayToXml($sendArr);

        return $this;
    }

    /**
     * Example: $this->text('msg')->reply(); or $this->reply('msg')
     * @param $msg set _sendData,directly excute reply,default with empty
     */
    public function reply($msg)
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
