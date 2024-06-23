<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

$dir = '.';
$icon_json = file_get_contents($dir . '/icons.json');
$file_icon_array = json_decode($icon_json, true);


$array = array();


foreach($file_icon_array as $index => $item)
{
  $svg = $item['svg'];
  foreach($svg as $type => $data)
  {
    unset($svg[$type]['raw']);
    unset($svg[$type]['last_modified']);
  }

  $array[$index] = array(
      'styles' => $item['styles'],
      'label' => $item['label'],
      'svg' => $svg,

  );

}

$file = fopen('./icons_processed.json', 'w+');
fwrite($file, json_encode($array) );
fclose($file);

echo "<PRE>"; print_r($array); echo "</PRE>";



echo "DONE";


?>



<?php

function convert($size)
{
    $unit=array('b','kb','mb','gb','tb','pb');
    return @round($size/pow(1024,($i=floor(log($size,1024)))),2).' '.$unit[$i];
}


/* function tick_handler() {
    $mem = memory_get_usage();
    $bt = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2)[0];
    fwrite($file, $bt["file"].":".$bt["line"]."\t".$mem."\n");
}
register_tick_function('tick_handler');
*/
?>
