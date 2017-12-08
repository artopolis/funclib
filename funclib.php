<?php $FuncLibVersion='ver01.010'; 

//============================================================================================
// Библиотека моих функций, которые должны ставиться на любой сайт в INCLUDE
// для wordpress'а помещается в корень и подключается в wp-load.php в секции if ( file_exists( ABSPATH . 'wp-config.php') )
//
//	arrays $arrMonth,$arrBrowser
//	функции
//	print_rr			— форматирует вывод на экран массива или объекта
//	getAllParams                    — возвращает значения всех параметров POST и GET
//	getAllParamNames                — возвращает массив всех имен параметров POST и GET
//	getParam			— возвращает определенный именем параметр из POST и GET
//
//	setSession			— открывает сессию
//	getSession			— возвращает массив сессии
//
//	checkBrowser                    — проверяет тип браузера
//	noRun 				— защита от запуска скрипта через POST
//	fullDate			— возращает полную строку даты из getstamp
//      parseDate			— возращает разные виды форматированных дат (без времени)
// 	getDay				— возвращает день недели по дате в формате DD-MM-YYYY
//	remove_focus                    — удаляет пунктирную рамку вокруг фокусного item
//	is_page				— проверяет наличие POST в ссылке
//	get_page			— получает последний аргумент после последнего слэша в POST
//	rstr				— удаляет последний знак в строке
//	cut_ext				— удаляет раширение в названии файла
//	formParas			— вставляет указанные тэги перед каждым параграфом и после него
//	
//	
//=============================================================================================	

	
$arrMonth = array("01" => "января", "02" => "февраля", "03" => "марта", "04" => "апреля", "05" => "мая", "06" => "июня", "07" => "июля", "08" => "августа", "09" => "сентября", "10" => "октября", "11" => "ноября", "12" => "декабря");
$arrBrowser = array("msie", "mozilla", "firefox", "opera", "netscape");

	
// Возвращает ECHO переменных, массивов и объектов
// @arg — массив, переменная или объект
// @name — Название массива, переменной или объекта, которое будет выведено на дисплей; опционально
// @recur — в обратном порядке; по умолчанию — выключено
function print_rr($arg,$name="",$recur=0,$color="black") {
	if(is_object($arg)) {
		if(!$recur) $first="font-size:medium;";
		else $first="";
		echo "<p style='font-weight:bold;".$first."text-align:left;margin-top:5px;margin-bottom:0px;line-height:100%;color:".$color.";'>This is an object <span style='color:maroon;'>$name</span></p>";
		echo "<ul style='text-align:left;margin-top:0px;color:".$color.";'>";		
		foreach($arg as $key => $value) {
			echo "<li style='margin-top:0;margin-bottom:0;color:".$color.";'>";
			if(is_object($arg->$key)) print_rr($arg->$key,$key,$recur=1,$color);
			elseif(is_array($arg->$key)) print_rr($arg->$key,$key,$recur=1,$color);
			else echo $key.' => '.$arg->$key.'<br>';
			echo "</li>";
		}
		echo "</ul>";
	} 	elseif(is_array($arg)) {
		echo "<p style='text-align:left;margin-top:5px;margin-bottom:0px;line-height:100%;color:".$color.";'>This is an array: <span style='color:#6A004C;'>$name</span></p>";
		$count = count($arg);
		if($count > 0){
			echo "<ul style='text-align:left;margin-top:0px;'>";
			foreach ($arg as $key => $value) {
				echo "<li style='margin-top:0;margin-bottom:0color:".$color.";;'>";
				if(is_array($value)) {
					print_rr($value,$key,$recur=1,$color);
				} elseif(is_object($value)) {
					print_rr($value,$key,$recur=1,$color);
//					echo "<BR>";
				} else echo "<p style='text-align:left;margin-top:0px;margin-bottom:0px;line-height:100%;color:".$color.";'><b>$key</b> => $value</p>";
				echo "</li>";
			}	
			echo "</ul>";
		}	
	} else echo "$name$arg <br>";	
}

