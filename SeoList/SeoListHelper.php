<?php
class SeoListHelper {
    const LIMIT_VISITS = 100;

    /**
     * create list
     * @return SeoListDb|SeoListRedis
     */
    public static function getList() {
        if ($r = get_rediska_instance()) {
            $list = new SeoListRedis($r);
        } else {
            $list = new SeoListDb();
        }
        return $list;
    }

    /**
     * @param string $url
     * @return string
     */
    public static function clearUrl($url) {
        $url = trim(str_replace(array('https://', 'http://', 'www.'), '', $url));
        $url = explode("?", $url)[0];
        return $url;
    }
}
?>