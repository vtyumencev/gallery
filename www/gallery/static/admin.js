$(document).on('change', '.mf-change-private', function (e) {
    if($(this).val() === "1") {
        $('.mf-private-list').removeClass('hidden');
    }
    else {
        $('.mf-private-list').addClass('hidden');
    }
}).on('keyup', '.mf-check-users', function (e) {
    if(e.keyCode === 13) {
        let username = $(this).val();
        let checkListExist = false;
        let mList = $(this).parents('.m-list');

        $('.ml-items .ml-item').each(function (i) {
            if(username === $(this).find('.ml-item-inw').html()) checkListExist = true;
        })
        if(checkListExist) return;

        $.get('/ajax.php?select=checkExistUser&username=' + username, function (data) {
            if(data.user_exist) {
                mList.find('.ml-items').append(`
                    <a class="ml-item" data-id="${data.user_id}">
                        <div class="ml-item-inw">${username}</div>
                        <div class="ml-item-remove"><div class="ml-item-remove-icon"></div></div>
                    </a>`);
                $('.mf-private-list').find('.ml-info').hide();
                $('.mf-check-users').val('');
            }
            else {

            }
        }, 'json');
    }
}).on('keyup', '.mf-check-albums', function (e) {
    if(e.keyCode === 13) {
        let album = $(this).val();
        let mList = $(this).parents('.m-list');

        $.get('/ajax.php?select=checkExistAlbum&address=' + album, function (data) {
            if(data.album_exist) {
                let checkListExist = false;
                mList.find('.ml-items .ml-item').each(function (i) {
                    if(data.album_id === $(this).attr('data-id')) checkListExist = true;
                });
                if(!checkListExist) {
                    mList.find('.ml-items').append(`
                    <a class="ml-item" data-id="${data.album_id}">
                        <div class="ml-item-inw">${data.album_name}</div>
                        <div class="ml-item-remove"><div class="ml-item-remove-icon"></div></div>
                    </a>`);
                    let checkItems = serializeMList(mList);
                    $('.mf-album-list').find('.ml-info').hide();
                    $('.mf-check-albums').val('');
                }
            }
        }, 'json');
    }
}).on('click', '.ml-item-remove-icon', function (e) {
    let mList = $(this).parents('.m-list');
    $(this).parents('.ml-item').remove();
    let checkItems = serializeMList(mList);
    if(checkItems.length === 0) mList.find('.ml-info').show();
}).on('click', '.save-form-albums', function (e) {
    let el = this;
    if($(el).hasClass('disabled')) return;
    $(el).addClass('disabled');
    $.post('/ajax.php?select=saveDataAlbum', {
        album_id: $(el).attr('data-album-id'),
        name: $('#name').val(),
        address: $('#address').val(),
        private: $('#private').val(),
        private_list: JSON.stringify(serializeMList($('.mf-private-list')))
    },  function (data) {
        if(data.success) {
            if($(el).attr('data-album-id') === '0') {
                getPage('/admin/albums/' + data.album_id, true, false);
            }
        }
        $(el).removeClass('disabled');
    }, 'json');
}).on('click', '.save-form-photo', function (e) {
    let el = this;
    if($(el).hasClass('disabled')) return;
    $(el).addClass('disabled');
    $.post('/ajax.php?select=saveDataPhoto', {
        photo_id: $(el).attr('data-photo-id'),
        private: $('#private').val(),
        private_list: JSON.stringify(serializeMList($('.mf-private-list'))),
        album_list: JSON.stringify(serializeMList($('.mf-album-list')))
    },  function (data) {
        if(data.success) {

        }
        $(el).removeClass('disabled');
    }, 'json');
});

$(document).on('change','#add-photo',function(e) {
    let el = $(this).parents('.add-photo-field');
    el.addClass('loading');
    let blobFile = this.files[0];
    let formData = new FormData();
    formData.append('fileToUpload', blobFile);
    $.ajax({
        url: 'ajax.php?select=uploadPhoto',
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function(data) {
            getPage('/admin/photos/' + data.photo_id, true, false);
        },
        error: function(jqXHR, textStatus, errorMessage) {

        }
    });
});

function serializeMList(el) {
    let list = [];
    $(el).find('.ml-items .ml-item').each(function (i) {
        list.push($(this).attr('data-id'));
    });

    return list;
}