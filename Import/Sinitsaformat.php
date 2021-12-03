<?php

class ExceptionImportObjects extends Exception
{
    public function __construct($message)
    {
        $this->message = $message;
    }
}


class Sinitsaformat {
    const PRIORITI_LIST_ID = 580;

    const STATUS_APP_ID = '000000002'; // Свободна

    const MSK_ID = '2';
    const NEWFLATS_ID = '000000001';
    const APART_ID = '000000009';

    const USER_ID = 646491;
    const DEFAULT_CONTACTS = '74996486418';

    protected $xml_complex = '';
    protected $xml_object = '';
    protected $xml_building = '';
    protected $xml_user = '';

    protected $dictionaries = null;

    protected $prioritylist = null;
    protected $fileimport = null;
    protected $object = null;

    public $objectDictionary = null;

    protected static $array_YES = ['true', 1 , '1', 'True', 'TRUE', true];
    protected static $array_NO = ['false', 0 , '0', 'False', 'FALSE', false];


    public function __construct()
    {
        $this->object = null;
    }


    public function setObjectDictionary(ObjectDictionary $dict) {
        $this->objectDictionary = $dict;
    }
    public function setXmlObject($xml_object) {
        $this->xml_object = $xml_object;
    }

    public function setPriorityList(PriorityList $item) {
        $this->prioritylist = $item;
    }

    public function setFileimport(FileImport $item) {
        $this->fileimport = $item;
    }

    public function getObjectRostkuid() {
        $rostk = '';
        if (!empty($this->fileimport)) {
            $rostk = 'fileimport-'.$this->fileimport->getId();
        } elseif (!empty($this->prioritylist)) {
            $rostk = $this->prioritylist->getUid();
        }
        if (empty($rostk)) {
            return '';
        }
        $uid = $this->xml_object['Код'];
        return trim($rostk . ' ' . $uid);
    }

    public function getUser($xml_user) {
        return UserPeer::retrieveByPK(self::USER_ID);
    }

    public function saveObject($xml_object, $xml_complex, $xml_building, $xml_user) {
        $this->xml_complex = $xml_complex;
        $this->xml_object = $xml_object;
        $this->xml_building = $xml_building;
        $this->xml_user = $xml_user;
        $saving_attach = false;
        $rostkuid = $this->getObjectRostkuid();

        if (empty($rostkuid)) {
            throw new ExceptionImportObjects('Не определен внешний идентификатор объекта');
        }

        $c = new Criteria();
        $c->addAnd(ObjectsPeer::ROSTKUID, $rostkuid);
        $this->object = ObjectsPeer::doSelectOne($c);
        if (empty($this->object)) {
            $saving_attach = true;
            $this->object = new Objects();
            $this->object->setRostkuid($rostkuid);
        }


        // здесь дублирование - проверка вынесена выше в import.php
        if ((string)$this->xml_object['statusappid'] !== self::STATUS_APP_ID) { // СТАТУС Свободна
            if (empty($this->object->getId())) {
                throw new ExceptionImportObjects('Статус объекта не позволяет его сохранить');
            } else {
                $this->hideObject();
                return $this->object;
            }
        }

        $this->object->setSaveTo(time() + 60 * 60 * 24 * $this->prioritylist->getSaveday());
        $this->object->setVisibility(true); // по умолчанию для новых объектов null, + если изменился статус у ранее выгржаемого объекта

        if ($this->object->getId()) {
            $this->clearCommonFields();
            $this->deleteSubObject();
        }

        $user = $this->getUser($xml_user);
        if ($user instanceof User) {
            $this->object->setUserId($user->getId());
            if ($profile = UserProfilePeer::retrieveByUid($user->getId())) {
                $this->object->setContacts(trim($profile->getDefaultContacts()));
            }
            if (empty($this->object->getContacts())) {
                // наши телефоны по умолчанию
                $this->object->setContacts(self::DEFAULT_CONTACTS);
            }
        } else {
            throw new ExceptionImportObjects('Не удалось определить пользователя объекта');
        }


        $this->fillCommonFields();


        $save_result = $this->object->save();
        if (empty($this->object->getId())) {
            return $this->object;
        }

        // !!! важно несколько раз сохранить для проверок в doSave
        $save_result = $this->object->save();

        if ($this->object->getObjecttypeId() == ObjectTypesPeer::NEWFLATS_ID) {
            $subobj = $this->fillNewflatsFields();
            if (is_object($subobj)) {
                $subobj->setObjectsId($this->object->getId());
                $subobj->save();
            } else {
                $this->hideObject();
                throw new ExceptionImportObjects('Не удалось сохранить доп характеристики для объекта');
            }
        }

        if ($subobj->getRoomsall() == 1 && $this->object->getSall() > 100) {
            $this->hideObject();
            throw new ExceptionImportObjects('Объект с одной комнатой и площадью более 100 кв.м');
        }

        // сохраняем подтип даже если есть ошибки при сохранении объекта
        if ($save_result === false) {
            return $this->object;
        }


        // дублирование проверки
        $atts_xml = $this->xml_object->Files->File;
        if (!count($atts_xml)) {
            $this->hideObject();
            throw new ExceptionImportObjects('Отсутствуют картинки');
        }

        if ($saving_attach || empty($this->object->getAttachmentId1())) {
            $this->saveAttachments();
        }

        // for save Attachment ids
        $this->object->save();

        return $this->object;
    }

