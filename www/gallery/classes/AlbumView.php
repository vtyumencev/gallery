<?php


namespace Gallery;

class AlbumView extends Photos
{
    public $album;
    public function getPage($forAJAX = NULL) {
        $res = static::$dataBase->query( '
            SELECT photos.*
            FROM photos
            INNER JOIN albums_photos ON albums_photos.photo_id = photos.id
            INNER JOIN albums ON albums.id = albums_photos.album_id
            WHERE albums.address = "'.$this->album.'"
        ');

        $photosPage = $this->getPhotos($res, $forAJAX);

        $albumRes = static::$dataBase->query( 'SELECT * FROM albums WHERE address = "'.$this->album.'"');
        $albumData = $albumRes->fetch_assoc();

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
}