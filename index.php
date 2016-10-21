<html>
<style type="text/css">
tr:hover {background:#e0e0e0}
</style>
<body>
<!--#!/usr/bin/env php -->
<?php

echo "<meta http-equiv=\"Content-Type\" content=\"text/html;charset=UTF-8\">";

$defcodes=$_GET[defcodes];
$dianums=$_GET[dianums];

if(isset($dianums)) {
//	echo "Выбран диапазон<br>";
//	echo "<br>: ".$dianums;
	$minimum=$_GET["minimum"];
	$maximum=$_GET["maximum"];
	ReturnRegexesiByDia($minimum,$maximum);
}
else if(isset($defcodes)) {
	ReturnRegexesByReg($defcodes);
}
else
	echo "<br>Выберите действие</br>";




//echo "<br><b>Регулярные выражения:</b></br>";

echo "<br>Для телефонных номеров РФ в коде <b>DEF</b> (9) укажите регион:</br>\n";

echo "<form method=\"get\" action=\"index.php\">";
echo "<br><select name=\"defcodes\" size=\"1\">";
$regionlist = fopen ('.code/regionlist-DEF-9x', 'r');
while (!feof($regionlist)) {
        $r = fgets($regionlist, 256);
        if(feof($regionlist)) break;
        echo "<option value=\"".$r;
	echo "\">".$r;
	echo "</option>";
}
fclose($regionlist);
echo "</select>
	<br><input type=\"submit\" value=\"Отправить\">
	</form>
	<br>";


echo "<br>Или введите произвольный диапазон чисел (длиной не более 14 знаков):</br>";
echo "<form method=\"get\" action=\"index.php\">
	<input type=\"hidden\" name=\"dianums\" value=\"1\">
	<tr>
		<td><input type=\"text\" name=\"minimum\" size=\"15\" maxlength=\"14\" pattern=\"^\d+$\"></td>
		<td> - <td>
		<td><input type=\"text\" name=\"maximum\" size=\"15\" maxlength=\"14\" pattern=\"^\d+$\"></td>
	</tr><br>
	<p><input type=\"submit\" value=\"Отправить\"></p>
	</form>";


function ReturnRegexesiByDia($minimum,$maximum) {
	echo "<br><b>Введённый диапазон:</b>";
	echo "<br><tt>".$minimum." - ".$maximum."</tt></br>";
	if((strlen($minimum)>14)||(strlen($maximum)>14)) {
		echo "<br>К сожалению, пока что длина чисел не может превышать 14 знаков.";
		return;
	}
	require_once("createqueue.php");
//	echo "<br><b>Обработанный диапазон:</b>";
//	echo "<br><tt>".$MIN." - ".$MAX."</tt></br>";
	echo "<br><b>Полученные регулярные выражения:</b><br><br>";
	$regexpressions=CreateQueue($minimum,$maximum);
	foreach($regexpressions as $i=>$value) {
		echo"<tt>_".$value."</tt><br>";
	}
}

function ReturnRegexesByReg($region) {
	$f="DEF-9x.html";
	$tmpdir="./.tmp";
	$workdir="./.code";

	echo "<br><b>Выбранный регион:</b>";
	echo "<br><tt>\"".trim($region)."\"</tt></br>";

//	$regionEn=translitIt(trim($region));

//	echo "<br><tt>\"".$regionEn."\"</tt></br>";
//	if(!file_exists("$tmpdir/$f-$regionEn.csv")) {
//		exec("awk -F ';' '{if($6~/^".trim($region)."$/) print $1\";\"$2\";\"$3\";\"$6}' $workdir/$f.csv > $tmpdir/regions/$f-$regionEn.csv");
		$alldialist = fopen ("$workdir/$f.csv", "r");
		echo "<br><b>Найденные диапазоны:</b></br>";
/*	
		echo "<ul>
			<li>Название 1</li>
			<li onClick=\"toggle_show(\'bla-bla2\')\">Название 2
				<ul id=\"bla-bla2\" style=\"display: none\">
					<li>Бла Бла Бла</li>
					<li>Бла Бла Бла</li>
					<li>Бла Бла Бла</li>
				</ul>
			</li>
			<li>Название 3</li>
			</ul>";
*/	
		while (!feof($alldialist)) {
		        $diastr = fgets($alldialist, 256);
			$r=explode(";","$diastr");
			if(trim($r[5])==trim($region)) {
				echo"<tt>".$diastr."</tt><br>";
			}
		}
		fclose($regionlist);
		echo "<br><b>Полученные регулярные выражения:</b><br><br>";
		$alldialist = fopen ("$workdir/$f.csv", "r");
		while (!feof($alldialist)) {
//		        if(feof($alldialist)) break;
		        $diastr = fgets($alldialist, 256);
			$r=explode(";","$diastr");
			if(trim($r[5])==trim($region)) {
//				echo"<tt>".$diastr."</tt><br>";
				$minimum=$r[1];
				$maximum=$r[2];
				require_once("createqueue.php");
//				echo "<br>Обработанный диапазон:";
//				echo "<br><tt>".$MIN." - ".$MAX."</tt></br>";
	//			echo "<br><b>Полученные регулярные выражения:</b><br><br>";
				$regexpressions=CreateQueue($minimum,$maximum);
				foreach($regexpressions as $i=>$value) {
					echo"<tt>_".$r[0].$value."</tt><br>";
				}
			}
		}
		fclose($regionlist);
		
		echo "<br><b>Конец!</b></br>";
//	}
//	echo "<br><b>Имя файла: \"".$f."-".trim($regionEn).".csv\"</b>";

}
	
/*function translitIt($str) {
    $tr = array(
	"А"=>"A","Б"=>"B","В"=>"V","Г"=>"G",
	"Д"=>"D","Е"=>"E","Ж"=>"J","З"=>"Z","И"=>"I",
	"Й"=>"Y","К"=>"K","Л"=>"L","М"=>"M","Н"=>"N",
	"О"=>"O","П"=>"P","Р"=>"R","С"=>"S","Т"=>"T",
	"У"=>"U","Ф"=>"F","Х"=>"H","Ц"=>"TS","Ч"=>"CH",
	"Ш"=>"SH","Щ"=>"SCH","Ъ"=>"","Ы"=>"YI","Ь"=>"",
	"Э"=>"E","Ю"=>"YU","Я"=>"YA","а"=>"a","б"=>"b",
	"в"=>"v","г"=>"g","д"=>"d","е"=>"e","ж"=>"j",
	"з"=>"z","и"=>"i","й"=>"y","к"=>"k","л"=>"l",
	"м"=>"m","н"=>"n","о"=>"o","п"=>"p","р"=>"r",
	"с"=>"s","т"=>"t","у"=>"u","ф"=>"f","х"=>"h",
	"ц"=>"ts","ч"=>"ch","ш"=>"sh","щ"=>"sch","ъ"=>"y",
	"ы"=>"yi","ь"=>"","э"=>"e","ю"=>"yu","я"=>"ya",
	"|"=>"-"," "=>"_","("=>"_",")"=>"_"		//,"\n"=>"eol","\$"=>"bol"
    );
    return strtr($str,$tr);
}*/


?>
