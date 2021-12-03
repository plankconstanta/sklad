<?php
interface SeoListInterface {
/**
* @param string $url
* @return bool
*/
public function add($url);

/**
* @param string $url
* @return bool
*/
public function remove($url);

/**
* @param string $url
* @return bool
*/
public function exist($url);

/**
* return count elements in list
* @return int
*/
public function count();

/**
* return actual mean after increment
* @param string $url
* @return int
*/
public function inc($url);
}
?>