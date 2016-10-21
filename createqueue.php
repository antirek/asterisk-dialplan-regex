<!--#!/usr/bin/env php -->
<?php
######################## README ########################
# cc2regex - createqueue.php
# Скрипт получает любой диапазон натуральных чисел одного порядка, и преобразует его в массив диапазонов, готовых к обработке с помощью regexcreator.
# При работе с целыми числами, не влезающими в int, вместо % и pow проще использовать BC Math или GMP. См. поиск в коде по *bc.
#
# Примеры:
#	MIN:	MAX:
#	2633722	2673388
#
# createqueue разбивает этот диапазон на список новых поддиапазонов (элементы 0 и 1, 2 и 3 и т.д.), а из них, в свою очередь, regexcreator делает регулярные выражения.
#
#	Результаты должны быть такими:
#	createqueue:	0	1	2	3	4	5	6	7	8	9	10	11	12	13	14	15	16	17
#			2633722	2633729	2633730	2633799	2633800	2633999	2634000	2639999	2640000	2669999	2670000	2672999	2673000	2673299	2673300	2673379	2673380	2673388
#	regexcreator:	_263372[2-9]	_26337[3-9]X	_2633[8-9]XX	_263[4-9]XXX	_26[4-6]XXXX	_267[0-2]XXX	_2673[0-2]XX	_26733[0-7]X	_267338[0-8]
#
# Наполнение списка:
#
# Шаг	Элементы списка														Новые элементы	Последний
# 1.	0																	1
#	2633722																	2673388
# 2.	0															1	2	3
#	2633722															2633729	2633730	2673388
# 3.	0	1	2													3	4	5
#	2633722	2633729	2633730													2633799	2633800	2673388
# 4.	0	1	2	3	4											5	6	7
#	2633722	2633729	2633730	2633799	2633800											2633999	2634000	2673388
# 5.	0	1	2	3	4	5	6									7	8	9
#	2633722	2633729	2633730	2633799	2633800	2633999	2634000									2639999	2640000	2673388
# 6.	0	1	2	3	4	5	6	7	8							9	10	11
#	2633722	2633729	2633730	2633799	2633800	2633999	2634000	2639999	2640000							2669999	2670000	2673388
# 7.	0	1	2	3	4	5	6	7	8	9	10					11	12	13
#	2633722	2633729	2633730	2633799	2633800	2633999	2634000	2639999	2640000	2669999	2670000					2672999	2673000	2673388
# 8.	0	1	2	3	4	5	6	7	8	9	10	11	12			13	14	15
#	2633722	2633729	2633730	2633799	2633800	2633999	2634000	2639999	2640000	2669999	2670000	2672999	2673000			2673299	2673300	2673388
# 9.	0	1	2	3	4	5	6	7	8	9	10	11	12	13	14	15	16	17
#	2633722	2633729	2633730	2633799	2633800	2633999	2634000	2639999	2640000	2669999	2670000	2672999	2673000	2673299	2673300	2673379	2673380	2673388
#
# Итоговый список и соответствующие регулярные выражения:
# 
# 0	2633722		_263372[2-9]
# 1	2633729
# 2	2633730		_26337[3-9]X
# 3	2633799
# 4	2633800		_2633[8-9]XX
# 5	2633999
# 6	2634000		_263[4-9]XXX
# 7	2639999
# 8	2640000		_26[4-6]XXXX
# 9	2669999
# 10	2670000		_267[0-2]XXX
# 11	2672999
# 12	2673000		_2673[0-2]XX
# 13	2673299
# 14	2673300		_26733[0-7]X
# 15	2673379
# 16	2673380		_267338[0-8]
# 17	2673388
# 
#######################################################

//echo "<br><b>START with ".$minimum." - ".$maximum."</b></br>";

