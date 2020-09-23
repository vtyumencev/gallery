<?php


namespace Gallery;


class Albums extends Module
{
    public function getPage($isAJAX = NULL) {

        $private_join = '';
        $private_where = '';
        if(static::$userData) {
            $private_join = 'LEFT JOIN albums_users ON albums_users.album_id = a.id';
            $private_where = 'OR albums_users.user_id = '.static::$userData['id'];
        }

        $res = static::$dataBase->query('
            SELECT a.id, a.address, a.name, a.private, p_count
            FROM albums a
                LEFT OUTER JOIN (
                    SELECT album_id, COUNT(*) as p_count
                    FROM albums_photos 
                    GROUP BY album_id
                ) ap ON ap.album_id = a.id
            '.$private_join.'
            WHERE a.deleted = 0
            AND (a.private = 0 '.$private_where.')
            ');


        $albums = '';
        while($albumsData = $res->fetch_assoc()) {
            $photosRes = static::$dataBase->query('
            SELECT photos.id, photos.image_small as image
            FROM photos
                INNER JOIN albums_photos ON albums_photos.photo_id = photos.id
            WHERE albums_photos.album_id = '.$albumsData['id'].'
            AND photos.deleted = 0
            AND photos.private = 0
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
                    <div class="a-i-count">
                        '.($albumsData['private'] ? '<div class="a-i-private"></div>' : '').'
                        <div>'.($albumsData['p_count'] ? $albumsData['p_count'].' фото' : 'Пустой альбом').'</div></div>
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