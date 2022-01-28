<?php
interface SeoListInterface {
/**
* @param string $url
* @return bool
*/
public function set($url); // add

/**
* @param string $url
* @return bool
*/
public function delete($url); // remove

/**
* @param string $url
* @return bool
*/
public function has($url); // exist

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