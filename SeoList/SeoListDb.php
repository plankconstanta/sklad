<?php
class SeoListDb implements SeoListInterface {

    /**
     * @param string $url
     * @return string
     */
    public static function hash($url) {
        $hash = md5($url);
        return $hash;
    }

    public function __construct()
    {

    }

    public function add($url) {
        $hash = self::hash($url);
        $item = new SeoList();
        $item->setUrl($url);
        $item->setHash($hash);
        $item->save();
        return true;
    }

    public function remove($url) {
        return false;
    }

    public function exist($url) {
        $item = $this->getByUrl($url);
        return $item ? true : false;
    }

    private function getByUrl($url) {
        $hash = self::hash($url);
        $c = new Criteria();
        $c->add(SeoListPeer::HASH, $hash);
        $c->add(SeoListPeer::URL, $url);
        $item = SeoListPeer::doSelectOne($c);
        return $item;
    }

    public function count() {
        $c = new Criteria();
        $cnt = SeoListPeer::doCount($c);
        return $cnt;
    }

    public function inc($url) {
        $item = $this->getByUrl($url);
        if (empty($item)) {
            return null;
        }

        $sql = 'UPDATE `seo_list` set `cnt` = `cnt` + 1 WHERE `id` = ' . $item->getId();
        $con = Propel::getConnection();
        $con->executeQuery($sql);

        $item = SeoListPeer::retrieveByPk($item->getId());
        return $item ? $item->getCnt() : 0;
    }
}
?>