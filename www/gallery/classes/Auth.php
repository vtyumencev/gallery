<?php


namespace Gallery;


class Auth extends Module
{
    public function getPage($isAJAX = NULL) {

        if(static::$userData) {
            $form = '
                <div class="already-signedIn">
                    <div>Вы вошли под именем '.static::$userData['username'].'</div>
                    <div class="as-logout"><div class="as-logout-btn"><a>Выйти из аккаунта</a></div></div>
                </div>';
        }
        else $form = '
            <input class="si-f-username" type="text" name="username login" placeholder="Имя пользователя">
            <input class="si-f-password" type="password" name="password" placeholder="Пароль">
            <button class="si-f-button">Войти</button>
            <div  class="si-f-msg"></div>';
            $htmlContent =
                '<div class="signin-page">
                    <div class="signin-block">
                        <div class="a-info">
                            <div class="si-i-icon"></div>
                            <div class="si-i-title">Моя фотогалерея</div>
                            <div class="si-i-desc">Панель аутентификации</div>
                        </div>
                        <div class="si-form">
                            '.$form.'
                        </div>
                    </div>
                </div>';

        return array(
            'active_tab' => 'account',
            'title' => 'Мой аккаунт — '.SITE_NAME,
            'content' => $htmlContent,
            'hide-header' => true);
    }

}