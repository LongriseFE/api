<?php
function wx_http_request($url, $params, $body="", $isPost=false, $isImage=false ) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_URL, $url."?".http_build_query($params));
    if($isPost){
        if($isImage){
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                    'Content-Type: multipart/form-data;',
                    "Content-Length: ".strlen($body)
                )
            );
        }else{
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                    'Content-Type: text/plain'
                )
            );
        }
        curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
    }
    $result = curl_exec($ch);
    curl_close($ch);
    return $result;
}

function getFileSize ($num) {
    $p = 0;
    $format='B';
    if($num>0 && $num<1024){
        $p = 0;
        return number_format($num).' '.$format;
    }
    if($num>=1024 && $num<pow(1024, 2)){
        $p = 1;
        $format = 'KB';
    }
    if ($num>=pow(1024, 2) && $num<pow(1024, 3)) {
        $p = 2;
        $format = 'MB';
    }
    if ($num>=pow(1024, 3) && $num<pow(1024, 4)) {
        $p = 3;
        $format = 'GB';
    }
    if ($num>=pow(1024, 4) && $num<pow(1024, 5)) {
        $p = 3;
        $format = 'TB';
    }
    $num /= pow(1024, $p);
    return number_format($num, 3).' '.$format;
}
function f_order($arr,$field,$sort){
  $order = array();
  foreach($arr as $kay => $value){
      $order[] = $value[$field];
  }
  if($sort==='desc'){
      array_multisort($order,SORT_ASC,$arr);
  }else if ($sort === 'asc'){
      array_multisort($order,SORT_DESC,$arr);
  }
  return $arr;
}
function array_iconv($data, $output = 'utf-8') {
    $encode_arr = array('UTF-8','ASCII','GBK','GB2312','BIG5','JIS','eucjp-win','sjis-win','EUC-JP');
    $encoded = mb_detect_encoding($data, $encode_arr);
    if (!is_array($data)) {
        return mb_convert_encoding($data, $output, $encoded);
    }
    else {
        foreach ($data as $key=>$val) {
        $key = array_iconv($key, $output);
        if(is_array($val)) {
            $data[$key] = array_iconv($val, $output);
        } else {
        $data[$key] = mb_convert_encoding($data, $output, $encoded);
        }
        }
    return $data;
    }
}
?>