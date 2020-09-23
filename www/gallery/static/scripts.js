let viewPhotos = [];
let selectedPhotoIndex = 0;
let startedFromVW = false;
let titlePhotoView = 'Просмотр фото — Галерея';
let lastResizeWidth = 0;
let timeoutResizeGrid = 0;


$(document).ready(function() {
    history.replaceState({title: document.title}, '');
    if(document.location.search.match(/view=[0-9]+/)) {
        startedFromVW = true;
        photosOpenViewWindow(document.location.search, false);
    }
    photosTouchSupport();
});

window.onresize = function() {
    if($('html').hasClass('vw-show')) {
        let photoData = viewPhotos[selectedPhotoIndex];
        photosUpdateViewWindowPosition($('.view-window .vw-image'), photoData.image_big_width, photoData.image_big_height);
    }
    if($('.photos-items').length && lastResizeWidth != window.innerWidth) {
        clearTimeout(timeoutResizeGrid);
        timeoutResizeGrid = setTimeout(function() {
            photosGridBuild(viewPhotos);
        }, 100)
        lastResizeWidth = window.innerWidth;
    }
};

$(document).on('click', '.si-f-button', function() {
    signInQuery();
}).on('keyup', '.si-f-username, .si-f-password', function(e) {
    if(e.keyCode === 13) {
        signInQuery();
    }
});


function signInQuery() {
    if($('.si-f-button').hasClass('disabled')) return;
    else if(!$('.si-f-username').val()) return $('.si-f-username').focus();
    else if(!$('.si-f-password').val()) return $('.si-f-password').focus();
    else {
        $('.si-f-button').addClass('disabled', true);
        $.post('/ajax.php?select=signIn', {
            username: $('.si-f-username').val(),
            password: $('.si-f-password').val()
        }, function (data) {
            if(data.error == 1) {
                $('.si-f-msg').show().html(data.error_msg);
                $('.si-f-button').removeClass('disabled');
            }
            else if(data.success == 1) {
                getPage(document.location.pathname, false, true);
                $('.si-f-msg').hide();
            }
        }, 'json');
    }
}

$(document).on('click', '.as-logout-btn', function() {
    $.post('/ajax.php?select=logout', {
    }, function (data) {
        if(data.success === 1) getPage(document.location.pathname, false, true);
    }, 'json');
});

$(document).keydown(function(e) {
    if (e.keyCode === 27 && $('html').hasClass('vw-show')) {
        $('.view-window .vw-close').click();
    }
    else if(e.keyCode === 37 && $('html').hasClass('vw-show')) {
        photosPrevVWItem();
    }
    else if(e.keyCode === 39 && $('html').hasClass('vw-show')) {
        photosNextVWItem();
    }
});

$(document).on('click', 'a[href]', function(e) {
    if($(this).attr('target')) return;
    e.preventDefault(this);
    if($(this).attr('href').match(/view=[0-9]+/)) {
        $('.view-window .vw-image')
            .width($(this).width()).height($(this).height())
            .css('margin-left', $(this).offset().left + (window.innerWidth - document.documentElement.clientWidth) / 2)
            .css('margin-top', $(this).offset().top - $(document).scrollTop());
    }
    getPage($(this).attr('href'), true, false);
});
window.onpopstate = function(event) {
    if($('html').hasClass('vw-show') && !document.location.search.match(/view=[0-9]+/)) {
        photosCloseViewWindow();
    }
    else {
        getPage(document.location.pathname + document.location.search, false, false);
    }
};

function getPage(pathname, changeState, getPersonalMenu) {
    if(pathname.match(/view=/)) {
        photosOpenViewWindow(pathname, changeState);
    }
    else {
        $.get('/ajax.php?select=getPage&pm=' + getPersonalMenu + '&pathname=' + pathname, function (data) {
            $('.page-content').html(data.module.content);

            document.title = data.module.title;

            if(changeState) history.pushState({title: data.module.title}, '', pathname);

            if(getPersonalMenu) $('.h-personal-menu').html(data.personal_menu);
            if(data.module.initJS == 'photos') {
                viewPhotos = JSON.parse(data.module.photosData);
                photosGridBuild(viewPhotos);
            }
            if(data.module.active_tab) {
                $('[active-tab]').removeAttr('active-tab');
                if(data.module.active_tab != 'none') $('[tab-name="' + data.module.active_tab + '"]').attr('active-tab', true);
            }
        }, 'json');
    }
}

