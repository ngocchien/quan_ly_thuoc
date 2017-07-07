/**
 * Created by tuandv on 8/9/16.
 */
(function ($) {
    var obj = null;
    var xhr = null;
    var photoJs = function (el, options) {
        var self = this;
        this.el = el;
        this.container = options.container || el;

        this.params = {
            object: ''
        };

        this.options = {
            object: ''
        };

        $.each(options, function (field, value) {
            switch (field) {
                case 'object':
                case 'selected':
                    self.options[field] = value;
                    self.params[field] = value;
                    break;
                default :
                    break;
            }
        });

        this.photoJs = null;
        this.render = function(){
            this.photoJs = $('<div></div>').addClass('photojs-v1');
            var container = '<div class="model-photojs">' +
                    '<div class="model-panel"></div>' +
                    '<div class="model-container">' +
                        '<div class="photojs-header">' +
                            '<h3>Hình ảnh</h3>' +
                            '<span class="close">×</span>' +
                        '</div>' +
                        '<div class="photojs-nabar">' +
                            '<ul>' +
                                '<li data-tab="libs" class="tab-libs active">Hình ảnh</li>' +
                                '<li data-tab="up" class="tab-up">Upload</li>' +
                                '<li data-tab="from-url" class="tab-from-url">Insert from url</li>' +
                            '</ul>' +
                        '</div>' +
                        '<div class="photojs-contents">' +
                            '<div class="photojs-content">' +
                                '<div class="photojs-content-libs">' +
                                    '<div class="photojs-libs-list">' +
                                        '<div class="loading" style="display: block"><div class="loading-icon"></div></div>'+
                                        '<div class="photojs-libs-list-files"></div>' +
                                    '</div>' +
                                    '<div class="photojs-selected">' +
                                        '<h3>CHI TIẾT HÌNH ẢNH</h3>' +
                                        '<div class="photojs-selected-detail" style="display: none">' +
                                            '<div class="photojs-selected-detail-img"><img/></div>' +
                                            '<div class="photojs-selected-detail-info">' +
                                                '<p class="title">Photo name.png</p>' +
                                                '<p class="date">16/08/2016</p>' +
                                                '<span class="remove"><i class="ace-icon icon-trash"></i></span>' +
                                            '</div>' +
                                        '</div>' +
                                        '<div class="">' +

                                        '</div>' +
                                    '</div>' +
                                '</div>' +
                                '<div class="photojs-content-up">' +
                                    '<form method="post" enctype="multipart/form-data">' +
                                        '<input style="display: block" name="upload[]" data-page="admin" hidden="hidden" multiple type="file">' +
                                    '</form>' +
                                '</div>' +
                                '<div class="photojs-content-from-url">' +
                                    '<div class="photojs-from-url-content">' +
                                        '<div class="from-url-item">' +
                                            '<div class="from-url-top">' +
                                                '<input class="from-url-input" type="text" placeholder="https://">' +
                                            '</div>' +
                                            '<div class="from-url-body" style="display: none">' +
                                                '<div class="thumbnail">' +
                                                    '<img src="">' +
                                                '</div>' +
                                                '<div class="from-url-caption">' +
                                                    '<p>Caption</p>' +
                                                    '<input class="from-caption" type="text">' +
                                                '</div>' +
                                                '<div class="from-url-name">' +
                                                    '<p>File name</p>' +
                                                    '<input class="from-file-name" type="text">' +
                                                '</div>' +
                                                '<div class="from-url-button">' +
                                                    '<button class="insert-to-library button-item">Insert library</button>' +
                                                '</div>' +
                                            '</div>' +
                                        '</div>'+
                                    '</div>'+
                                '</div>' +
                            '</div>' +
                        '</div>' +
                        '<div class="photojs-footer">' +
                            '<button type="button">Đồng ý</button>' +
                        '</div>' +
                    '</div>' +
                '</div>';

            this.photoJs.html(container);
            this.photoJs.appendTo($('.data-view'));

            //self.load();

            this.photoJs.find('.photojs-header').on('click', '.close', function(){
                self.photoJs.find('.model-photojs').hide();
            });

            this.photoJs.find('.photojs-nabar').on('click', 'li', function(){
                var tab = $(this).data('tab');
                $(this).parent().find('.active').removeClass('active');
                $(this).addClass('active');

                //
                switch (tab){
                    case 'libs':
                        self.photoJs.find('.photojs-contents').find('.photojs-content-libs').show();
                        self.photoJs.find('.photojs-contents').find('.photojs-content-up').hide();
                        self.photoJs.find('.photojs-contents').find('.photojs-content-from-url').hide();

                        break;
                    case 'up':
                        self.photoJs.find('.photojs-contents').find('.photojs-content-libs').hide();
                        self.photoJs.find('.photojs-contents').find('.photojs-content-up').show();
                        self.photoJs.find('.photojs-contents').find('.photojs-content-from-url').hide();

                        break;
                    case 'from-url':
                        self.photoJs.find('.photojs-contents').find('.photojs-content-libs').hide();
                        self.photoJs.find('.photojs-contents').find('.photojs-content-up').hide();
                        self.photoJs.find('.photojs-contents').find('.photojs-content-from-url').show();
                        break;
                }
            });

            this.photoJs.find('.photojs-content-up').find('input').change(function (evt) {
                var me = $(this);
                var files = evt.target.files;
                var form = self.photoJs.find('.photojs-content-up').find('form');

                form.ajaxForm({
                    type: 'post',
                    multiple: true,
                    url: Registry.get('SITE_URL') + 'api/administrator/uploads',
                    data: {},
                    beforeSend: function () {},
                    uploadProgress: function (event, position, total, percentComplete) {
                        //on progress
                        console.log('uploadProgress');
                    },
                    complete: function (response) {
                        // on complete
                        console.log('complete');
                    },
                    success: function (data) {
                        self.photoJs.find('.photojs-content-up').find('input').val('');

                        //focus tab libs
                        self.photoJs.find('.photojs-contents').find('.photojs-content-libs').show();
                        self.photoJs.find('.photojs-contents').find('.photojs-content-up').hide();

                        self.load();
                    }
                });

                form.submit();
            });

            this.validURL = function(str) {
                var pattern = /^(?:(?:https?|ftp):\/\/)(?:\S+(?::\S*)?@)?(?:(?!10(?:\.\d{1,3}){3})(?!127(?:\.\d{1,3}){3})(?!169\.254(?:\.\d{1,3}){2})(?!192\.168(?:\.\d{1,3}){2})(?!172\.(?:1[6-9]|2\d|3[0-1])(?:\.\d{1,3}){2})(?:[1-9]\d?|1\d\d|2[01]\d|22[0-3])(?:\.(?:1?\d{1,2}|2[0-4]\d|25[0-5])){2}(?:\.(?:[1-9]\d?|1\d\d|2[0-4]\d|25[0-4]))|(?:(?:[a-z\u00a1-\uffff0-9]+-?)*[a-z\u00a1-\uffff0-9]+)(?:\.(?:[a-z\u00a1-\uffff0-9]+-?)*[a-z\u00a1-\uffff0-9]+)*(?:\.(?:[a-z\u00a1-\uffff]{2,})))(?::\d{2,5})?(?:\/[^\s]*)?$/i;
                return pattern.test(str);
            };

            this.photoJs.on('input', '.from-url-input', function () {
                var href = $(this).val();

                if(self.validURL(href)){
                    $(this).parent().parent().find('.thumbnail').find('img').prop('src', href);

                    //
                    $(this).parent().parent().find('.from-url-body').show();
                }
            });

            this.photoJs.on('click', '.insert-to-library', function () {
                var href = $(this).parent().parent().parent().find('.from-url-input').val();
                var file_name = $(this).parent().parent().find('.from-file-name').val();
                var me = $(this);
                var type = me.data('set');
                if(self.validURL(href)){
                    var size = self.photoJs.find('.from-url-item').size();
                    if(size > 1){
                        me.parent().parent().parent().remove();

                        //Reload
                        self.load();
                    }else{
                        //setting
                        var from_url_body = self.photoJs.find('.from-url-body');
                        me.parent().parent().parent().find('.from-url-input').val('');
                        from_url_body.hide();

                        self.photoJs.find('.photojs-contents').find('.photojs-content-libs').show();
                        self.photoJs.find('.photojs-contents').find('.photojs-content-up').hide();
                        self.photoJs.find('.photojs-contents').find('.photojs-content-from-url').hide();
                        self.photoJs.find('.photojs-nabar').find('ul li.active').removeClass('active');
                        self.photoJs.find('.photojs-nabar').find('ul li.tab-libs').addClass('active');
                    }

                    $.ajax({
                        method: 'POST',
                        dataType: 'json',
                        url: Registry.get('SITE_URL') + 'api/administrator/uploads',
                        data: {type: 'from-url', href: href, file_name: file_name},
                        beforeSend: function () {},
                        success: function(resp) {
                            //Reload
                            self.load();

                            if(type === 'cover'){
                                //
                                me.data('set', '');

                                if (typeof(Storage) !== "undefined") {
                                    if (localStorage.getItem("insert-from-url") !== null) {
                                        var insert_from_url = JSON.parse(localStorage.getItem("insert-from-url"));

                                        if(insert_from_url.cover){
                                            insert_from_url.cover = '';
                                            localStorage.setItem("insert-from-url", JSON.stringify(insert_from_url));
                                        }
                                    }
                                }

                            }

                            if(type === 'poster'){
                                //
                                me.data('set', '');

                                if (typeof(Storage) !== "undefined") {
                                    if (localStorage.getItem("insert-from-url") !== null) {
                                        var insert_from_url = JSON.parse(localStorage.getItem("insert-from-url"));

                                        if(insert_from_url.poster){
                                            insert_from_url.poster = '';
                                            localStorage.setItem("insert-from-url", JSON.stringify(insert_from_url));
                                        }
                                    }
                                }

                            }
                        }
                    });
                }
            });

            this.photoJs.on('click', '.photojs-image', function(){
                $(this).parent().parent().parent().find('.photojs-image.active').removeClass('active');
                $(this).addClass('active');

                //get info
                var fid = $(this).find('img').data('id');
                var file_name = $(this).find('img').data('name') ? $(this).find('img').data('name') : 'File name';
                var ctime = $(this).find('img').data('ctime') ? $(this).find('img').data('ctime') : 'Date';
                var src = $(this).find('img').attr('src');

                var photojs_selected = self.photoJs.find('.photojs-selected');
                photojs_selected.find('.photojs-selected-detail').show();
                photojs_selected.find('.photojs-selected-detail-img').find('img').prop('src', src);
                photojs_selected.find('.photojs-selected-detail-info').find('.title').text(file_name);
                photojs_selected.find('.photojs-selected-detail-info').find('.date').text(ctime);

            });

            this.photoJs.find('.photojs-footer').on('click', 'button', function(){
                var src = self.photoJs.find('.photojs-libs-list-files').find('.photojs-image.active img').attr('src');
                var file = '<img src="' + src + '" style="-webkit-box-shadow:rgba(0, 0, 0, 0.2) 0px 1px 3px; -webkit-transition:all 0.2s ease-in-out; border:1px solid rgb(194, 194, 194); box-shadow:rgba(0, 0, 0, 0.298039) 0px 1px 3px; display:block; height:auto; margin:0px auto; max-width:90%; transition:all 0.2s ease-in-out; vertical-align:middle;"><p style="text-align:center"><em></em></p>';
                var fid = '';
                var file_name = '';

                switch (self.params.object){
                    case 'editor':
                        CKEDITOR.instances['editor'].insertHtml(file);
                        break;
                    case 'picture':
                        //get normal, medium, small (id)
                        fid = self.photoJs.find('.photojs-libs-list-files').find('.photojs-image.active img').data('id');

                        self.el.parent().parent().find('img').prop('src', src);
                        self.el.parent().parent().find('input').val(fid);

                        break;
                    case 'multiple':
                        fid = self.photoJs.find('.photojs-libs-list-files').find('.photojs-image.active img').data('id');
                        file_name = self.photoJs.find('.photojs-libs-list-files').find('.photojs-image.active img').data('name');

                        var html = '';
                        html += '<tr>';
                        html += '<td><img src="'+ src +'">';
                        html += '<input type="hidden" name="fid[]" value="'+ fid +'">';
                        html += '<input type="hidden" name="file-name[]" value="'+ file_name +'">';
                        html += '</td><td>'+ file_name +'</td>';
                        html += '<td class="remove" data-fid="'+ fid +'"><i class="product-galaxy-remove icon-trash"></i></td>';
                        html += '</tr>';
                        $('.col-product-table').find('table tbody').append(html);
                        break;
                }

                //hide popup
                self.photoJs.find('.model-photojs').hide();

            });

        };

        el.on('click', function(){
            var model_photojs = self.photoJs.find('.model-photojs');
            var model_container = self.photoJs.find('.model-container');

            self.load();

            model_photojs.show();

            //load cover & poster from localstorage
            if (typeof(Storage) !== "undefined") {
                if(localStorage.getItem("insert-from-url") !== null){
                    var insert_from_url = JSON.parse(localStorage.getItem("insert-from-url"));
                    if(insert_from_url && (insert_from_url.cover || insert_from_url.poster)){
                        self.photoJs.find('.photojs-contents').find('.photojs-content-libs').hide();
                        self.photoJs.find('.photojs-contents').find('.photojs-content-up').hide();
                        self.photoJs.find('.photojs-contents').find('.photojs-content-from-url').show();
                        self.photoJs.find('.photojs-nabar').find('.active').removeClass('active');
                        self.photoJs.find('.photojs-nabar').find('.tab-from-url').addClass('active');

                        console.log('insert_from_url', insert_from_url);
                        var photojs_from_url_content = self.photoJs.find('.photojs-from-url-content');

                        if(insert_from_url.cover){
                            var cover_inserted = false;
                            photojs_from_url_content.find('.from-url-input').each(function () {
                                if($(this).val() === ''){
                                    cover_inserted = true;

                                    $(this).val(insert_from_url.cover);

                                    $(this).parent().parent().find('.thumbnail').find('img').prop('src', insert_from_url.cover);
                                    if(insert_from_url.shorten){
                                        $(this).parent().parent().find('.from-file-name').val(insert_from_url.shorten + '-cover');
                                    }

                                    $(this).parent().parent().find('.from-url-body').show();
                                    $(this).parent().parent().find('.insert-to-library').data('set', 'cover');
                                }
                            });

                            //
                            if(!cover_inserted){
                                var cover_container = '';
                                cover_container = '<div class="from-url-item">' +
                                    '<div class="from-url-top">' +
                                        '<input class="from-url-input" value="'+ insert_from_url.cover +'" type="text" placeholder="https://">' +
                                    '</div>' +
                                    '<div class="from-url-body">' +
                                        '<div class="thumbnail">' +
                                            '<img src="'+ insert_from_url.cover +'">' +
                                        '</div>' +
                                        '<div class="from-url-caption">' +
                                            '<p>Caption</p>' +
                                            '<input class="from-caption" type="text">' +
                                        '</div>' +
                                        '<div class="from-url-name">' +
                                            '<p>File name</p>' +
                                            '<input class="from-file-name" value="'+ insert_from_url.shorten + '-cover' +'" type="text">' +
                                        '</div>' +
                                        '<div class="from-url-button">' +
                                            '<button data-set="cover" class="insert-to-library button-item">Insert library</button>' +
                                        '</div>' +
                                    '</div>' +
                                    '</div>';

                                photojs_from_url_content.append(cover_container);
                            }
                        }

                        if(insert_from_url.poster){
                            var poster_inserted = false;
                            photojs_from_url_content.find('.from-url-input').each(function () {
                                if($(this).val() === ''){
                                    poster_inserted = true;

                                    $(this).val(insert_from_url.poster);

                                    $(this).parent().parent().find('.thumbnail').find('img').prop('src', insert_from_url.poster);
                                    if(insert_from_url.shorten){
                                        $(this).parent().parent().find('.from-file-name').val(insert_from_url.shorten + '-poster');
                                    }

                                    $(this).parent().parent().find('.from-url-body').show();
                                    $(this).parent().parent().find('.insert-to-library').data('set', 'poster');
                                }
                            });

                            //
                            if(!poster_inserted){
                                var poster_container = '';
                                poster_container = '<div class="from-url-item">' +
                                    '<div class="from-url-top">' +
                                        '<input class="from-url-input" value="'+ insert_from_url.poster +'" type="text" placeholder="https://">' +
                                    '</div>' +
                                    '<div class="from-url-body">' +
                                        '<div class="thumbnail">' +
                                            '<img src="'+ insert_from_url.poster +'">' +
                                        '</div>' +
                                        '<div class="from-url-caption">' +
                                            '<p>Caption</p>' +
                                            '<input class="from-caption" type="text">' +
                                        '</div>' +
                                        '<div class="from-url-name">' +
                                            '<p>File name</p>' +
                                            '<input class="from-file-name" value="'+ insert_from_url.shorten + '-poster' +'" type="text">' +
                                        '</div>' +
                                        '<div class="from-url-button">' +
                                            '<button data-set="poster" class="insert-to-library button-item">Insert library</button>' +
                                        '</div>' +
                                    '</div>' +
                                    '</div>';

                                photojs_from_url_content.append(poster_container);
                            }
                        }
                    }
                }
            }
        });

        this.load = function(){
            //show loading
            self.photoJs.find('.photojs-libs-list').find('.loading').fadeIn();

            /*if(xhr && xhr.readystate != 4){
                xhr.abort();
            }*/

            xhr = $.ajax({
                method: 'GET',
                dataType: 'json',
                url: Registry.get('SITE_URL') + 'api/administrator/uploads',
                data: {},
                beforeSend: function () {},
                success: function(resp) {
                    if(resp.data){
                        var container = '';
                        var STATIC_URL = Registry.get('STATIC_URL');

                        $.each(resp.data, function(month, files){
                            container += '<div class="photojs-month" data-month="'+ month +'">' +
                                            '<div class="photojs-month-title">Tháng '+ month +'</div>' +
                                                '<div class="photojs-month-content">';
                            $.each(files, function(key, file){
                                container += '<div class="photojs-image">' +
                                                '<div class="centered">' +
                                                    '<img data-id="'+ file.fid +'" data-name="'+ file.file_name +'" data-ctime="'+ file.ctime +'" src="'+ STATIC_URL + file.src +'">' +
                                                '</div>' +
                                            '</div>';

                            });
                            container += '</div></div></div>';
                        });

                        self.photoJs.find('.photojs-libs-list-files').html(container);

                        //hide loading
                        self.photoJs.find('.photojs-libs-list').find('.loading').fadeOut();
                    }
                }
            });
        };

        this.init();
    };

    photoJs.prototype = {
        init: function () {
            if (!this.el.data('photo-js')) {
                this.el.data('photo-js', 'photo-js-jquery_' + parseInt(Math.random() * 1000));
                var self = this;
                self.render();
            }
        }
    };
    $.fn.photoJs = function (options) {
        var param = {};
        this.each(function () {
            obj = new photoJs($(this), $.extend(options, param));
            $(this).data('obj_photojs', obj);
        });

        return this;
    };

})(window.jQuery);
