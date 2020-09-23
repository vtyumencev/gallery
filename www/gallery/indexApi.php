<?php

header('Content-Type: application/json; charset=utf-8');


$pathArray = preg_split('/\//', preg_replace('/^\//', '', $_GET['select']));

require_once __DIR__ . '/common.php';

use Gallery\Core;

switch($pathArray[0]) {
    case 'GetPhotos':
        $galleryApp = new Core();

        $photo_id = $_GET['id'] ?? NULL;
        if($photo_id) {
            $offset = 0;
            $limit = 1;
        }
        else {
            $offset = $_GET['offset'] ?? 0;
            $limit = $_GET['limit'] ?? 20;
        }

        $res = $galleryApp->galleryDB->query('
            SELECT *
            FROM photos
            WHERE deleted = 0
              AND private = 0
                '.($photo_id ? 'AND id = '.$photo_id : '').'
            ORDER BY added_at
            LIMIT '.$limit.'
            OFFSET '.$offset);

        $photos = array();
        $count = 0;
        while ($data = $res->fetch_assoc()) {
            $count++;
            $photos[] = array(
                'id' => $data['id'],
                'added_at' => $data['added_at'],
                'image_big' => array(
                    'url' => SITE_DOMAIN.$data['image_big'],
                    'width' => $data['image_big_width'],
                    'height' => $data['image_big_height']
                ),
                'image_small' => array(
                    'url' => SITE_DOMAIN.$data['image_small'],
                    'width' => $data['image_small_width'],
                    'height' => $data['image_small_height']
                )
            );
        }
        echo json_encode(
            array('response' =>
                array(
                    'count' => $count,
                    'items' => $photos
                )
            ));
        break;
    case 'GetAlbums':
        $galleryApp = new Core();

        $album_id = $_GET['id'] ?? NULL;
        if($album_id) {
            $offset = 0;
            $limit = 1;
        }
        else {
            $offset = $_GET['offset'] ?? 0;
            $limit = $_GET['limit'] ?? 20;
        }

        $res = $galleryApp->galleryDB->query('
            SELECT *
            FROM albums
            WHERE deleted = 0
              AND private = 0
                '.($album_id ? 'AND id = '.$album_id : '').'
            ORDER BY create_dt
            LIMIT '.$limit.'
            OFFSET '.$offset);

        $albums = array();
        $count = 0;
        while ($data = $res->fetch_assoc()) {
            $count++;
            $albums[] = array(
                'id' => $data['id'],
                'create_dt' => $data['create_dt'],
                'name' => $data['name'],
                'address' => $data['address']
            );
        }

        echo json_encode(
            array('response' =>
                array(
                    'count' => $count,
                    'items' => $albums
                )
            ));
        break;
    default:
        echo json_encode(array('response' => array('error' => 1, 'error_msg' => '')));
}
