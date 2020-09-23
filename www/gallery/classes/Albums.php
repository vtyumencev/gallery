<?php


namespace Gallery;


class Albums extends Module
{
    public function getPage($isAJAX = NULL) {

        $res = static::$dataBase->query( '
            SELECT a.id, a.address, a.name, p_count
            FROM albums a
                LEFT OUTER JOIN (
                    SELECT album_id, COUNT(*) as p_count
                    FROM albums_photos 
                    GROUP BY album_id
                ) ap ON ap.album_id = a.id
            WHERE a.private = 0
            ');


        $albums = '';
        while($albumsData = $res->fetch_assoc()) {
            $photosRes = static::$dataBase->query( '
            SELECT photos.id, photos.image_small as image
            FROM photos
                INNER JOIN albums_photos ON albums_photos.photo_id = photos.id
            WHERE albums_photos.album_id = '.$albumsData['id'].'
            LIMIT 4
            ');

            $previews = '';
            $pCount = 0;
            while($photosData = mysqli_fetch_array($photosRes)) {
                $previews .= '<div class="a-i-preview-i" style="background-image: url('.$photosData['image'].')"></div>';
                $pCount++;
            }
            while($pCount < 4) {
                $previews .= '<div class="a-i-preview-i"></div>';
                $pCount++;
            }

            $albums .= '
            <a class="a-item" href="/albums/'.$albumsData['address'].'">
                <div class="a-i-cover">'.$previews.'</div>
                <div class="a-i-caption">
                    <div class="a-i-name">'.$albumsData['name'].'</div>
                    <div class="a-i-count">'.($albumsData['p_count'] ? $albumsData['p_count'].' фото' : 'Пустой альбом').'</div>
                </div>
            </a>';
        }

        $htmlContent = '<div class="albums-items">'.$albums.'</div>';


        return array(
            'active_tab' => 'albums',
            'title' => 'Альбомы — '.SITE_NAME,
            'content' => $htmlContent) ;
    }
}