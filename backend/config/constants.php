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
/**
 * 



ayn6daWB8xFDEmPT
*/

///////////// NOTE : any keys added here MUST also be added to the 'sanctioned' set in service.php ////////////////////////
define('UNAUTHORIZED_REQUEST',0);
define('RENDER_FORM',40000);
define('RENDER_BU',40002);
define('CALCULATE_ESTIMATE',40010);
define('GET_ESTIMATE',40011);
define('SAVE_ESTIMATE',40012);


define('KEY_RENDER_FORM','UnCp2kE8sqkY4MW4');
define('KEY_CALCULATE_ESTIMATE','gzMSmqCmET7CYvxE');
define('KEY_GET_ESTIMATE','cs3NGXzpmrrtGAE4');
define('KEY_SAVE_ESTIMATE','wsGcTtTJXfckrK5C');

define('CPC8DW_PRICE',0.06);
define('CPC12DW_PRICE',0.07);
