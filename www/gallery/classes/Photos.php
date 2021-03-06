<?php


namespace Gallery;

class Photos extends Module
{
    public function getPage($isAJAX = NULL)
    {

        $private_join = '';
        $private_where = '';
        if(static::$userData) {
            $private_join = 'LEFT JOIN photos_users ON photos_users.photo_id = photos.id';
            $private_where = 'OR photos_users.user_id = '.static::$userData['id'];
        }

        $res = static::$dataBase->query('
            SELECT *
            FROM photos
            '.$private_join.'
            WHERE deleted = 0
              AND (private = 0 '.$private_where.')
            ORDER BY added_at DESC
            ');

        $photosPage = $this->getPhotos($res, $isAJAX);


        return array(
            'active_tab' => 'photos',
            'initJS' => 'photos',
            'photosData' => $photosPage['photosData'],
            'title' => 'Фотографии — ' . SITE_NAME,
            'content' => $photosPage['photosItems']);
    }

    public function getPhotos($res, $isAJAX)
    {
        $photos = array();
        while ($data = $res->fetch_assoc()) {
            $photos[] = array(
                'id' => $data['id'],
                'image_big' => $data['image_big'],
                'image_big_width' => $data['image_big_width'],
                'image_big_height' => $data['image_big_height'],
                'image_small' => $data['image_small'],
                'image_small_width' => $data['image_small_width'],
                'image_small_height' => $data['image_small_height']
            );
        }

        $photosData = json_encode($photos);

        $js =
            '<script>
            viewPhotos =' . $photosData . ';
            photosGridBuild(viewPhotos);
        </script>';


        $htmlContent = '<div class="photos-items"></div>' . ($isAJAX ? '' : $js);
        return array(
            'photosData' => $photosData,
            'photosItems' => $htmlContent);
    }
}