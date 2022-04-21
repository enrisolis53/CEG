<?php
//******************************************************************************************************
//-- String normalization
function strsave($str) {
    $invalid = array("'" => "`", "\"" => "``", "''" => "``", "?" => "`", "â€™" => "`", "$" => "S");
    $str = str_replace(array_keys($invalid), array_values($invalid), $str);
	$str = utf8_decode($str);
    return $str;
}
function strhtml($str) {
	$str = mb_convert_encoding($str, "UTF-8");
    return $str;
}
//-- String normalization
//******************************************************************************************************


//******************************************************************************************************
//-- Numeric to string
function number2text($convertme) {
	$numtoconv = round(floatval($convertme),2);
	
	if ($numtoconv<=9999999999.99) {
		$numberstring = ""; //-- this will handle converted number
		$prevnumber = 0; //-- will be used to determine if teen or ten
		//-- Shall i state the obvious?
		$ones = array("","one","two","three","four","five","six","seven","eight","nine");
		$teens = array("","eleven","twelve","thirteen","fourteen","fifteen","sixteen","seventeen","eighteen","nineteen");
		$tens = array("","ten","twenty","thirty","forty","fifty","sixty","seventy","eighty","ninety");
		$word = array("","");
		//-- Numeric breakdown
		$decimal = round($numtoconv-floor($numtoconv),2);
		$strdecimal = strval($decimal);
		$strinteger = strval(floor($numtoconv));
		$lenme = strlen($strinteger);
		//-- Loop through all the numbers in the amount		
		for ($i=0; $i<$lenme; $i++) {
			$char = intval(substr($strinteger,$i,1));
			$pos = $lenme - $i;
			$text = " ";
			
			if ($pos==10) { 
				if ($char>0) { 
					$numberstring .= $ones[$char]." billion"; 
				} 
			}
			if ($pos==3 || $pos==6 || $pos==9) { 
				if ($char>0) { 
					$numberstring .= " ".$ones[$char]." hundred"; 
					if ($pos==6 && $char>0) { $word[0] = $word[0].$char; }  //-- Thousands
					if ($pos==9 && $char>0) { $word[1] = $word[1].$char; }	//-- Millions
				} 
			}
			if ($pos==5 || $pos==8) { 
				$prevnumber = $char; 
				if ($char>0) {
					if ($pos==5 && $char>0) { $word[0] = $word[0].$char; }  //-- Thousands
					if ($pos==8 && $char>0) { $word[1] = $word[1].$char; }	//-- Millions
				}
			}
			if ($pos==1 || $pos==4 || $pos==7) {
				if ($char==0 && $prevnumber==1) {
					$numberstring .= " ".$tens[$prevnumber]; 					
				}
				else {
					if ($prevnumber==1 && $char>0) {
						$numberstring .= " ".$teens[$char]; 
					}
					if ($prevnumber>1 && $char>0) { 
						$numberstring .= " ".$tens[$prevnumber]." ".$ones[$char]; 
					}
					if ($prevnumber==0 && $char>0) { 
						$numberstring .= " ".$ones[$char]; 
					}
					if ($prevnumber>1 && $char==0) {
						$numberstring .= " ".$tens[$prevnumber]; 
					}
				}
				if ($pos==4) {
					if ($word[0] || $char>0) {
						$numberstring .= " thousand"; 
					}
				}
				if ($pos==7) {
					if ($word[1] || $char>0) {
						$numberstring .= " million"; 
					}
				}
				$prevnumber = 0;
			}
			if ($pos==2) { 
				$prevnumber = $char; 
			}
		}
		$numberstring = trim(str_replace("  "," ",$numberstring));
		$numstr = strtoupper(substr($numberstring,0,1)).substr($numberstring,1)." pesos";
		if ($decimal>0) { $numstr .= " and ".($decimal*100)."/100"; }
		
		return $numstr;
	}
	else { 
		return "Number is too large to convert!"; 
	}
}
//-- Numeric to string
//******************************************************************************************************

//-- Proper names
function ucname($string) {
    $string =ucwords(strtolower($string));

    foreach (array('-', '\'') as $delimiter) {
      if (strpos($string, $delimiter)!==false) {
        $string =implode($delimiter, array_map('ucfirst', explode($delimiter, $string)));
      }
    }
    return $string;
}

//-- String to Hex and vice versa
function String2Hex($string){
    $hex='';
    for ($i=0; $i < strlen($string); $i++){
        $hex .= dechex(ord($string[$i]));
    }
    return $hex;
}
function Hex2String($hex){
    $string='';
    for ($i=0; $i < strlen($hex)-1; $i+=2){
        $string .= chr(hexdec($hex[$i].$hex[$i+1]));
    }
    return $string;
}

//-- Sort multi-dimensional array
/* -- USAGE
$list = array(
	array( 'type' => 'suite', 'name'=>'A-Name'),
	array( 'type' => 'suite', 'name'=>'C-Name'),
	array( 'type' => 'suite', 'name'=>'B-Name')
);
$list = array_sort($list, 'name', SORT_ASC);
*/
function array_sort($array, $on, $order=SORT_ASC){
    $new_array = array();
    $sortable_array = array();

    if (count($array) > 0) {
        foreach ($array as $k => $v) {
            if (is_array($v)) {
                foreach ($v as $k2 => $v2) {
                    if ($k2 == $on) {
                        $sortable_array[$k] = $v2;
                    }
                }
            } else {
                $sortable_array[$k] = $v;
            }
        }

        switch ($order) {
            case SORT_ASC:
                asort($sortable_array);
                break;
            case SORT_DESC:
                arsort($sortable_array);
                break;
        }

        foreach ($sortable_array as $k => $v) {
            $new_array[$k] = $array[$k];
        }
    }
    return $new_array;
}

//////////////////////////////////////////////////////////////////////
//PARA: Date Should In YYYY-MM-DD Format
//RESULT FORMAT:
// '%y Year %m Month %d Day %h Hours %i Minute %s Seconds'        =>  1 Year 3 Month 14 Day 11 Hours 49 Minute 36 Seconds
// '%y Year %m Month %d Day'                                    =>  1 Year 3 Month 14 Days
// '%m Month %d Day'                                            =>  3 Month 14 Day
// '%d Day %h Hours'                                            =>  14 Day 11 Hours
// '%d Day'                                                        =>  14 Days
// '%h Hours %i Minute %s Seconds'                                =>  11 Hours 49 Minute 36 Seconds
// '%i Minute %s Seconds'                                        =>  49 Minute 36 Seconds
// '%h Hours                                                    =>  11 Hours
// '%a Days                                                        =>  468 Days
//////////////////////////////////////////////////////////////////////
function ddiff($date_1 , $date_2 , $differenceFormat = '%a' ) {
    $datetime1 = date_create($date_1);
    $datetime2 = date_create($date_2);
    $interval = date_diff($datetime1, $datetime2);
    return $interval->format($differenceFormat);
}

//-- This function allows the addition of day(s),month(s),year(s) to the original date while still preserving the Hours, minutes and seconds
//-- You can also modify to add to hours, miuntes and even seconds.
function add_date($givendate,$day=0,$mth=0,$yr=0) {
    $cd = strtotime($givendate);
    $newdate = date('Y-m-d h:i:s', mktime(date('h',$cd),
    date('i',$cd), date('s',$cd), date('m',$cd)+$mth,
    date('d',$cd)+$day, date('Y',$cd)+$yr));
    return $newdate;
}
?>