    protected function hideObject() {
        $this->object->save_to_no_check = time() - 5*60; // чтобы автоколя не продлил
        $this->object->setVisibility(false);
        $this->object->save();
    }

    protected function saveAttachments() {
        $atts_xml = $this->xml_object->Files->File;
        foreach($atts_xml as $att_xml) {
            $filepaths[] = (string)$att_xml['файлнасервере'];
        }
        $i=1;
        foreach($filepaths as $url) {
            $issave = false;
            if ($url) {
                try {
                    $findhash = AttachmentPeer::getHash(trim($url));
                    if ($findhash && !$findhash->getAttachmentId()) {
                        $issave = true; $checkAtt = null;
                    } elseif ($findhash && $findhash->getAttachmentId()) {
                        $checkAtt = AttachmentPeer::retrieveByPK($findhash->getAttachmentId());
                        if (!empty($checkAtt)) {
                            if (in_array($i, range(1,10))) {
                                $method = 'getAttachmentId'.$i;
                                if ($this->object->$method() != $findhash->getAttachmentId()) {
                                    $method = 'setAttachmentId'.$i;
                                    $this->object->$method($findhash->getAttachmentId());
                                }
                            }
                        } else {
                            $issave = true;
                        }
                    }
                    if ($issave && $findhash) {
                        $att = new Attachment();
                        $att->initFromURL(trim($url));
                        if ($att->getId()) {
                            if (in_array($i, range(1,10))) {
                                $method = 'setAttachmentId'.$i;
                                $this->object->$method($att->getId());
                                $att->addAnnotationFromImage();
                                if ($findhash->getHash()) {
                                    $findhash->setAttachmentId($att->getId());
                                    $findhash->save();
                                }
                            }
                            /*if ($url == $plan) {
                                $item->setPlan($att->getId());
                            }*/
                        }
                    }
                    $i++;
                } catch (Exception $e) {
                    mail('ahmed@pdg.ru', 'saving sin img', $e->getMessage());
                }
            }
        }
    }

    protected function clearCommonFields() {
        $this->object->setRegionId(null);
        $this->object->setMetroId(null);
        $this->object->setOkrugId(null);
        $this->object->setAreaId(null);
        $this->object->setAddressStreet1(null);
        $this->object->setHouse(null);
        $this->object->setKorpus(null);
        $this->object->setLatitude(null);
        $this->object->setLongitude(null);
        $this->object->setDescription(null);
        $this->object->setContacts(null);
        $this->object->setVideo(null);
        $this->object->setUserId(null);
        $this->object->setMcompanyId(null);
        $this->object->setSall(null);
        $this->object->setPrice(null);
        $this->object->setPriceTypeN(1);
        $this->object->setAgComm(null);
        $this->object->setClComm(null);
        $this->object->setZalog(null);
        $this->object->setConObj(null);
        $this->object->setConObjString(null);
        $this->object->setIsipoteka(null);
    }

    public function deleteSubObject() {
        if (empty($this->object->getId())) {
            return false;
        }

        foreach(array_unique(ObjectTypesPeer::$OBJECT_TYPES) as $objtype) {
            $peer = $objtype.'Peer';
            $c = new Criteria();
            $c->add($peer::OBJECTS_ID, $this->object->getId());
            $peer::doDelete($c);
            unset($c);
        }
    }