// Возвращает массив со всеми параметрами вызова — и POST и GET
//
//
function getAllParams() {
	global $_GET, $_POST;
	$arrParams = array();

	while( list($strParamNamePARA, $anyParamValue) = each($_POST) ) {
# 		Изменение названия параметров POST - все названия параметров переводятся в мелкие буквы, потому что имена одноименных полей в MySQL возвращаются только в lower_case 
		$strParamName=strtolower($strParamNamePARA);
		if (is_array($_POST[$strParamNamePARA])) {
			foreach ($_POST[$strParamNamePARA] as $anyKey => $anyValue) {
				$arrParams[$strParamName][$anyKey] = $anyValue;
			}
		}
		else {
			$anyParamValue = $_POST[$strParamNamePARA];
			if ( get_magic_quotes_gpc() ) {
				$anyParamValue = stripslashes($anyParamValue);
			}
			$anyParamValue = str_replace ( array ( '&amp;' , '&quot;', '&apos;' , '&lt;' , '&gt;' ), array ( '&', '"', "'", '<', '>' ), $anyParamValue );
			$arrParams[$strParamName] = $anyParamValue;
		}
	}

	while( list($strParamNamePARA, $anyParamValue) = each($_GET) ) {
# 		Изменениее названия параметров GET
		$strParamName=strtolower($strParamNamePARA);
		$anyParamValue = $_GET[$strParamNamePARA];
		if ( get_magic_quotes_gpc() ) {
			$anyParamValue = stripslashes($anyParamValue);
		}
		$anyParamValue = str_replace ( array ( '&amp;' , '&quot;', '&apos;' , '&lt;' , '&gt;' ), array ( '&', '"', "'", '<', '>' ), $anyParamValue );
		$arrParams[$strParamName] = $anyParamValue;
	}
	reset($_POST);
	reset($_GET);

	return $arrParams;
}

// Возвращает массив со всеми именами параметров и POST и GET
//
//
function getAllParamNames($separ='',$decoded=0,$prn=0) {
	global $_GET, $_POST;
	$arrPNames = array();
	$arrParams = array();
	$arrParams = getAllParams();
	
	while(list($key,$val) = each($arrParams)) {
//		$key=str_replace("/","",$key);
		if($separ) $key=substr(strrchr($key, $separ), 1);	
		if($decoded) $arrPNames[] = urldecode($key);
		else $arrPNames[] = $key;
	}

	if($prn) print_rr($arrPNames,'arrParamNames: ');
	return $arrPNames;
	
}

// Возвращает значение параметра (POST или GET) по его имени
//
//
function getParam($strParamName) {
	global $_GET, $_POST;

	$anyParamValue = "";

	if ( isset($_POST[$strParamName]) ) {
		$anyParamValue = $_POST[$strParamName];
	}
	else if ( isset($_GET[$strParamName]) ) {
		$anyParamValue = $_GET[$strParamName];
	}

	if ( get_magic_quotes_gpc() ) {
		$anyParamValue = stripslashes($anyParamValue);
	}

	$anyParamValue = str_replace ( array ( '&amp;' , '&quot;', '&apos;' , '&lt;' , '&gt;' ), array ( '&', '"', "'", '<', '>' ), $anyParamValue );

	return $anyParamValue;
}

// set session
function setSession($strSessionName, $anySessionValue) {
	global $_SESSION;
	global ${$strSessionName};

	if(session_is_registered($strSessionName)) {
		session_unregister($strSessionName);
	}

	${$strSessionName} = $anySessionValue;

	session_register($strSessionName);

	if (isset($_SESSION[$strSessionName])) {
		unset($_SESSION[$strSessionName]);
	}
  
	$_SESSION[$strSessionName] = $anySessionValue;
}

