<?php
include_once 'htmlprocessing.php';
include_once 'cookies.php';
include_once 'redirect.php';
include_once 'fbpixel.php';

//Включение отладочной информации
ini_set('display_errors', '1');
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
//Конец включения отладочной информации

function white($use_js_checks)
{
    global $white_action,$white_folder_name,$white_redirect_url,$white_redirect_type;
	global $white_curl_url,$white_error_code,$white_use_domain_specific,$white_domain_specific;


	set_subid();
    $action = $white_action;
    $folder_name= $white_folder_name;
    $redirect_url= $white_redirect_url;
    $curl_url= $white_curl_url;
    $error_code= $white_error_code;

    if ($white_use_domain_specific) { //если у нас под каждый домен свой вайт
        $curdomain = $_SERVER['SERVER_NAME'];
        foreach ($white_domain_specific as $wds) {
            if ($wds['name']==$curdomain) {
                $wtd_arr = explode(":", $wds['action'], 2);
                $action = $wtd_arr[0];
                switch ($action) {
                    case 'error':
                        $error_code= intval($wtd_arr[1]);
                        break;
                    case 'folder':
                        $folder_name = $wtd_arr[1];
                        break;
                    case 'curl':
                        $curl_url = $wtd_arr[1];
                        break;
                    case 'redirect':
                        $redirect_url = $wtd_arr[1];
                        break;
                }
                break;
            }
        }
    }

    //при js-проверках либо показываем специально подготовленный вайт
    //либо вставляем в имеющийся вайт код проверки
    if ($use_js_checks) {
        switch ($action) {
            case 'error':
            case 'redirect':
                echo load_js_testpage();
                break;
            case 'folder':
                echo load_white_content($folder_name, $use_js_checks);
                break;
            case 'curl':
                echo load_white_curl($curl_url, $use_js_checks);
                break;
        }
    } else {
        switch ($action) {
            case 'error':
                http_response_code($error_code);
                break;
            case 'folder':
                echo load_white_content($folder_name, false);
                break;
            case 'curl':
                echo load_white_curl($curl_url,false);
                break;
            case 'redirect':
                if ($white_redirect_type===302) {
                    redirect($redirect_url);
                } else {
                    redirect($redirect_url, $white_redirect_type);
                }
                break;
        }
    }
    return;
}

function black($clkrdetect, $clkrresult, $check_result)
{
    global $black_preland_action,$black_preland_redirect_type, $black_preland_redirect_urls, $black_preland_folder_names;
	global $black_land_action, $black_land_folder_names, $save_user_flow;
	global $black_land_redirect_type,$black_land_redirect_urls;
	global $fbpixel_subname;

    header('Access-Control-Allow-Credentials: true');
    if (isset($_SERVER['HTTP_REFERER'])) {
        $parsed_url=parse_url($_SERVER['HTTP_REFERER']);
        header('Access-Control-Allow-Origin: '.$parsed_url['scheme'].'://'.$parsed_url['host']);
    }
	
	$fbpx = getpixel();
	if ($fbpx!==''){
		ywbsetcookie($fbpixel_subname,$fbpx,'/');
	}
	$cursubid=set_subid();
    //устанавливаем fbclid в куки, если получали его из фб
	if (isset($_GET['fbclid']) && $_GET['fbclid']!='')
	{
		ywbsetcookie('fbclid',$_GET['fbclid'],'/');
	}

	$prelandings=[];
	if ($black_preland_action=='redirect')
		$prelandings=$black_preland_redirect_urls;
	else if ($black_preland_action=='folder')
		$prelandings = $black_preland_folder_names;

	$landings=[];
	if ($black_land_action=='redirect')
		$landings = $black_land_redirect_urls;
	else if ($black_land_action=='folder')
		$landings = $black_land_folder_names;
	
    switch ($black_preland_action) {
        case 'none':
            $res=select_landing($save_user_flow,$landings);
            $landing=$res[0];
            write_black_to_log($cursubid, $clkrdetect, $clkrresult, $check_result, '', $landing);

            switch ($black_land_action){
                case 'folder':
                    echo load_landing($landing);
                    break;
                case 'redirect':
                    redirect($landing,$black_land_redirect_type);
                    break;
            }

            break;
        case 'folder':
			//если мы используем локальные проклы
            if ($prelandings != []) {
                $prelanding='';
                if ($save_user_flow && isset($_COOKIE['prelanding'])) {
                    $prelanding = $_COOKIE['prelanding'];
					if (array_search($prelanding,$prelandings)===false)
						$prelanding='';
                }
				if ($prelanding==='') {
                    //A-B тестирование прокладок
                    $r = rand(0, count($prelandings) - 1);
                    $prelanding = $prelandings[$r];
					ywbsetcookie('prelanding',$prelanding,'/');
                }

                $res=select_landing($save_user_flow,$landings);
                $landing=$res[0];
                $t=$res[1];

                echo load_prelanding($prelanding, $t);
                write_black_to_log($cursubid, $clkrdetect, $clkrresult, $check_result, $prelanding, $landing);
            }
			break;
        case 'redirect':
			$r = rand(0, count($prelandings) - 1);
			$redirect=$prelandings[$r];
            write_black_to_log($cursubid, $clkrdetect, $clkrresult, $check_result, '', $redirect);
            redirect($redirect,$black_preland_redirect_type);
            break;
    }
    return;
}

function select_landing($save_user_flow,$landings){
    $landing='';
    if ($save_user_flow && isset($_COOKIE['landing'])) {
        $landing = $_COOKIE['landing'];
        $t=array_search($landing, $landings);
        if ($t===false)
            $landing='';
    }
    if ($landing===''){
        //A-B тестирование лендингов
        $t = rand(0, count($landings) - 1);
        $landing = $landings[$t];
        ywbsetcookie('landing',$landing,'/');
    }
    return array($landing,$t);
}
?>