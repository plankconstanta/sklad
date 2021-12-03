<?php
class SeoListRedis implements SeoListInterface {
    private $store = null;

    const EXPIRETIME = 60 * 60 * 24 * 2;

    public function __construct($r)
    {
        $this->store = $r;
        $this->store->selectDb(6);
    }

    public function add($url) {
        $this->store->setAndExpire($url, 0, self::EXPIRETIME);
        return true;
    }

    public function remove($url) {
        return false;
    }

    public function exist($url) {
        return $this->store->exists($url);
    }

    public function count() {
        return $this->store->getKeysCount();
    }

    public function inc($url) {
        $this->store->increment($url);
        $val = $this->store->get($url);
        return $val;
    }
}

?>