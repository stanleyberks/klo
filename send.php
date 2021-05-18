<?php
//Включение отладочной информации
ini_set('display_errors', '1');
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
//Конец включения отладочной информации

include_once 'settings.php';
include_once 'logging.php';
include_once 'cookies.php';
include_once 'redirect.php';
include_once 'requestfunc.php';

$name = '';
if (isset($_POST['name']))
    $name=$_POST['name'];
else if (isset($_POST['fio']))
    $name=$_POST['fio'];
else if (isset($_POST['first_name'])&&isset($_POST['last_name']))
    $name = $_POST['first_name'].' '.$_POST['last_name'];
else if (isset($_POST['firstname'])&&isset($_POST['lastname']))
    $name = $_POST['firstname'].' '.$_POST['lastname'];
$phone = isset($_POST['phone'])?$_POST['phone']:$_POST['tel'];
$subid = isset($_COOKIE['subid'])?$_COOKIE['subid']:'';

$is_duplicate = lead_is_duplicate($subid,$phone);

$cookietime=time()+60*60*24*5; //время, на которое ставятся куки, по умолчанию - 5 дней
//устанавливаем пользователю в куки его имя и телефон, чтобы показать их на стр Спасибо
ywbsetcookie('name',$name,$cookietime,'/');
ywbsetcookie('phone',$phone,$cookietime,'/');

//шлём в ПП только если это не дубль
if (!$is_duplicate){
    $fullpath='';
    //если у формы прописан в action адрес, а не локальный скрипт, то шлём все данные формы на этот адрес
    if (substr($black_land_conversion_script, 0, 4 ) === "http"){
        $fullpath=$black_land_conversion_script;
    }
    //иначе составляем полный адрес до скрипта отправки ПП
    else{
        $url= $_COOKIE['landing'].'/'.$black_land_conversion_script;
        $fullpath = get_abs_from_rel($url);
    }
    $curl = curl_init();
    curl_setopt_array($curl, array(
      CURLOPT_URL => $fullpath,
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_TIMEOUT => 0,
      CURLOPT_FOLLOWLOCATION => false,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => "POST",
      CURLOPT_SSL_VERIFYPEER => false,
      CURLOPT_POSTFIELDS => http_build_query($_POST),
      CURLOPT_REFERER => $_SERVER['REQUEST_URI'],
      CURLOPT_HTTPHEADER => array(
        "Content-Type: application/x-www-form-urlencoded"
      )
    ));

    $content = curl_exec($curl);
    $info = curl_getinfo($curl);
    $error= curl_error($curl);
    curl_close($curl);

    //в ответе должен быть редирект, если его нет - грузим обычную страницу Спасибо кло
    switch($info["http_code"]){
        case 302:
            write_leads_to_log($subid,$name,$phone,'');
            if ($black_land_use_custom_thankyou_page ){
                redirect("thankyou.php?".http_build_query($_GET));
            }
            else{
                redirect($info["redirect_url"]);
            }
            break;
        case 200:
            write_leads_to_log($subid,$name,$phone,'');
            if ($black_land_use_custom_thankyou_page ){
                jsredirect("thankyou.php?".http_build_query($_GET));
            }
            else{
                echo $content;
            }
            break;
        default:
            var_dump($error);
            var_dump($info);
            exit();
            break;
    }
}
else
{
    redirect('thankyou.php');
}

?>