function photosOpenViewWindow(path, changeState) {
    let selectedPhoto = path.match(/view=([0-9]+)/)[1];
    selectedPhotoIndex = viewPhotos.findIndex(x => x.id === selectedPhoto);
    let photoData = viewPhotos[selectedPhotoIndex];
    $('html').addClass('hide-scrollbar');
    $('.view-window .vw-image').attr('src', photoData.image_small);

    if(startedFromVW) {
        $('.view-window .vw-image').attr('src', photoData.image_big);
        photosUpdateViewWindowPosition($('.view-window .vw-image'), photoData.image_big_width, photoData.image_big_height);
        setTimeout(function() {
            $('.view-window .vw-image').addClass('animate');
        },200);
    }
    else {
        setTimeout(function() {
            $('html').addClass('vw-show');
            $('.view-window .vw-image').addClass('animate');
            photosUpdateViewWindowPosition($('.view-window .vw-image'), photoData.image_big_width, photoData.image_big_height);
            setTimeout(function() {
                $('.view-window .vw-image').attr('src', photoData.image_big);
            }, 300);
        },200);
    }
    if(changeState) {
        history.pushState({title: titlePhotoView}, '', path);
    }
    else history.replaceState({title: titlePhotoView, oldTitle: history.state.title},  '');
    document.title = titlePhotoView;
    photosUpdateViewWindowElements();
}

$(document).on('click', '.view-window .vw-close', function(e) {
    if(startedFromVW) {
        photosCloseViewWindow();
        document.title = history.state.oldTitle;
        history.replaceState({title: history.state.oldTitle},  '', document.location.pathname);
        startedFromVW = false;
    }
    else history.back();
}).on('click', '.view-window .vw-next', function(e) {
    photosNextVWItem();
}).on('click', '.view-window .vw-prev', function(e) {
    photosPrevVWItem();
});

function photosNextVWItem() {
    let photoData = viewPhotos[selectedPhotoIndex + 1];
    if(photoData) {
        $('.view-window .vw-image').removeClass('animate');
        setTimeout(function () {
            $('.view-window .vw-image').addClass('animate');
        }, 50);
        $('.vw-image').attr('src', '');
        $('.vw-image').attr('src', photoData.image_big);
        selectedPhotoIndex++;
        photosUpdateViewWindowPosition($('.view-window .vw-image'), photoData.image_big_width, photoData.image_big_height);
        history.replaceState(history.state,  '', '?view=' + photoData.id);
        photosUpdateViewWindowElements();
    }
}

function photosPrevVWItem() {
    let photoData = viewPhotos[selectedPhotoIndex - 1];
    if(photoData) {
        $('.view-window .vw-image').removeClass('animate');
        setTimeout(function () {
            $('.view-window .vw-image').addClass('animate');
        }, 50);
        $('.vw-image').attr('src', '');
        $('.vw-image').attr('src', photoData.image_big);
        selectedPhotoIndex--;
        photosUpdateViewWindowPosition($('.view-window .vw-image'), photoData.image_big_width, photoData.image_big_height);
        history.replaceState(history.state,  '', '?view=' + photoData.id);
        photosUpdateViewWindowElements();
    }
}

function photosCloseViewWindow() {
    $('html').removeClass('hide-scrollbar');
    $('html').removeClass('vw-show');


    let p = $('[data-photo-id="' + viewPhotos[selectedPhotoIndex].id + '"]');
    p.addClass('hidden');
    $('.view-window .vw-image')
        .width(p.width()).height(p.height())
        .css('margin-left', p.offset().left)
        .css('margin-top', p.offset().top - $(document).scrollTop());

    setTimeout(function() {
        $('.view-window .vw-image').removeClass('animate');
        p.removeClass('hidden');
    }, 300);
    document.title = history.state.title;
}

