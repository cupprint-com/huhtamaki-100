<?php
# we define a secret here to try to prevent direct access to any included files
define('CP_HUHTAMAKI_RUNNING',1);
$base = dirname(__FILE__);
chdir($base);
require_once 'inc/service.php';


# all operational work is delegated to the service class
$service=new HuhtamakiCupprint();
$sanctioned=$service->sanctioned();
#print_r($sanctioned);
$paramKey="000011112222"; #nonsense default key
$output=0;

if (array_key_exists('key', $_REQUEST)){
    $paramKey=$_REQUEST['key'];
}
# if we have a query string called 'output' it means we want to write output to response (when in debug / dev mode)
if (array_key_exists('output', $_REQUEST)){
    $output=1;
}


if (isset($argv)){
    # print_r($argv);
    if ($argv[1]){
        $paramKey=$argv[1];
    }
}
$operationToExecute=UNAUTHORIZED_REQUEST;

if (array_key_exists($paramKey, $sanctioned)){
    $operationToExecute=$sanctioned[$paramKey];
}

$result=[];
switch ($operationToExecute){
    case UNAUTHORIZED_REQUEST:#
        break;
    case RENDER_FORM: # renders the html form that is used to capture user input
        $service->renderFormHtml();
        die();
        break;
    case CALCULATE_ESTIMATE: # handler for the html form submission
        $result=$service->processFormSubmission();
        $json=json_encode($result);
        echo $json;
        die();
        break;
    case GET_ESTIMATE: ## retrieves estimate
        $service->renderEstimate();
        die();
        break;
}


if (H100_DEBUG && $output){
    print_r($operationToExecute);
    $json=json_encode($result);
    echo $json;
}