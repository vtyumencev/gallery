<?php


namespace Gallery;


class Admin extends Module
{
    public $pathArray = '';
    function getPage($isAJAX = NULL) {
        if($this->pathArray[1]) {
            if($this->pathArray[1] == 'albums') {
                $albumData = NULL;
                $private_list = '';
                if(preg_match("/[0-9]+$/", $this->pathArray[2])) {
                    $res = static::$dataBase->query('SELECT * FROM albums WHERE deleted = 0 AND id = '.$this->pathArray[2]);
                    $albumData = $res->fetch_assoc();

                    if($albumData) {
                        $res = static::$dataBase->query('
                        SELECT users.id as user_id, users.username as username
                        FROM users
                        INNER JOIN albums_users ON albums_users.user_id = users.id
                        WHERE albums_users.album_id = '.$albumData['id']);

                        while($usersData = $res->fetch_assoc()) {
                            $private_list .= '
                                <a class="ml-item" data-id="'.$usersData['user_id'].'">
                                    <div class="ml-item-inw">'.$usersData['username'].'</div>
                                    <div class="ml-item-remove"><div class="ml-item-remove-icon"></div></div>
                                </a>';
                        }
                    }
                }
                $htmlContent = '
                    <div class="manage-page">
                        <div class="manage-form">
                            <div class="mf-title"><a href="/admin">Обзор</a> → '.($albumData ? 'Редактирование альбома №'.$albumData['id'] : 'Создание альбома').'</div>
                            <div class="mf-field">
                                <div class="mf-f-name">Название альбома:</div>
                                <input type="text" name="name" id="name" value="'.$albumData['name'].'">
                            </div>
                            <div class="mf-field">
                                <div class="mf-f-name">Идентификатор альбома:</div>
                                <input type="text" name="address" id="address" value="'.$albumData['address'].'">
                            </div>
                            <div class="mf-field">
                                <div class="mf-f-name">Ограничение:</div>
                                <select class="mf-change-private" name="private" id="private">
                                    <option value="0">Доступно всем посетителям</option>
                                    <option value="1" '.($albumData['private'] ? 'selected' : '').'>Приватный доступ</option>
                                </select>
                            </div>
                            <div class="mf-field mf-private-list'.($albumData['private'] ? '' : ' hidden').'">
                                <div class="mf-f-name">Доступ к альбому:</div>
                                <div class="m-list">
                                    <input class="ml-input mf-check-users" type="text" name="username" placeholder="Добавить пользователя по имени">
                                    <div class="ml-items">'.$private_list.'</div>
                                    <div class="ml-info"'.($private_list ? ' style="display: none;"' : '').'>Пока что никто не имеет доступа к данному альбому</div>
                                </div>
                            </div>
                            <div class="mf-field buttons">
                                '.($albumData ?
                                    '<button class="mf-button mf-save save-form-albums" data-album-id="'.$albumData['id'].'">Сохранить</button><button class="mf-button mf-delete">Удалить альбом</button>' :
                                    '<button class="mf-button mf-save save-form-albums" data-album-id="0">Добавить альбом</button>').'
                            </div>
                        </div>
                    </div>';
            }
            else if($this->pathArray[1] == 'photos' && preg_match("/[0-9]+$/", $this->pathArray[2])) {

                $photoData = NULL;
                $private_list = '';
                $album_list = '';

                $res = static::$dataBase->query('SELECT * FROM photos WHERE deleted = 0 AND id = '.$this->pathArray[2]);
                $photoData = $res->fetch_assoc();

                if($photoData) {
                    $res = static::$dataBase->query('
                        SELECT users.id as user_id, users.username as username
                        FROM users
                        INNER JOIN photos_users ON photos_users.user_id = users.id
                        WHERE photos_users.photo_id = '.$photoData['id']);

                    while($usersData = $res->fetch_assoc()) {
                        $private_list .= '
                                <a class="ml-item" data-id="'.$usersData['user_id'].'">
                                    <div class="ml-item-inw">'.$usersData['username'].'</div>
                                    <div class="ml-item-remove"><div class="ml-item-remove-icon"></div></div>
                                </a>';
                    }


                    $res = static::$dataBase->query('
                        SELECT albums.id as album_id, albums.name as album_name
                        FROM albums
                        INNER JOIN albums_photos ON albums_photos.album_id = albums.id
                        WHERE albums_photos.photo_id = '.$photoData['id']);

                    while($albumData = $res->fetch_assoc()) {
                        $album_list .= '
                                <a class="ml-item" data-id="'.$albumData['album_id'].'">
                                    <div class="ml-item-inw">'.$albumData['album_name'].'</div>
                                    <div class="ml-item-remove"><div class="ml-item-remove-icon"></div></div>
                                </a>';
                    }
                }

                $htmlContent = '
                    <div class="manage-page">
                        <div class="manage-form">
                            <div class="mf-title"><a href="/admin">Обзор</a> → Редактирование фото №'.$photoData['id'].'</div>
                            <div class="mf-field">
                                <div class="mf-photo">
                                    <a href="'.$photoData['image_big'].'" target="_blank"><img src="'.$photoData['image_big'].'"></a>
                                </div>
                            </div>
                            <div class="mf-field">
                                <div class="mf-f-name">Ограничение:</div>
                                <select class="mf-change-private" name="private" id="private">
                                    <option value="0">Доступно всем посетителям</option>
                                    <option value="1" '.($photoData['private'] ? 'selected' : '').'>Приватный доступ</option>
                                </select>
                            </div>
                            <div class="mf-field mf-private-list'.($photoData['private'] ? '' : ' hidden').'">
                                <div class="mf-f-name">Доступ к фото:</div>
                                <div class="m-list">
                                    <input class="ml-input mf-check-users" type="text" name="username" placeholder="Добавить пользователя по имени">
                                    <div class="ml-items">'.$private_list.'</div>
                                    <div class="ml-info"'.($private_list ? ' style="display: none;"' : '').'>Пока что никто не имеет доступа к данной фотографии</div>
                                </div>
                            </div>
                            <div class="mf-field mf-album-list">
                                <div class="mf-f-name">Альбомы:</div>
                                <div class="m-list">
                                    <input class="ml-input mf-check-albums" type="text" placeholder="Добавить альбом по идентификатору">
                                    <div class="ml-items">'.$album_list.'</div>
                                    <div class="ml-info"'.($album_list ? ' style="display: none;"' : '').'>Данная фотография не находится ни в одном альбоме</div>
                                </div>
                            </div>
                            <div class="mf-field buttons">
                                <button class="mf-button mf-save save-form-photo" data-photo-id="'.$photoData['id'].'">Сохранить</button>
                                <button class="mf-button mf-delete">Удалить фото</button>
                            </div>
                        </div>
                    </div>';
            }
        }
        else {

            $res = static::$dataBase->query('
                SELECT a.id, a.name, p_count
                FROM albums a
                    LEFT OUTER JOIN (
                        SELECT album_id, COUNT(*) as p_count
                        FROM albums_photos 
                        GROUP BY album_id
                    ) ap ON ap.album_id = a.id
                WHERE deleted = 0');
            $albums = '';
            while($data = $res->fetch_assoc()) {
                $albums .= '
                <a class="ml-item" href="/admin/albums/'.$data['id'].'">
                    <div class="ml-item-inw">
                        <div class="ml-item-inw-name">'.$data['name'].'</div>
                        <div class="ml-item-inw-desc">'.($data['p_count'] ? $data['p_count'] : 'Нет ').' фото</div>
                    </div>
                </a>';
            }

            $photos = '';
            $res = static::$dataBase->query('SELECT * FROM photos WHERE deleted = 0 ORDER BY added_at DESC');
            while ($data = $res->fetch_assoc()) {
                $photos .= '<a class="bb-p-item" href="/admin/photos/'.$data['id'].'" style="background-image: url('.$data['image_small'].');"></a>';
            }

            $htmlContent = '
                <div class="manage-page">
                    <div class="manage-browse-blocks">
                        <div class="browse-block">
                            <div class="bb-title">Альбомы</div>
                            <a class="bb-add-button" href="/admin/albums/new">Добавить альбом</a>
                            <div class="m-list">
                                <div class="ml-items">'.$albums.'</div>
                            </div>
                        </div>
                        <div class="browse-block bb-photos">
                            <div class="bb-title">Фотографии</div>
                            <div class="add-photo-field">
                                <input type="file" id="add-photo" name="add-photo" accept="image/png, image/jpeg">
                                <label for="add-photo" class="bb-add-button">
                                    <div class="bb-ap-loading-icon"></div>
                                    <div class="bb-ap-text">Добавить фото</div>
                                    <div class="bb-ap-loading-text">Идет загрузка...</div>
                                </label>
                            </div>
                            <div class="bb-p-items">'.$photos.'</div>
                        </div>
                    </div>
                </div>';
        }


        return array(
            'active_tab' => 'admin',
            'title' => 'Управление — '.SITE_NAME,
            'content' => $htmlContent) ;
    }

}