function photosGridBuild(photos) {
    let elPlace = $('.photos-items');
    elPlace.empty();

    let start = 0;
    let margin = 4;
    let row = 300;

    let pageWidth = $('.photos-items').outerWidth();
    let checkedScrollbar = false;

    if(window.innerWidth < 500) row = 200;

    while(photos.length > start) {
        let sum = 0;
        let count = 0;
        let h = photos[start].image_small_height;
        if(h < row) h = row;

        while(h >= row && photos[start + count] || photos[start + count] && !photos[start + count + 1]) {
            sum = sum + photos[start + count].image_small_width * photos[start].image_small_height / photos[start + count].image_small_height;
            h = pageWidth * photos[start].image_small_height / sum;
            count++;
        }

        if(h > row) h = row;

        for(let i = 0; i < count; i++) {
            let w = h / photos[start + i].image_small_height * photos[start + i].image_small_width;
            elPlace.append(`
                <div class="p-item">
                    <a href="?view=${photos[start + i].id}" data-photo-id="${photos[start + i].id}">
                        <img class="p-item-img" src="${photos[start + i].image_small}" style="height: ${(h - margin)}px; width: ${(w - margin)}px;">
                    </a>
                </div>`);
        }
        start = start + count;

        if(!checkedScrollbar && $('.photos-items').outerWidth() != pageWidth) {
            checkedScrollbar = true;
            pageWidth = $('.photos-items').outerWidth();
            elPlace.empty();
            start = 0;
        }
    }
}

function photosUpdateViewWindowPosition(el, vw_original_w, vw_original_h) {
    let vw_new_w = vw_original_w, vw_new_h = vw_original_h;
    if (vw_original_h / vw_original_w > window.innerHeight / window.innerWidth) {
        if(vw_original_h > window.innerHeight) {
            vw_new_h = window.innerHeight;
            vw_new_w = vw_original_w / vw_original_h * window.innerHeight;
        }
    }
    else {
        if(vw_original_w > window.innerWidth) {
            vw_new_w = window.innerWidth;
            vw_new_h = vw_original_h / vw_original_w * window.innerWidth;
        }
    }
    if(vw_new_h < window.innerHeight) {
        el.css('margin-top', window.innerHeight / 2 - (vw_new_h / 2));
    }
    else el.css('margin-top', 0);
    if(vw_new_w < window.innerWidth) {
        el.css('margin-left', window.innerWidth / 2 - (vw_new_w / 2));
    }
    else el.css('margin-left', 0);
    el.width(vw_new_w).height(vw_new_h);
}

function photosUpdateViewWindowElements() {
    let photoData = viewPhotos[selectedPhotoIndex + 1];
    if(photoData) {
        $('.vw-next').removeClass('hidden');
    }
    else $('.vw-next').addClass('hidden');

    photoData = viewPhotos[selectedPhotoIndex - 1];
    if(photoData) {
        $('.vw-prev').removeClass('hidden');
    }
    else $('.vw-prev').addClass('hidden');
}

