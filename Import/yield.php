<?php
$cnt = 0;
echo __LINE__."_______".PHP_EOL;
check_point_memory_usage();
foreach(getXmlElementFromFile('avito-final.xml', 'Ad') as $item) {
    // do smth with data
    $cnt++;
    if ($cnt == 10) {
        echo __LINE__."_______".PHP_EOL;
        check_point_memory_usage();
    }
}

echo __LINE__."_______".PHP_EOL;
check_point_memory_usage();


function getXmlElementFromFile(string $filename, string $nodename) {
    $z = new XMLReader;
    $z->open($filename);

    while ($z->read() && $z->name !== $nodename);

    while ($z->name === $nodename)
    {
        $node = new SimpleXMLElement($z->readOuterXML());
        yield $node;
        $z->next($nodename);
    }
}

function check_point_memory_usage() {
    print "memory_get_usage() =" . memory_get_usage()/1024 . "kb\n";
    print "memory_get_usage(true) =" . memory_get_usage(true)/1024 . "kb\n";
    print "memory_get_peak_usage() =" . memory_get_peak_usage()/1024 . "kb\n";
    print "memory_get_peak_usage(true) =" . memory_get_peak_usage(true)/1024 . "kb\n";
    //print "custom memory_get_process_usage() =" . memory_get_process_usage() . "kb\n";
    print "custom memory usage() =" . trim(vsz()) . "kb\n";
    print "custom memory usage(true) =" . trim(rss()) . "kb\n";
}



/**
 * Returns memory usage from /proc<PID>/status in bytes.
 *
 * @return int|bool sum of VmRSS and VmSwap in bytes. On error returns false.
 */
function memory_get_process_usage()
{
    $status = shell_exec('cat /proc/' . getmypid() . '/status');

    $matchArr = array();
    preg_match_all('~^(VmRSS|VmSwap):\s*([0-9]+).*$~im', $status, $matchArr);

    if(!isset($matchArr[2][0]) || !isset($matchArr[2][1]))
    {
        return false;
    }

    return intval($matchArr[2][0]) + intval($matchArr[2][1]);
}

// сколько было выделено памяти на весь процесс с излишком в kb
function vsz() {
    return shell_exec(sprintf('ps -o vsz= -p %s', getmypid()));
}
// сколько памяти процесс занял фактически в kb
function rss() {
    return shell_exec(sprintf('ps -o rss= -p %s', getmypid()));
}
