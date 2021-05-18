<?php
use Noodlehaus\Config;
use Noodlehaus\Writer\Json;
require_once '../config/ConfigInterface.php';
require_once '../config/AbstractConfig.php';
require_once '../config/Config.php';
require_once '../config/Parser/ParserInterface.php';
require_once '../config/Parser/Json.php';
require_once '../config/Writer/WriterInterface.php';
require_once '../config/Writer/AbstractWriter.php';
require_once '../config/Writer/Json.php';
require_once '../config/ErrorException.php';
require_once '../config/Exception.php';
require_once '../config/Exception/ParseException.php';
require_once '../config/Exception/FileNotFoundException.php';
require_once '../redirect.php';

require('../settings.php');
if ($log_password!==''&&(empty($_GET['password'])||$_GET['password'] !== $log_password)) {
    echo 'No Password For Settings Save!';
    exit();
}
$conf = new Config('../settings.json');
foreach($_POST as $key=>$value){
    $confkey=str_replace('_','.',$key);
    if (is_string($value)&&is_array($conf[$confkey])){
        $value=explode(',',$value);
        $conf[$confkey]=$value;
    }
    else if ($value==='false'|| $value==='true'){
        $value=filter_var($value,FILTER_VALIDATE_BOOLEAN);
        $conf[$confkey]=$value;
    }
    else if ($value===''&&$conf[$confkey]===[]){
        $value=[];
        $conf[$confkey]=$value;
    }
    else if (is_array($value)){
        $conf[$confkey]=$value;
    }
    else{
        $conf[$confkey]=$value;
    }

}
$conf->toFile('../settings.json',new Json());
require('../settings.php');
redirect('settings.php?password='.$log_password,302,false);
?>