function photosTouchSupport() {
    let
        obj = $('.view-window')[0],
        lastTouch = null,
        startVWY = 0,
        phantom = 0,
        posX = 0,
        posY = 0,
        type = 0,
        touchesCount = 0,
        timeOutMove = false;

    obj.addEventListener('touchstart', function(event) {
        if(timeOutMove) return;
        touchesCount = event.touches.length;
        if(touchesCount === 1) {
            startVWY = $('.vw-image').offset().top - window.scrollY;
            posX = event.targetTouches[0].pageX;
            posY = event.targetTouches[0].pageY;
        }
    });

    obj.addEventListener('touchmove', function(event) {
        if(touchesCount === 1) {
            let touch = event.targetTouches[0];
            if((Math.abs(posY - touch.pageY) > 10 || type === 1) && type !== 2) {
                type = 1;
                $('.vw-image').removeClass('animate').css('transform', 'translateY(' + (touch.pageY - posY) + 'px)');

            }
            else if(Math.abs(posX - touch.pageX) > 10) {
                type = 2;

                if(posX - touch.pageX > 0)  {
                    if(viewPhotos[selectedPhotoIndex + 1]) {
                        if(phantom !== 2) {
                            let photoData = viewPhotos[selectedPhotoIndex + 1];
                            $('.vw-image-phantom').attr('src', photoData.image_small).addClass('show').removeClass('animate');
                            photosUpdateViewWindowPosition($('.vw-image-phantom'), photoData.image_big_width, photoData.image_big_height);
                            phantom = 2;
                        }
                        $('.vw-image-phantom').css('transform', 'translateX(' + (window.innerWidth + (touch.pageX - posX)) + 'px)');
                    }
                    else {
                        phantom = 0;
                    }
                }
                else if(posX - touch.pageX < 0)  {
                    if(viewPhotos[selectedPhotoIndex - 1]) {
                        if(phantom !== 1) {
                            let photoData = viewPhotos[selectedPhotoIndex - 1];
                            $('.vw-image-phantom').attr('src', photoData.image_small).addClass('show').removeClass('animate');
                            photosUpdateViewWindowPosition($('.vw-image-phantom'), photoData.image_big_width, photoData.image_big_height);
                            phantom = 1;
                        }
                        $('.vw-image-phantom').css('transform', 'translateX(' + (-window.innerWidth + (touch.pageX - posX)) + 'px)');
                    }
                    else {
                        phantom = 0;
                    }
                }

                $('.vw-image').removeClass('animate').css('transform', 'translateX(' + (phantom === 0 ? (touch.pageX - posX) / 10 : (touch.pageX - posX)) + 'px)');
            }
            lastTouch = event;
        }
        event.preventDefault();
    });

    obj.addEventListener('touchend', function(event) {
        if(lastTouch) {

            let touch = lastTouch.targetTouches[0];

            if(type === 1 && Math.abs(posY - touch.pageY) > 100) {
                $('.vw-image').css('transform', 'translateY(0)').css('margin-top', (touch.pageY - posY + startVWY));
                setTimeout(function () {
                    $('.vw-image').addClass('animate');
                    $('.view-window .vw-close').click();
                    }, 10);
            }
            else if(phantom && type === 2 && posX - touch.pageX > 50) {
                timeOutMove = true;
                $('.vw-image').addClass('animate').css('transform', 'translateX(' + (-window.innerWidth) + 'px)');
                $('.vw-image-phantom').addClass('animate').css('transform', 'translateX(0)');
                selectedPhotoIndex++;
                let photoData = viewPhotos[selectedPhotoIndex];
                history.replaceState(history.state,  '', '?view=' + photoData.id);
                timeOutMove = setTimeout(function () {
                    $('.vw-image').removeClass('animate').css('transform', 'translateX(0)');

                    $('.vw-image-phantom').attr('src', photoData.image_big).removeClass('show');

                    let ph = $('.vw-image');
                    $('.vw-image-phantom').addClass('vw-image').removeClass('vw-image-phantom');
                    ph.removeClass('vw-image').addClass('vw-image-phantom');
                    timeOutMove = false;
                    }, 300);
                phantom = 0;
            }
            else if(phantom && type === 2 && touch.pageX - posX > 50) {
                timeOutMove = true;
                $('.vw-image').addClass('animate').css('transform', 'translateX(' + window.innerWidth + 'px)');
                $('.vw-image-phantom').addClass('animate').css('transform', 'translateX(0)');
                selectedPhotoIndex--;
                let photoData = viewPhotos[selectedPhotoIndex];
                history.replaceState(history.state,  '', '?view=' + photoData.id);
                setTimeout(function () {
                    $('.vw-image').removeClass('animate').css('transform', 'translateX(0)');

                    $('.vw-image-phantom').attr('src', photoData.image_big).removeClass('show');

                    let ph = $('.vw-image');
                    $('.vw-image-phantom').addClass('vw-image').removeClass('vw-image-phantom');
                    ph.removeClass('vw-image').addClass('vw-image-phantom');
                    timeOutMove = false;
                }, 300);
                phantom = 0;
            }
            else {
                if(phantom === 1) $('.vw-image-phantom').addClass('animate').css('transform', 'translateX(' + (-window.innerWidth) + 'px)');
                else if(phantom === 2) $('.vw-image-phantom').addClass('animate').css('transform', 'translateX(' + window.innerWidth + 'px)');
                phantom = 0;
                $('.vw-image').addClass('animate').css('transform', 'translate(0, 0)');
            }
        }
        type = 0;
        touchesCount = 0;
        lastTouch = null;
    });
}