    protected function fillCommonFields() {

        $regionid = $this->getRegionId();

        if (empty($regionid)) {
            throw new ExceptionImportObjects('Не удалось определить регион объекта');
        }
        if (!in_array($regionid, [RegionPeer::MSK_ID, RegionPeer::MO_ID])) {
            throw new ExceptionImportObjects('Регион объекта не Москва или Московская область');
        }
        $this->object->setRegionId($regionid);

        $this->object->setDealId(DealtypesPeer::SALE);


        $objecttypeid = $this->getObjecttypeId();
        if (empty($objecttypeid)) {
            throw new ExceptionImportObjects('Не удалось определить тип объекта');
        }
        $this->object->setObjecttypeId($objecttypeid);

        $sall = (float)$this->xml_object['stotal'];
        if (empty($sall)) {
            throw new ExceptionImportObjects('Не указана площадь объекта');
        }
        $this->object->setSall($sall);

        $price = (int)$this->xml_object['flatcostbase'];
        if (empty($price)) {
            $price = (int)$this->xml_object['flatcostwithdiscounts'];
        }

        if (empty($price)) {
            throw new ExceptionImportObjects('Не указана стоимость объекта');
        }
        $this->object->setPrice($price);
        $this->object->setCurrencyId(1);

        $this->fillAddressFields();

        // mortgage
        $value = (string)$this->xml_building['mortgage'];
        if (in_array($value, self::$array_NO, true)) {
            $this->object->setIsipoteka(false);
        } elseif (in_array($value, self::$array_YES, true)) {
            $this->object->setIsipoteka(true);
        }

        // привязка к комплексу
        if ($value = (string)$this->xml_complex['name']) {
            $this->object->setConObjString($value);
        }

        $this->object->setDescription(strip_tags((string)$this->xml_complex['noteformat']));
    }

    protected function fillAddressFields() {
        $lat = floatval(str_replace(',', '.', ((string)$this->xml_complex['Latitude'])));
        $long = floatval(str_replace(',', '.', ((string)$this->xml_complex['Longitude'])));

        if ($lat && $long) {
            $this->object->setLatitude($lat);
            $this->object->setLongitude($long);
        }

        // район
        $area_name = (string)$this->xml_complex['addressblockname'];
        if (trim($area_name)) {
            $area_o = AreaPeer::getAreaByNameAndRegion($area_name, $this->object->getRegionId());
            if ($area_o) {
                $this->object->setAreaId($area_o->getId());
            } elseif ($log = $this->objectDictionary->getLog()) {
                $log->save(ObjectDictionary::AREA, $area_name);
            }
        }

        if (empty($this->object->getAreaId()) &&  $this->object->getLatitude() && $this->object->getLongitude()) {
            //$this->object->setIsYandexTest(3);
            $districtValidator = new koschaPolygonRestate(new koschaPolygon());
            $areaID = $districtValidator->getAreaIdByPoint($this->object->getLatitude(), $this->object->getLongitude(), $this->object->getRegionId());
            if($areaID) {
                $this->object->setAreaId($areaID);
            }
        }

        // метро
        $metro_name = (string)$this->xml_complex->subways->subway['subwayname'];
        if (trim($metro_name)) {
            $metro_o = MetroPeer::getMetroByNameAndRegion($metro_name, $this->object->getRegionId());
            if ($metro_o) {
                $this->object->setMetroId($metro_o->getId());
            } elseif ($log = $this->objectDictionary->getLog()) {
                $log->save(ObjectDictionary::METRO, $metro_name);
            }
        }

        // адрес
        if ($value = $this->xml_complex['address']) {
            $value = (trim(str_replace(array('Россия', 'г. Санкт Петербург,', 'г Санкт Петербург,', 'г. Санкт-Петербург,', 'г Санкт-Петербург,', 'г Москва,', 'г. Москва', 'Санкт-Петербург,', 'Москва,', 'Ленинградская обл., ', 'Санкт Петербург,'), '', $value)));
            $value_parts = explode(',', $value);
            $address_parts = [];
            foreach($value_parts as $item) {
                if (!empty(trim($item))) {
                    $address_parts[] = trim($item);
                }
            }
            $this->object->setAddressStreet1(implode(', ', $address_parts));
        }
    }

    protected function getObjecttypeId() {
        $objtype_name = $this->xml_object['spacetypename'];
        if ($objtype_name == 'Апартаменты' || $objtype_name=='Квартира') {
            return ObjectTypesPeer::NEWFLATS_ID;
        } else {
            // TODO обраб др типы
        }
    }

