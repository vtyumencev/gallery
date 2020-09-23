<?php


namespace Gallery;

class AlbumView extends Photos
{
    public $album;
    public function getPage($forAJAX = NULL) {


        $private_join = '';
        $private_where = '';
        if(static::$userData) {
            $private_join = 'LEFT JOIN albums_users ON albums_users.album_id = albums.id';
            $private_where = 'OR albums_users.user_id = '.static::$userData['id'];
        }

        $albumRes = static::$dataBase->query('
            SELECT *
            FROM albums
            '.$private_join.'
            WHERE address = "'.$this->album.'"
            AND deleted = 0
            AND (private = 0 '.$private_where.')
            ');
        $albumData = $albumRes->fetch_assoc();

        if($albumData) {

            $res = static::$dataBase->query('
                SELECT photos.*
                FROM photos
                INNER JOIN albums_photos ON albums_photos.photo_id = photos.id
                INNER JOIN albums ON albums.id = albums_photos.album_id
                WHERE albums.address = "'.$this->album.'"
            ');

            $photosPage = $this->getPhotos($res, $forAJAX);

            $htmlContent = '
            <div class="album-view">
                <div class="a-v-title">Альбом '.$albumData['name'].'</div>
                '.$photosPage['photosItems'].'
            </div>';


            return array(
                'active_tab' => 'none',
                'initJS' => 'photos',
                'title' => 'Альбом '.$albumData['name'].' — '.SITE_NAME,
                'photosData' => $photosPage['photosData'],
                'content' => $htmlContent) ;
        }
        else {
            return array(
                'page_not_found' => true);
        }
    }
}