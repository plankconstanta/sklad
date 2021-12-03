<?php
/**
 * счетчик просмотров страниц (сохраняем или в Redis или в Mysql)
 * если значение мешьше порогового - скрываем от поисковиков
 */

sfLoader::loadHelpers('Rediska', 'SeoList');
$url = sfContext::getInstance()->getRequest()->getUri();
$url = SeoListHelper::clearUrl($url);
$list = SeoListHelper::getList();

if ($list && $list->count()) {
    if (!$list->exist($url)) {
        $list->add($url);
    }

    $val = $list->inc($url);

    if ($val < SeoListHelper::LIMIT_VISITS) {
        // black list
         { $this->getResponse()->addMeta('robots', 'none'); }
    } else {
        // white list
    }
}
?>