// get session
function getSession($strSessionName) {
	global $HTTP_POST_VARS, $HTTP_GET_VARS, $_SESSION;
	global ${$strSessionName};

	$anySessionValue = "";

	if( !isset($HTTP_POST_VARS[$strSessionName]) && !isset($HTTP_GET_VARS[$strSessionName]) && isset($_SESSION[$strSessionName]) )  {
		$anySessionValue = $_SESSION[$strSessionName];
	}

	return $anySessionValue;
}

#===================================================
#  Проверка типа броузера для совместимости
#===================================================
function checkBrowser()
{
	global $HTTP_USER_AGENT ;

	if ( !isset( $_SERVER ) ) {
		global $HTTP_SERVER_VARS ;
	    $_SERVER = $HTTP_SERVER_VARS ;
	}

	if ( isset( $HTTP_USER_AGENT ) )
		$sAgent = $HTTP_USER_AGENT ;
	else
		$sAgent = $_SERVER['HTTP_USER_AGENT'] ;

 	if ( strpos($sAgent, 'MSIE') !== false && strpos($sAgent, 'mac') === false && strpos($sAgent, 'Opera') === false )
	{
		$iVersion = substr($sAgent, strpos($sAgent, 'MSIE') + 0, 4) ;
		$ret = $iVersion;
	}
	else if ( strpos($sAgent, 'Gecko/') !== false )
	{
		$iVersion = substr($sAgent, strpos($sAgent, 'Gecko/') + 0, 5) ;
		$ret = $iVersion;
	}
	else if ( strpos($sAgent, 'Opera/') !== false )
	{
		$fVersion = substr($sAgent, strpos($sAgent, 'Opera/') + 0, 5) ;
		$ret = $fVersion;
	}
	else if ( preg_match( "|AppleWebKit/(\d+)|i", $sAgent, $matches ) )
	{
		$iVersion = $matches[1] ;
//		return ( $matches[1] >= 522 ) ;
//		echo $sAgent;
		$ret = "Macintosh";
	}
	else
		$ret = false ;
//    echo "RET:&nbsp;".$ret;
    return $ret;

}

//защита от запуска скрипта 
//посторонними через http:// имя скрипта
function noRun($fScriptName) {
	if (eregi($fScriptName,$PHP_SELF)) {
	    header("Location: index.php");
	    die();
	}
}

//===================================================================================================
// Функция возвращает строку с полной датой
// (в формате "ДНд, ДМс-Мес-Гд Час:Мин:Сек GMT")
//===================================================================================================
function fullDate($timestamp) {
	$arrDateArray=getdate($timestamp);
	$strOutStr=$arrDateArray['weekday'].", ".$arrDateArray['mday']."-".(substr($arrDateArray['month'],0,3))."-".(substr($arrDateArray['year'],2,2))." ".$arrDateArray['hours'].":".$arrDateArray['minutes'].":".$arrDateArray['seconds']." GMT";
	return $strOutStr;
}

//===================================================================================================
// Функция возвращает строку с краткой датой без времени.
// @date — или формат yyyy-mm-dd:h:min:sec или datastamp (в т.ч. из функции getdate()) 
// @opt — формат 1: год, 2: месяц, 3: число, 4: день недели, 5: число месяц, 6: месяц(назв.) год, 7: число месяц(назв.) год, 8: число месяц(назв.) год, д.нед. 9: месяц, род.падеж
// если OPT 0 или NULL — возвращается строка DD-MM-YYYY 
// @short — краткая или полная форма — 0 или NULL: полная форма; 1: мес., 2: д/нед. 3: мес.,д/нед.
//===================================================================================================

