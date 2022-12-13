<?php

$array = [
    100500,
    42,
    100400,
    41,
    42,
    100500
];

function test(array $a, $M) {
    $keys = [];
    $a = array_unique($a);
    if (count($a) < $M) {
        return;
    }

    for ($i=0;$i<$M;$i++) {
        $key = mt_rand(0, (count($a)-1));
        while (isset($keys[$key])) {
            $key = mt_rand(0, (count($a)-1));
        }
        yield $a[$key];
    }
}

foreach (test($array, 3) as $item) {
    echo $item . PHP_EOL;
}

// тоже самое но короче
/*var_dump((fn(int $m = 1) => array_map(
    fn(mixed $key) => $array[$key],
                is_array($keys = array_rand($array = array_unique($array), ($cnt = count($array)) >= $m ? $m : $cnt)) ?
                    $keys : [$keys]
)
)(1));*/
