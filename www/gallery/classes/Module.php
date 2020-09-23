<?php


namespace Gallery;


abstract class Module
{
    public static $dataBase;
    public static $userData;

    function __construct($_dataBase, $_userData)
    {
        $this::$dataBase = $_dataBase;
        $this::$userData = $_userData;
    }

    abstract public function getPage($isAJAX = NULL);
}