function parseDate($date,$opt=0,$short=0) {
//echo "Date param: ".$date."<br>";
	$result="";	
    $week = array("воскресенье", "понедельник", "вторник", "среда", "четверг", "пятница", "суббота");
    $sh_week = array("вск", "пнд", "втр", "срд", "чтв", "птн", "сбт");
    $arrMonth = array("январь", "февраль", "март", "апрель", "май", "июнь", "июль", "август", "сентябрь", "октябрь", "ноябрь", "декабрь");
    $sh_month = array("янв", "фев", "мар", "апр", "май", "июн", "июл", "авг", "сен", "окт", "ноя", "дек");
	if(!$short) $short=0; 
	if(!$opt) $opt=0;

	if(is_string($date)===true) {
		$numPunkt=stripos($date,":");	
		if($numPunkt) $strKillTime=substr($date,0,$numPunkt-2);
		//echo "<p style='color: red;'>strKillTime: ".$strKillTime."</p><BR>";
		$arrDate=explode('-',$date);
	} elseif(is_array($date)===true){
		$arrDate[0]=$date['year'];
		$arrDate[1]=$date['mon'];
		$arrDate[2]=$date['mday'];
	}
	
//print_rr($arrDate,'arrDate:');
	$year=$arrDate[0];
	$monthNum=(int)$arrDate[1]-1; // при расчете для массива месяцев, где есть 0-1 элемент
//echo "monthNum: ".$monthNum."<BR>";
	$dayNum=$arrDate[2];
	$weekNum=getDay($dayNum, $arrDate[1], $year); // используется реальный номер месяца
//echo "Week: ".	$weekNum;

	if($short>0) {
		if($short==1) {
			$monthStr=$sh_month[$monthNum];
			$dayStr=$week[$weekNum];
		}
		if($short==2) {
			$monthStr=$arrMonth[$monthNum];
			$dayStr=$sh_week[$weekNum];
		}
		if($short==3) {
			$monthStr=$sh_month[$monthNum];
			$dayStr=$sh_week[$weekNum];
		}
	} else {
		$dayStr=$week[$weekNum];
		$monthStr=$arrMonth[$monthNum];
//echo "<BR>monthStr: ".$monthStr."<BR>";
	}


	switch ($opt) {
		case 1:
			$result=$year;
			break;
		case 2:
//			if(!$short || $short==3) {
//				if($monthNum==2 || $monthNum==7) $monthStr=$monthStr."a";
//				else $monthStr=substr($monthStr,0,strlen($monthStr)-1)."я";	
//			}	
			$result=$monthStr;
			break;
		case 3:
			$result=(int)$dayNum;
			break;
		case 4:
			$result=$dayStr;
			break;
		case 5:
			if(!$short || $short==2) {
				if($monthNum==2 || $monthNum==7) $monthStr=$monthStr."a";
				else $monthStr=substr($monthStr,0,strlen($monthStr)-2)."я";	
			}	
			$result=(int)$dayNum." ".$monthStr;
			break;
		case 6:
			$result=$monthStr." ".$year;
			break;
		case 7:
			if(!$short || $short==2) {
				if($monthNum==2 || $monthNum==7) $monthStr=$monthStr."a";
				else $monthStr=substr($monthStr,0,strlen($monthStr)-2)."я";	
			}	
			$result=(int)$dayNum." ".$monthStr." ".$year;
			break;
		case 8:
			if(!$short || $short==2) {
				if($monthNum==2 || $monthNum==7) $monthStr=$monthStr."a";
				else $monthStr=substr($monthStr,0,strlen($monthStr)-2)."я";	
			}	
			$result=(int)$dayNum." ".$monthStr." ".$year.", ".$dayStr;
			break;
		case 9:
			if(!$short || $short==2) {
				if($monthNum==2 || $monthNum==7) $monthStr=$monthStr."a";
				elseif($monthNum==8)  $monthStr=substr($monthStr,0,strlen($monthStr)-2)."я";	
				else $monthStr=substr($monthStr,0,strlen($monthStr)-2)."я";	
			}	
			$result=$monthStr;
			break;
		default:
			$result=$dayNum."–".$monthNum."–".$year;
			break;
	}
//echo "Result: ".$result;
	return $result;
}


//===================================================================================================
// Вычисление дня недели по отпарсенной дате
//===================================================================================================

