<?php 

function https_post($url,$data)
{
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url); 
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
    curl_setopt($curl, CURLOPT_POST, 1);
    curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    $result = curl_exec($curl);
    if (curl_errno($curl)) {
       return 'Errno'.curl_error($curl);
    }
    curl_close($curl);
    return $result;
}
/*
*微信tpl
*文字以及图片的返回的tpl
*/

function reply_text($from_openid, $to_openid, $content){
    $text_tpl = "<xml>
                    <ToUserName><![CDATA[%s]]></ToUserName>
                    <FromUserName><![CDATA[%s]]></FromUserName>
                    <CreateTime>%s</CreateTime>
                    <MsgType><![CDATA[text]]></MsgType>
                    <Content><![CDATA[%s]]></Content>
                    <FuncFlag>0</FuncFlag>
                </xml>";
    return sprintf($text_tpl, $to_openid, $from_openid, time(), $content);
}

function reply_news($from_openid, $to_openid, $items){
    $resultStr = "<xml>\n
                    <ToUserName><![CDATA[".$to_openid."]]></ToUserName>\n
                    <FromUserName><![CDATA[".$from_openid."]]></FromUserName>\n
                    <CreateTime>".time()."</CreateTime>\n
                    <MsgType><![CDATA[news]]></MsgType>\n
                    <ArticleCount>".count($items)."</ArticleCount>\n
                    <Articles>\n";
    foreach ($items as $item) {
        $resultStr.="<item>\n
                        <Title><![CDATA[".$item[0]."]]></Title>\n
                        <Description><![CDATA[".$item[1]."]]></Description>\n
                        <PicUrl><![CDATA[".$item[2]."]]></PicUrl>\n
                        <Url><![CDATA[".$item[3]."]]></Url>\n
                    </item>\n";
    }
    $resultStr .= "</Articles>\n
                </xml>";
    return $resultStr;
}
/*
*微信客服
*/
function reply_service($from_openid, $to_openid){
    $text_tpl = "<xml>
                    <ToUserName><![CDATA[%s]]></ToUserName>
                    <FromUserName><![CDATA[%s]]></FromUserName>
                    <CreateTime>%s</CreateTime>
                    <MsgType><![CDATA[transfer_customer_service]]></MsgType>
                </xml>";
    return sprintf($text_tpl, $to_openid, $from_openid, time());
}



?>