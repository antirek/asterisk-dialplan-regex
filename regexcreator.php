#!/usr/bin/env php
<?php
######################## README ########################
# cc2regex - regexcreator.php
# Скрипт создаёт только регулярки вида _123[4-6]XXX. На вход принимает диапазон из 2-х чисел, например здесь 1234000 и 1236999.
# Примеры правильных диапазонов:
# 	MIN:		MAX:		Результат:
# 	1234000		1236999		"_123[4-6]XXX"
# 	1230		1235		"_123[0-5]"
# 	2345600 	2345699		"_23456XX"
# Примеры неправильных диапазонов:
# 	1234400		1235500
#	1230000		1240000
# 
########################################################

$sn="regexcreator.php";


$pref="";		//то, что добавить в префикс из диапазона, например "123"
$prefX="";		//второй статический префикс перед первым, например символ "_"
$regex="";

switch ($argc) {
	case 1:
		echo("USAGE: $sn MIN MAX\n");
		die;
	case 3:
		$max=$MAX=$argv[2];
		settype($max,"integer");
	case 2:
		$min=$MIN=$argv[1];
		settype($min,"integer");
}
echo"Minumum: $min\tMaximum: $max\n";
echo"Minumum: ".gettype($min)."\tMaximum: ".gettype($max)."\n";

if(	($min>$max)||
	(strlen($min)!=strlen($max))
	) {
	die("\nERROR, BAD NUMBERS: $min $max\n");
}
// Добавить проверку на равенство кол-ва цифр, добавить проверку на min>max, проверку на цифры

$len=strlen($min);
echo"Length: $len\n";

if($min==$max) {
	echo"Числа равны!\n";
	$regex=$min;
}
else for($i=0;$i<(strlen($min)+1);$i++) {				//$i всегда (ну, почти) 0, т.к. далее происходит $i--.
	$n1=substr($min, $i, 1);				//текущая обрабатываемая цифра
	$n2=substr($max, $i, 1);
	echo"\n##################### СИМВОЛ #$i: $n1\t$n2\n";

//	Формирование префикса (123[4-6] или 123)
	if(strlen($pref)==$i+strlen($pref)) {				//нужно ли такое условие? по сути это проверка на $i==0;
		if($n1==$n2) {						//Если цифры равны (1 1, 2 2, 3 3),
			$pref=$pref.$n1;				// то прибывляем их к префиксу (123)
			$min=substr($min, 1);				// и отрезаем их от чисел. (4000 6999)
			$max=substr($max, 1);
			echo"New Min: $min\n";
			echo"New Max: $max\n";
			$i--;
		}
		else if($n1<$n2) {					//Если цифры не равны (4 6, 0 9)
			if(($n1!=0)||($n2!=9)) {			//Если это не (0 9) а (4 6)
				$pref=$pref."[".$n1."-".$n2."]";	// то добавляем диапазон к префиксу (123[4-6]), или вместо диапазона символ "X", в случае $n1=0 и $n2=9
				$min=substr($min, 1);			// отрезаем их от чисел (000 999)
				$max=substr($max, 1);
			}
			echo"New Min: $min\n";
			echo"New Max: $max\n";
			echo"Префикс сформирован: \"$pref\"\n";
			if($i==strlen($min))
				$regex=$pref;
		}
	}
	//После того, как сформировали префикс (123[4-6] или 1234) остаются варианты
	//Равный остаток (922 922) - только в случае использования квадратных скобок:
	else if($min==$max) {
		$regex=$pref.$min;
		echo"Числа в правых частях равны!\tMIN: $min\n";
		break;
	}
	//Остаток вида XXX (000 999)
	else if((strspn($min,"0")==strlen($min))&&(strspn($max,"9")==strlen($max))) {
		echo"Числа в правых частях нули и девятки!\tMIN:$min\tMAX:\t$max\n";
		$xx=str_replace("0","X",$min);
		$regex=$pref.$xx;
		break;
	}
	else {
//		return"ERROR";
		die("\nERROR, BAD NUMBERS: $MIN $MAX\nPREFIX: $pref\n");
	}
}
//if(!isset($regex)) $regex=$pref;

echo"##################### Verbose:\n";
$lenpref="";
for($i=0;$i<strlen($pref);$i++) {
	$lenpref=$lenpref." ";
}

echo"MIN =         \"_$MIN\"\n";
echo"Остаток MIN = \"_$lenpref$min\"\n";
echo"MAX =         \"_$MAX\"\n";
echo"Остаток MAX = \"_$lenpref$max\"\n";

echo"REG =         \"_$pref\"\n";
echo"OLDREGEX =    \"_$pref$prefX\"\n";
echo"REGEX =       \"_$regex\"\n";

?>