function getDay($day,$mon,$year) {
        $day = (int)$day; //если день двухсимвольный и <10 
        $mon = (int)$mon; //если месяц двухсимвольный и <10 
        $a = (int)((14 - $mon) / 12);
        $y = $year - $a;
        $m = $mon + 12 * $a - 2;
        $d = (7000 + (int)($day+$y+ (int)($y/4) - (int)($y/100) + (int)($y/400) + (31*$m)/12))%7;
//		$d=date("w",mktime(0,0,0,$mon,$day,$year));
        return $d;
}

//Функция удаляет пунктирную рамку вокруг фокусного item, вставлять в <header></header>
function remove_focus() {
	echo '<script type="text/javascript" src="path-to/jquery.js"></script>
		<script type="text/javascript">
			jQuery(document).ready(function(){
				jQuery("input").focus(
				function(){
				this.blur();
				});
			});
		</script>';
}

#========================================================================
# Проверяет, есть ли дополнительный путь после BASE — например, после http://www.iconed.ru/all_books/book1/ стоит СОДЕРЖАНИЕ
#========================================================================
function is_page() {
	if($t=$_SERVER['QUERY_STRING']) return 1;
	return 0;
}

#========================================================================
# Возвращает дополнительный путь после BASE — например, после http://www.iconed.ru стоит /all_books/book1/СОДЕРЖАНИЕ
#	параметр full значает, что возвращается /all_books/book1/СОДЕРЖАНИЕ
#	если параметр full опущен ($full=0) — возаращается СОДЕРЖАНИЕ
#========================================================================
function get_page($full=0) {
	$t=$_SERVER['QUERY_STRING'];
//	echo "TTT: ".substr($t,strrpos($t,"/")+1).", t: ".$t.", strPos: ".strrpos($t,"/")."<br>";
	if($full) return $t;
	else return substr($t,strrpos($t,"/")+1);
}

// Функция удаляеет последний знак в строке
// $num — количество удаляемых знаков
function rstr($str,$num=1) {
	$t=trim($str);
//	$ret=substr($t,strlen($t)-$num,strlen($t)); // возвращает последний знак в строке
	$ret=substr($t,0,strlen($t)-$num);
	return $ret;
}

#=========================================================================
# Удаляет расширение из имени файла
# Возвращает имя файла или массив из имени файла и расширения
#=========================================================================
function cut_ext($fname, $booArray=false) {
	$fname=trim($fname);
	$arrFName=explode('.',$fname);
	$last=count($arrFName)-1;  // первый элемент — должен быть "ноль", то есть общее число сокращает на 1
	//если count больше 2, то есть существуют точки внутри имени, их надо восстановить
	$retFName=$arrFName[0];
	if($last>1)	$retFName .= ".";
	for($i=1;$i<$last;$i++) {
		$retFName.=$arrFName[$i];
		if($i<($last-1))$retFName.=".";
	}	
	if($booArray) {
		$retArrFName[0]=$retFName;
		$retArrFName[1]=$arrFName[$last];
		return $retArrFName;
	} else return $retFName;
}	

//=========================================
// Функция возвращает текст, в котором вставляет перед и после каждого параграфа определенные тэги
//
// @before — тэг со стилем, открывающим параграф
// @after — закрывающий тэг
// @text — текст, содержащий параграфы
//=========================================
function formParas($text,$before,$after) {
	$iCo=1;
	$return='';
	while($iCo > 0) { 
//		echo "<br>iCo start: $iCo<br>text: $text";
		$tt=strpos($text,"\n");
		if($tt==false) $tt=strpos($text,"<br>"); 
		if($tt==false) $tt=strpos($text,"<br />"); 
		if($tt==false) $tt=strpos($text,"<br/>"); 
		if($tt===false) {
			$return .= $before.$text.$after;
			$iCo=0;
		} else {
//			echo "<br>TMP ($iCo): ".
			$strTMPText = substr($text,0,($tt+1));
//			echo "<br>REST: ".
			$text=str_replace($strTMPText,'',$text);
			$return .= $before.$strTMPText.$after;
			$iCo++;
//			echo "<br>iCo end: $iCo<br>";
		}
	}
	return $return;
}	
	
?>