function CreateQueue($minimum,$maximum) {

$sn="createqueue.php";
$debug=FALSE;
//$debug=TRUE;
if($debug) echo "<br><b>START with ".$minimum." - ".$maximum."</b></br>";


	$max=$MAX=$maximum;
	$min=$MIN=$minimum;

/*switch ($argc) {
	case 1:
		die("<br>\nUSAGE: $sn MIN MAX\n");
	case 3:
		$max=$MAX=$argv[2];
	case 2:
		$min=$MIN=$argv[1];
} */

####################################################
$beginfromnull=FALSE;
settype($min,"string");
settype($max,"string");

if($debug) echo"<br>\nMinumum: $min\nMaximum: $max\n";
//Проверка входных чисел и их изменение.
if(is_numeric($min)&&is_numeric($max)) {
	if($min<0||$max<0) die("<br>\nBAD DIA!!!\n");
	if($min>$max) {
		$min=$max;
		$max=$MIN;
	}
	if($debug) echo"Старый минимум:\t".$min."<br>\n";
	while(strlen($min)<strlen($max))
		$min="0".$min;
	if($debug) echo"Новый минимум:\t".$min."<br>\n";
	if($debug) echo"Новый максимум:\t".$max."<br>\n";
	$MIN=$min;
	$MAX=$max;
}
else die("<br>\nBAD DIA\n");

//*todo: Если в первом и во втором числе справа обнаружен общий префикс, обрабатываем числа без него, добавляем в конце.
//Если в числах слева нули, увеличиваем порядок чисел, после обработки уменьшаем. Увеличивать порядок не самая лучшая идея, но работает. В дальнейшем стоит от неё отказаться.

if(preg_match("/^0+\d*$/", $min)) {
	$beginfromnull=TRUE;
	$min="1".$min;
	$max="1".$max;
}


$myl=new SplDoublyLinkedList();
if($myl->isEmpty())
{
	$myl->push($min);
	$myl->push($max);
}

$myl->rewind();
$power=1;
$dlisfull=FALSE;
$expdown=FALSE;			//Если переменная установлена то в цикле не повышать степень, а понижать.

while(!$dlisfull) {
	if($debug) echo"<br>\n################################################################################################## USE index: ".$myl->key().", value: ".$myl->current()."\n";
	if(($myl->key())%2==1) { if($debug) echo "Нечётный ключ!<br>\n"; continue;}						//Если итератор нечётный, не обрабатываем элемент
	if($debug) echo"################################################# Степень числа ".$myl->current().": ".intval(log10($myl->current()))."<br>\n";
	// Переменная $power - степень десятки. Пока $k <= порядку числа + 1 обрабатываем числа (каждую пару, текущее и последнее).
	while(($power<=(intval(log10($myl->current()))+1))&&!$expdown) {
		if($debug) {
			echo"Используем диапазон ".$myl->current()." - ".$max.", степень = ".$power.", порядок = ".bcpow(10,$power)."<br>\n";		//Используем текущий диапазон.
			$krot=((($myl->current()))%((pow(10,$power))));
			echo"КРОТ:\t".$krot."<br>\n";
			echo"ТИП КРОТА:\t".gettype($krot)."<br>\n";
			$krot=bcmod(($myl->current()),(bcpow(10,$power)));
			echo"BC-КРОТ:\t".$krot."<br>\n";
			echo"ТИП BC-КРОТА:\t".gettype($krot)."<br>\n";
		}
		//если максимум-текущий элемент < текущего порядка например 12345 и 12347 то уменьшаем степень.
		if(($max - $myl->current())*10<(bcpow(10,$power))) {
			if($debug) echo"Разность:".($max - $myl->current())."<br>\n";
			if($debug) print_r($myl);
			if($debug) echo"Отсюда надо идти к уменьшению степени!<br>\n";
			$expdown=TRUE;
			break 1;
		}

		//Если текущий элемент не кратен текущей степени, то добавляем 2 новых элемента после текущего. Например после 1234 добавится 1239 и 1240.
//*bc		else if((($myl->current())%(pow(10,$power)))!=0) {
		else if(bcmod($myl->current(),bcpow(10,$power))!=0) {
			//Вычисление нового элемента. Для 1234: 1234 - 4 + 10 - 1 = 1239, второй новый - +1.
//*bc			$newmin =(($myl->current()) - ($myl->current())%(pow(10,$power)) + pow(10,$power) - 1);
			$newmin = bcsub(bcadd($myl->current(),bcpow(10,$power)),bcadd(bcmod($myl->current(),bcpow(10,$power)),1));
			if($debug) echo "Найденный ЭЛЕМЕНТ:\t\t".$newmin."\tТИП:".gettype($myl->current())."<br>\n";
			//если новый элемент равен максимальному, например 9999, то не добавляем элемент, уменьшать не надо, список готов!
			if($newmin==$max) {
				if($debug) echo"Новый элемент \"".$newmin."\"равен максимальному \"".$max."\"";
				if($debug) print_r($myl);
				if($debug) echo"Список готов!<br>\n";
				$dlisfull=TRUE;
				break 2;
			}
			//если новый элемент больше максимального (например текущий 1234, максимальный 1238)
			if($newmin>$max) {
				if($debug) {
					echo"Новый элемент ".$newmin." больше максимального ".$max.", какая-то шляпа.<br>\n";
					echo"Не шляпа, а как раз отсюда и идём к уменьшению степени!<br>\n";
					print_r($myl);
					echo"Отсюда надо идти к уменьшению степени!<br>\n";
				}
				$expdown=TRUE;
				break 1;
			}
//*bc			$newdia=$newmin+1;
			$newdia=bcadd($newmin,1);
			settype($newmin,"string");
			settype($newdia,"string");
			$myl->offsetSet($myl->key()+1,$newmin);				//добавляем новый элемент после текущего
			$myl->push($newdia);						//и следующий новый
			$myl->push($max);						//и последний (максимальный)
			break;
		}
		//если текущий элемент кратен текущей степени, то увеличивем её.
		else $power++;
	}
	if($expdown) {
		if($debug) echo"################################################# Понижаем степень!<br>\n";
		while($power>1) {
			$power--;
			if($debug) echo"Используем диапазон ".$myl->current()." - ".$max.", степень = ".$power.", порядок = ".bcpow(10,$power)."<br>\n";		//Используем текущий диапазон.
			if($debug) echo"Разность в диапазоне: \"".($max-$myl->current())."\" Порядок разности: \"".intval(log10($max - $myl->current()))."\"<br>\n";
			if($myl->current()==$max) {
				if($debug) echo "КОНЕЦ!<br>\n";
				$dlisfull=TRUE;
				break;
			}
			// Условие для диапазонов вида 2780000 - 2789999:
			if((($max-$myl->current()+1)==bcpow(10,$power))&&(($myl->current()%bcpow(10,$power))==0)) {
				if($debug) echo "КОНЕЦ!<br>\n";
				$dlisfull=TRUE;
				break;
			}
			// Условие для диапазонов вида 2750000 - 2789999:
			if((($myl->current()%bcpow(10,$power))==0)&&(($max-$myl->current()+1)%bcpow(10,$power)==0)) {
				if($debug) echo "КОНЕЦ!<br>\n";
				$dlisfull=TRUE;
				break;
			}
			// Условие для диапазонов типа 2673000 - 2673003 и высоких порядков: если порядок разности между max и current меньше текущего порядка, уменьшаем порядок
			if(intval(log10($max - $myl->current()))<$power) {
				if($debug) echo"Разные порядки<br>\n";
				continue;
			}
//*bc			$newmin=$max - $max%pow(10,$power) -1;
			$newmin=bcsub($max,bcadd(bcmod($max,bcpow(10,$power)),1));
			$newdia=bcadd($newmin,1);
			if($debug) echo"Новая пара элементов: ".$newmin."\t".$newdia."<br>\n";
			$myl->offsetSet($myl->key()+1,$newmin);				//добавляем новый элемент после текущего
			$myl->push($newdia);						//и следующий новый
			$myl->push($max);						//и последний (максимальный)
			$myl->next();
			$myl->next();
		}
		$dlisfull=TRUE;
	}
	if($debug) echo"Новый элемент добавлен: ".$newmin."<br>\n";
	$myl->next();
	$myl->next();
	if($debug) echo"Новый текущий элемент:\tindex: ".$myl->key().", value: ".$myl->current()."<br>\n";
	if($debug) print_r($myl);
}
if($debug) for($i=0;$i<3;$i++){
	echo" # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # #<br>\n";
	echo"# # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # #<br>\n";
}
if($debug) echo"################################################################################################## Текущий список:<br>\n";
if($debug) print_r($myl);

$myl->rewind();

if($debug) echo"Текущий элемент: ".$myl->key().", значение: ".$myl->current()."<br>\n";
if($debug) echo"<br>\n################################################################################################## РЕЗУЛЬТАТ:\n";
if($debug) echo"<br>\n################################################# Диапазон:\n";
if($debug) echo"<br>\n\t\t\t\t\t".$min." - ".$max."\n";
if($debug) echo"<br>\n\t\t\t\t\t".$MIN." - ".$MAX."\n";
if($debug) echo"<br>\n################################################# Регулярные выражения:\n\n";
$j=0;
foreach($myl as $i=>$value) {
	if($i%2==1)
		continue;						//Если итератор нечётный, не обрабатываем элемент
	if($beginfromnull)
		$regexpression=substr(RegexCreate($value,$myl->offsetGet($i+1)), 1);
	else
		$regexpression=RegexCreate($value,$myl->offsetGet($i+1));
	if($debug) echo"Поддиапазон и выражение:\t".($i/2)."\t".$value." - ".$myl->offsetGet($i+1)."\t".$regexpression."<br>\n";
	$regexpressions[$j]=$regexpression;
	$j++;
}
if($debug) print_r($regexpressions);
return($regexpressions);
}

