<?php

require_once __DIR__ . '/common.php';

use Gallery\Core;

$galleryApp = new Core();
$galleryApp->checkUserAuth();
$userData = $galleryApp->userData;

header('Content-Type: application/json');

switch ($_GET['select']) {
    case 'getPage':
        $pageData = $galleryApp->getPage($_GET['pathname'], true);
        echo json_encode(array(
            'module' => $pageData,
            'personal_menu' => ($_GET['pm'] == 'true' ? $galleryApp->getPersonalMenu($pageData['active_tab'], $userData) : '')
        ));
        break;
    case 'signIn':
        if(preg_match("/^[a-zA-Z0-9\_]+$/", $_POST['username']) && $_POST['password']) {
            $password = md5($_POST['password']);
            $res = $galleryApp->galleryDB->query('SELECT * FROM users WHERE username = "'.$_POST['username'].'" AND password = "'. $password.'"');
            if($res->num_rows) {
                $userdata = $res->fetch_assoc();
                $randomString = (bin2hex(random_bytes (16)));
                $expDateTime = time() + 60*60*24*10000;
                setcookie("userid", $userdata['id'], $expDateTime, '/');
                setcookie("session", $randomString, $expDateTime, '/');
                $galleryApp->galleryDB->query( 'INSERT INTO users_sessions SET 
                    user_id = '.$userdata['id'].',
                    session_key = "'. $randomString.'",
                    started_at = NOW()
                ');
                echo json_encode(array('success' => 1));
            }
            else {
                setcookie('userid', false);
                setcookie('session', false);
                echo json_encode(array('error' => 1, 'error_msg' => 'Неверные данные для вход в систему'));
            }
        }
        else echo json_encode(array('error' => 1, 'error_msg' => 'Неверный формат данных для входа в систему'));
        break;
    case 'logout':
        setcookie('userid', false);
        setcookie('session', false);
        echo json_encode(array('success' => 1));
        break;
    case 'checkExistUser':
        $res = $galleryApp->galleryDB->query('SELECT id FROM users WHERE username = "'.$_GET['username'].'"');
        if($res->num_rows) {
            $userdata = $res->fetch_assoc();
            echo json_encode(array('user_exist' => 1, 'user_id' => $userdata['id']));
        }
        else echo json_encode(array('user_exist' => 0));
        break;
    case 'checkExistAlbum':
        $res = $galleryApp->galleryDB->query('SELECT id, name FROM albums WHERE address = "'.$_GET['address'].'"');
        if($res->num_rows) {
            $userdata = $res->fetch_assoc();
            echo json_encode(array('album_exist' => 1, 'album_id' => $userdata['id'], 'album_name' => $userdata['name']));
        }
        else echo json_encode(array('album_exist' => 0));
        break;
    case 'saveDataAlbum':
        if($userData['is_admin'] && $_POST['name'] && $_POST['address']) {
            $album = $_POST['album_id'];
            $res = 0;
            $values = '
                name = "'.$_POST['name'].'",
                address = "'.$_POST['address'].'",
                private = '.$_POST['private'];
            if($album) $res = $galleryApp->galleryDB->query('UPDATE albums SET '.$values.' WHERE id = '.$album);
            else {
                $res = $galleryApp->galleryDB->query('INSERT INTO albums SET create_dt = NOW(), '.$values);
                $album = $galleryApp->galleryDB->insert_id;
            }
            if($res) {
                $galleryApp->galleryDB->query('DELETE FROM albums_users WHERE album_id = '.$album);
                foreach (json_decode($_POST['private_list']) as &$value) {
                    $galleryApp->galleryDB->query('INSERT INTO albums_users SET album_id = '.$album.', user_id = '.$value);
                }
                echo json_encode(array('success' => 1, 'album_id' => $album));
            }
            else echo json_encode(array('error' => 1));
            break;
        }
        else echo json_encode(array('error' => 1));
        break;
    case 'saveDataPhoto':
        if($userData['is_admin']) {
            $photo = $_POST['photo_id'];
            $res = 0;
            $values = 'private = '.$_POST['private'];
            $res = $galleryApp->galleryDB->query('UPDATE photos SET '.$values.' WHERE id = '.$photo);
            if($res) {
                $galleryApp->galleryDB->query('DELETE FROM photos_users WHERE photo_id = '.$photo);
                foreach (json_decode($_POST['private_list']) as &$value) {
                    $galleryApp->galleryDB->query('INSERT INTO photos_users SET photo_id = '.$photo.', user_id = '.$value);
                }

                $galleryApp->galleryDB->query('DELETE FROM albums_photos WHERE photo_id = '.$photo);
                foreach (json_decode($_POST['album_list']) as &$value) {
                    $galleryApp->galleryDB->query('INSERT INTO albums_photos SET photo_id = '.$photo.', album_id = '.$value);
                }

                echo json_encode(array('success' => 1, 'photo_id' => $photo));
            }
            else echo json_encode(array('error' => 1));
            break;
        }
        else echo json_encode(array('error' => 1));
        break;
    case 'uploadPhoto':
        if($userData['is_admin']) {
            if(file_exists($_FILES['fileToUpload']['tmp_name'])) {

                include __DIR__ . '/libraries/php-image-resize-master/ImageResize.php';

                $type = 0;

                list($p_width, $p_height, $p_type, $p_attr) = getimagesize($_FILES['fileToUpload']['tmp_name']);

                if($p_type == 2) $type = 'jpg';
                else if($p_type == 3) $type = 'png';

                $fileID = md5(microtime());
                $path = '/media/p/'.mt_rand(0, 99).'/'.mt_rand(0, 99).'/';
                $fileBig = __DIR__.$path.$fileID.'.'.$type;
                $fileBigWeb = $path.$fileID.'.'.$type;
                $fileSmall = __DIR__.$path.$fileID.'-small.'.$type;
                $fileSmallWeb = $path.$fileID.'-small.'.$type;

                if(!file_exists(__DIR__.$path)) mkdir(__DIR__.$path, 0777, true);


                $preview = new \Gumlet\ImageResize($_FILES['fileToUpload']['tmp_name']);

                if($p_width > 1080) {
                    $preview->resizeToWidth(1920);
                }
                $preview->save($fileBig);
                list($big_width, $big_height) = getimagesize($fileBig);

                $preview = new \Gumlet\ImageResize($_FILES['fileToUpload']['tmp_name']);

                if($p_height > 400) {
                    $preview->resizeToHeight(400);
                    $preview->save($fileSmall);
                }
                else {
                    $filePreviewWeb = $fileBigWeb;
                }
                list($small_width, $small_height) = getimagesize($fileSmall);

                $galleryApp->galleryDB->query('INSERT INTO photos SET
                       added_at = NOW(),
                       image_big = "'.$fileBigWeb.'",
                       image_big_width = '.$big_width.',
                       image_big_height = '.$big_height.',
                       image_small = "'.$fileSmallWeb.'",
                       image_small_width = '.$small_width.',
                       image_small_height = '.$small_height.'
                       ');
                echo json_encode(array('success' => 1, 'photo_id' => $galleryApp->galleryDB->insert_id));
            }
        }
        break;
    default:
        echo json_encode(array('error' => 1));
}