    protected function getRegionId() {
        $region_name = (string)$this->xml_complex['addressblockname'];

        // TODO обраб др регионы и добавляем в DictionaryLog если не нашли
        if (!empty($region_name) && $region_name != 'Москва и МО') {
            return null;
        }

        // пытаемся определить Москва или Московская область по адресу
        $address = (string)$this->xml_complex['address'];
        $region_names = ['Москва', 'москва', 'МОСКВА'];
        foreach($region_names as $region_name) {
            if (mb_strpos($address, $region_name) !== false) {
                return RegionPeer::MSK_ID;
            }
        }
        $region_names = ['МОСКОВСКАЯ ОБЛАСТЬ', 'Московская область', 'Московская обл'];
        foreach($region_names as $region_name) {
            if (mb_strpos($address, $region_name) !== false) {
                return RegionPeer::MO_ID;
            }
        }

        // пытаемся определить Москва или Московская область по координатам
        $lat = floatval(str_replace(',', '.', ((string)$this->xml_complex['Latitude'])));
        $long = floatval(str_replace(',', '.', ((string)$this->xml_complex['Longitude'])));
        if ($lat && $long) {
            $districtValidator = new koschaPolygonRestate(new koschaPolygon());
            $areaID = $districtValidator->getAreaIdByPoint($lat, $long, RegionPeer::MSK_ID);
            if($areaID) {
                return RegionPeer::MSK_ID;
            }
            $areaID = $districtValidator->getAreaIdByPoint($lat, $long, RegionPeer::MO_ID);
            if($areaID) {
                return RegionPeer::MO_ID;
            }
            unset($districtValidator);
        }


        return $region_name == 'Москва и МО' ? RegionPeer::MSK_ID : null;
    }

    protected function fillNewflatsFields() {
        $subobject = new Newflats();

        // roomcount
        if ($roomscnt = (int)$this->xml_object['roomcount']) {
            $subobject->setRoomsall($roomscnt);
            $subobject->setRoomssale($roomscnt);
        } else {
            $subobject->setIsstudia(1);
            $subobject->setRoomsall(1);
            $subobject->setRoomssale(1);
        }

        // видизокна
        $value = $this->xml_object['видизокна'];
        if ($id = $this->objectDictionary->getIdByName(ObjectDictionary::WINDOWVIEW, $value)) {
            $subobject->setWindowviewId($id);
        }

        // floor
        if ($value = $this->xml_object['floor']) {
            $subobject->setEt($value);
        }

        // floors
        if ($value = $this->xml_object['floors']) {
            $subobject->setEtall($value);
        }

        // sliving – жилая площадь
        if ($value = $this->xml_object['sliving']) {
            $subobject->setS1($value);
        }

        // sroom – площадь комнат (str);
        if ($value = $this->xml_object['sroom']) {
            $subobject->setS2($value);
        }

        // skitchen – площадь кухни
        if ($value = $this->xml_object['skitchen']) {
            $subobject->setS3($value);
        }

        // scorridor – площадь коридора (float);
        if ($value = $this->xml_object['scorridor']) {
            $subobject->setS5($value);
        }

        // swatercloset - площадь санузла
        if ($value = $this->xml_object['swatercloset']) {
            $subobject->setS6($value);
        }

        // height
        if ($value = $this->xml_object['height']) {
            $subobject->setPotolok($value);
        }

        // buildingtypename
        $value = $this->xml_building['buildingtypename'];
        if ($id = $this->objectDictionary->getIdByName(ObjectDictionary::HOUSETYPE, $value)) {
            $subobject->setHousetypeId($id);
        }

        // мусоропровод
        $value = (string)$this->xml_building['мусоропровод'];
        if (in_array($value, self::$array_NO, true)) {
            $subobject->setIsrubbish(false);
        } elseif (in_array($value, self::$array_YES, true)) {
            $subobject->setIsrubbish(true);
        }

        // типотделки
        $value = (string)$this->xml_object['decorationname'];
        if ($id = $this->objectDictionary->getIdByName(ObjectDictionary::RENOVATION, $value)) {
            $subobject->setRenovationId($id);
        }

        $objtype_name = (string)$this->xml_object['spacetypename'];
        if ($objtype_name == 'Апартаменты') {
            $subobject->setIsapart(true);
        }

        $value = (string)$this->xml_complex->subways->subway['дометрокак'];
        if ($id = $this->objectDictionary->getIdByName(ObjectDictionary::TRANSPORT, $value)) {
            $subobject->setTransportId($id);
        }
        if ($subobject->getTransportId()) {
            $metro_rule = (string)$this->xml_complex->subways->subway['дометроедизм'];
            $metro_dist = (string)$this->xml_complex->subways->subway['расстояние'];
            if ($metro_rule == 'Минут' && $metro_dist) {
                $subobject->setFromMetro($metro_dist);
                $subobject->setMetro((string)$this->xml_complex->subways->subway['subwayname']);
            }
        }

        return $subobject;
    }

}