//exit;

function RegexCreate($min,$max) {
	if(($min>$max)||(strlen($min)!=strlen($max))) {
		die("<br>\nERROR, BAD NUMBERS: $min $max\n");
	}
	$MIN=$min;
	$MAX=$max;
	$pref="";		//то, что добавить в префикс из диапазона, например "123"
	$regex="";
	$len=strlen($min);
	if($min==$max) {
		if($debug) echo"Числа равны!<br>\n";
		$regex=$min;
	}
	else for($i=0;$i<(strlen($min)+1);$i++) {				//$i всегда (ну, почти) 0, т.к. далее происходит $i--.
		$n1=substr($min, $i, 1);					//текущая обрабатываемая цифра
		$n2=substr($max, $i, 1);
		if($debug) echo"<br>\n##################### СИМВОЛ #$i: $n1\t$n2\n";
	
	//	Формирование префикса (123[4-6] или 123)
		if(strlen($pref)==$i+strlen($pref)) {				//нужно ли такое условие? по сути это проверка на $i==0;
			if($n1==$n2) {						//Если цифры равны (1 1, 2 2, 3 3),
				$pref=$pref.$n1;				// то прибывляем их к префиксу (123)
				$min=substr($min, 1);				// и отрезаем их от чисел. (4000 6999)
				$max=substr($max, 1);
				$i--;
			}
			else if($n1<$n2) {					//Если цифры не равны (4 6, 0 9)
				if(($n1!=0)||($n2!=9)) {			//Если это не (0 9) а (4 6)
					$pref=$pref."[".$n1."-".$n2."]";	// то добавляем диапазон к префиксу (123[4-6]), или вместо диапазона символ "X", в случае $n1=0 и $n2=9
					$min=substr($min, 1);			// отрезаем их от чисел (000 999)
					$max=substr($max, 1);
				}
				if($debug) echo"Префикс сформирован: \"$pref\"<br>\n";
				if($i==strlen($min))
					$regex=$pref;
			}
		}
		//После того, как сформировали префикс (123[4-6] или 1234) остаются варианты
		//Равный остаток (922 922) - только в случае использования квадратных скобок:
		else if($min==$max) {
			$regex=$pref.$min;
			if($debug) echo"Числа в правых частях равны!\tMIN: $min<br>\n";
			break;
		}
		//Остаток вида XXX (000 999)
		else if((strspn($min,"0")==strlen($min))&&(strspn($max,"9")==strlen($max))) {
			if($debug) echo"Числа в правых частях нули и девятки!\tMIN:$min\tMAX:\t$max<br>\n";
			$xx=str_replace("0","X",$min);
			$regex=$pref.$xx;
			break;
		}
		else {
			die("<br>\nERROR, BAD NUMBERS: $MIN $MAX\nPREFIX: $pref\n");
		}
	}
	return($regex);
}

?>

