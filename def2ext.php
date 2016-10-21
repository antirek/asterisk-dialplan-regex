#!/usr/bin/env php
<?php
######################## README ########################
# First read http://www.rossvyaz.ru/activity/num_resurs/registerNum/
# This script get files from http://www.rossvyaz.ru/docs/articles/ and then create regular expressions for phone numbers.
#
# def2ext.php - Скрипт, запускаемый пользователем.
# cc2regex
#
# regionlist:
# awk -F ';' '{print $6}' .code/DEF-9x.html.csv|sort|uniq > regionlist-DEF-9x
########################################################

######################## DEFAULTS ########################
$sn="def2ext.php";

$urldir="http://www.rossvyaz.ru/docs/articles";

$tmpdir="./.tmp";
$workdir="./.code";

$resultsyntax="asterisk";

$debug=FALSE;

##########################################################

######################## Variables ########################
$code=array (null,null,null,"ABC-3x.html","ABC-4x.html",null,null,null,"ABC-8x.html","DEF-9x.html");
$a=0;
$b=count($code);
###########################################################

switch ($argc) {
	case 1:
		echo "By default uses ALL numbers.\n";
		echo "USAGE: $sn region [code] [asterisk|asterisksql [filename]]\n";
		echo "NOTE: Use [code] as 1 digit, example:\n";
		echo "\t$sn Санкт-Петербург 9 asterisk\n";
	    die;
	    break;
	case 3:
		for($i=0;$i<count($code);$i++) {
			if(!isset($code[$i])) continue;
			if($argv[2]==$i)
			{
				$c=$i;
				break;
			}
		}
		if(!isset($c))
			die("ERROR, Unknown code \"$argv[2]\"");
	case 2:
		$reg=$argv[1];
}

if(!file_exists($tmpdir))
	mkdir($tmpdir);
if(!file_exists($workdir))
	mkdir($workdir);

if(isset($c)) {
	$a=$c;
	$b=$c+1;
} else $c="all";

for($i=$a;$i<$b;$i++) {
	if(!isset($code[$i])) continue;
	DownAndChEnc($code[$i]);
	CrRegexpFile($code[$i],$reg);
}


/*
Добавить проверку файлов на свежесть, варианты:
a) stat("$file") должен быть не старее, чем сегодняшняя дата date() минус 2 недели
б) wget -N
*/
function DownAndChEnc($f) {
	global $tmpdir, $workdir, $urldir;
//	if(!file_exists("$tmpdir/$f"))
		exec("wget -N -P $tmpdir $urldir/$f");
// grep "^<tr>" DEF-9x.html|awk -F '\t' '{print $3";"$6";"$9";"$12";"$15";"$18}'| iconv -f WINDOWS-1251 -t UTF-8
//	if(!file_exists("$workdir/$f.csv"))
		exec("grep \"^<tr>\" $tmpdir/$f | awk -F '\t' '{print $3\";\"$6\";\"$9\";\"$12\";\"$15\";\"$18}'| iconv -f WINDOWS-1251 -t UTF-8 -o $workdir/$f.csv");
}

/*
В workdir/* из $2 и $3 делать regexp.

Т.Е.


*/
function CrRegexpFile($f,$region) {
	global $workdir, $tmpdir;
	if(!file_exists("$tmpdir/regions"))
		mkdir("$tmpdir/regions");
echo "################ DEBUG: CRREGEXP REGION = /$region/\n";
echo "################ DEBUG: FILE = $f\n";
echo ("################ DEBUG:\n awk -F ';' '{if($6~/^$region$/) print $1\";\"$2\";\"$3\";\"$6}' $workdir/$f.csv > $tmpdir/regions/$f-$region.csv\n");
	exec("awk -F ';' '{if($6~/^$region$/) print $1\";\"$2\";\"$3\";\"$6}' $workdir/$f.csv > $tmpdir/regions/$f-$region.csv");
}

function RegexpressionsByRegion($f,$region) {
	
	
}

#######
# Result table:
# code;firstnum;lastnum;
#######
if($debug) echo "################ DEBUG: Region = $reg\n";
if($debug) echo "################ DEBUG: code = $c\n";

?>

