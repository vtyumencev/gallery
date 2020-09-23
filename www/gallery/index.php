<?php

require_once __DIR__ . '/common.php';

use Gallery\Core;

$galleryApp = new Core();
$galleryApp->checkUserAuth();
$pageData = $galleryApp->getPage($_GET['select'], false);
$personalMenu = $galleryApp->getPersonalMenu($pageData['active_tab'], $galleryApp->userData);

if($pageData['page_not_found']) {
    header("HTTP/1.0 404 Not Found");
}

$flagMobile = '';
if(preg_match('/mobile/i', $_SERVER['HTTP_USER_AGENT'])) {
    $flagMobile = ' ua-mobile';
}

?>

<html lang="ru" class="<?=($_GET['view'] ? 'vw-show' : '').$flagMobile?>">
    <head>
        <link href="https://fonts.googleapis.com/css2?family=Source+Sans+Pro&display=swap" rel="stylesheet">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="shortcut icon" href="/static/icons/gallery.svg">
        <link rel="stylesheet" href="/static/common.css">
        <link rel="stylesheet" href="/static/admin.css"/>
        <script src="/static/jquery-3.5.1.min.js"></script>
        <script src="/static/scripts.js"></script>
        <script src="/static/admin.js"></script>
        <title><?=$pageData['title']?></title>
    </head>
    <body>
        <div class="page-wrapper">
            <div class="w-header">
                <div class="header">
                    <div class="h-menu h-main-menu">
                        <a href="/photos" tab-name="photos"<?=($pageData['active_tab'] == 'photos' ? ' active-tab="true"' : '')?>>Все фотографии</a>
                        <a href="/albums" tab-name="albums"<?=($pageData['active_tab'] == 'albums' ? ' active-tab="true"' : '')?>>Альбомы</a>
                    </div>
                    <div class="h-menu h-personal-menu">
                        <?=$personalMenu?>
                    </div>
                </div>
            </div>
            <div class="page-content">
                <?=$pageData['content']?>
            </div>
        </div>
        <div class="view-window">
            <img class="vw-image">
            <img class="vw-image-phantom">
            <div class="vw-elements">
                <div class="vw-close"><div class="vw-c-icon"></div></div>
                <div class="vw-prev vw-nav"><div class="vw-nav-icon"></div></div>
                <div class="vw-next vw-nav""><div class="vw-nav-icon"></div></div>
            </div>
        </div>
    </body>
</html>
