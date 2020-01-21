<?php 
if(!defined('CP_HUHTAMAKI_RUNNING')){
    exit(1);
}
# check if development file exists (must be excluded from GIT)
$filename=dirname(__FILE__) . '/development.txt';

$localDatabaseHost='localhost';
$localDatabaseName='automation';
$localDatabaseUser='automation';
$localDatabasePassword='ugnTsJGdU5aF';
$upsApiUrl='https://onlinetools.ups.com/ship/v1801/rating/shop';
$debugging=0;

if (file_exists($filename)){
    $localDatabaseName='automation_development';
    $debugging=1;
}

#define constants used for database connectivity
define('H100_HOST',$localDatabaseHost);
define('H100_DATABASE',$localDatabaseName);
define('H100_USERNAME',$localDatabaseUser);
define('H100_PASSWORD',$localDatabasePassword);
define('H100_DEBUG',$debugging);


# constants used for invoking the UPS api
define('UPS_API',$upsApiUrl);
define('UPS_LICENSE','8D7516C6974428F0');
define('UPS_USERNAME','Cupprint1');
define('UPS_PASSWORD','Shipping1*');
define('UPS_SOURCE','CupPrint');
define('UPS_SHIPPER_NUMBER','A1340R');
define('UPS_CUPPRINT_ADDRESS','Unit 4/5 Block C');
define('UPS_CUPPRINT_CITY','Ennis');
define('UPS_CUPPRINT_STATE','');
define('UPS_CUPPRINT_ZIP','V95 NN60');
define('UPS_CUPPRINT_COUNTRY','IE');
# MARKUP to be applied to freight cost
define('UPS_FREIGHT_MARKUP',1.15); #15% markup


define('CPC8DW_WEIGHT',15.5);
define('CPC8DW_PACKAGE_LENGTH',"57");
define('CPC8DW_PACKAGE_WIDTH',"33");
define('CPC8DW_PACKAGE_HEIGHT',"41");


define('CPC12DW_WEIGHT',20.5);
define('CPC12DW_PACKAGE_LENGTH',"58");
define('CPC12DW_PACKAGE_WIDTH',"38");
define('CPC12DW_PACKAGE_HEIGHT',"46");



///////////// NOTE : any keys added here MUST also be added to the 'sanctioned' set in service.php ////////////////////////
define('UNAUTHORIZED_REQUEST',0);
define('RENDER_FORM',40000);
define('RENDER_BU',40002);
define('CALCULATE_ESTIMATE',40010);
define('GET_ESTIMATE',40011);
define('SAVE_ESTIMATE',40012);
define('CALCULATE_FREIGHT',40013);

define('KEY_RENDER_FORM','UnCp2kE8sqkY4MW4');
define('KEY_CALCULATE_ESTIMATE','gzMSmqCmET7CYvxE');
define('KEY_GET_ESTIMATE','cs3NGXzpmrrtGAE4');
define('KEY_SAVE_ESTIMATE','wsGcTtTJXfckrK5C');
define('KEY_CALCULATE_FREIGHT','ayn6daWB8xFDEmPT');

define('CPC8DW_PRICE',0.06);
define('CPC12DW_PRICE',0.07);

