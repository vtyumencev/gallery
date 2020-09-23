<?php


namespace Gallery;
use mysqli;

class Core
{
    public $galleryDB;
    public $userData;

    public function __construct() {
        $this->galleryDB = new mysqli(
            'mysql',
            'root',
            'root',
            'gallery');
    }

    public function checkUserAuth() {
        if(preg_match("/^[0-9]+$/", $_COOKIE['userid'])
            && preg_match("/^[a-zA-Z0-9]+$/", $_COOKIE['session'])) {
            $res = $this->galleryDB->query('SELECT user_id FROM users_sessions WHERE user_id = '.$_COOKIE['userid'].' AND session_key = "'.$_COOKIE['session'].'"');
            if($res->num_rows) {
                $session_data = $res->fetch_assoc();
                $res = $this->galleryDB->query('SELECT id, username, is_admin FROM users WHERE id = '.$session_data['user_id']);
                $this->userData = $res->fetch_assoc();
            }
            else {
                setcookie('userid', false);
                setcookie('session', false);
            }
        }
    }

    public function getPage($pathname, $isAJAX) {

        $pathArray = preg_split('/\//', preg_replace('/^\//', '', $pathname));

        switch($pathArray[0]) {

            case 'account':
                include(__DIR__ . '/../classes/Auth.php');
                $module = new Auth($this->galleryDB, $this->userData);
                return $module->getPage();

            case 'admin':
                include(__DIR__ . '/../classes/Admin.php');
                $module = new Admin($this->galleryDB, $this->userData);
                $module->pathArray = $pathArray;
                return $module->getPage();

            case 'photos':
                include(__DIR__ . '/../classes/Photos.php');
                $module = new Photos($this->galleryDB, $this->userData);
                return $module->getPage($isAJAX);

            case 'albums':
                if($pathArray[1]) {
                    include(__DIR__ . '/../classes/Photos.php');
                    include(__DIR__ . '/../classes/AlbumView.php');
                    $module = new AlbumView($this->galleryDB, $this->userData);
                    $module->album = $pathArray[1];
                    return $module->getPage($isAJAX);
                }
                else {
                    include(__DIR__ . '/../classes/Albums.php');
                    $module = new Albums($this->galleryDB, $this->userData);
                    return $module->getPage();
                }

            default:
                return array(
                    'page_not_found' => true,
                    'title' => 'Страница не найдена — '.SITE_NAME,
                    'content' => '');
        }
    }

    public function getPersonalMenu($tab, $userData) {
        if($userData) {
            $personalMenu = '
            <div class="h-p-btns">
                '.($userData['is_admin'] ? '<div class="h-go-admin"><a href="/admin" tab-name="admin"'.($tab == 'admin' ? ' active-tab="true"' : '').'>Управление</a></div>' : '').'
                <a href="/account" tab-name="account"'.($tab == 'account' ? ' active-tab="true"' : '').' class="h-profile">
                    <div class="h-p-icon" style="background-image: url(/static/icons/user.svg);"></div>
                    <div class="h-p-name">'.$userData['username'].'</div>
                </a>
            </div>
        ';
        }
        else {
            $personalMenu = '<a href="/account" tab-name="account"'.($tab == 'profile' ? ' active-tab="true"' : '').'>Войти в аккаунт</a>';
        }
        return $personalMenu;
    }
}