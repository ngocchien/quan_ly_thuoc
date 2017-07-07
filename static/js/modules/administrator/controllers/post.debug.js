/**
 * Created by chiennn on 18/06/2017.
 */

Controller.define('administrator/post', function () {
    // var xhr =null;
    return {
        model: {
            delete: function (params) {
                return $.ajax({
                    type: 'post',
                    url: Registry.get('SITE_URL') + 'administrator/post/delete',
                    data: params,
                    dataType : 'json'
                });
            }
        },
        actions: {
            index: {
                require: {
                    scripts: ['bootbox/bootbox.js'],
                    stylesheets: []
                },
                execute: function () {
                    var self = this,
                        removeAll = function () {
                            if(self.find('input[name=data-id]:checked').length <= 0){
                                bootbox.alert('Vui lòng chọn bài viết muốn xóa!!!');
                                return false;
                            }
                            bootbox.confirm('Bạn có chắc chắn muốn xóa các bài viết này không???', function (e) {
                                if(e){
                                    var arr_id = [];
                                    self.find('input[name=data-id]:checked').each(function () {
                                        arr_id.push($(this).val())
                                    });
                                    self.model.delete({arr_post_id : arr_id}).then(function (rs){
                                        if(rs.st == 1){
                                            bootbox.alert(rs.ms,function () {
                                                window.location = window.location.href;
                                            })
                                        }else{
                                            bootbox.alert(rs.ms)
                                        }
                                    });
                                }
                            });
                        };
                    self.on('click','.remove-all',function () {
                        removeAll();
                    })

                }
            },
            create : {
                require: {
                    scripts: ['tags.js', 'photojs/jquery.form.js', 'photojs.js'],
                    stylesheets: ['photojs.css']
                },
                execute: function () {
                    var self = this;
                    self.find('.photojs').photoJs({
                        object: 'editor'
                    });

                    self.find('.icon-upload-alt').photoJs({
                        object: 'picture'
                    });

                    //keywords
                    self.find('.tags-js.tags-keywords').tags({
                        object_name: 'tags',
                        name: 'tags',
                        placeholder: 'Chọn từ khóa',
                        create: true
                    });
                }
            },
            edit : {
                require: {
                    scripts: ['tags.js', 'photojs/jquery.form.js', 'photojs.js'],
                    stylesheets: ['photojs.css']
                },
                execute: function () {
                    var self = this;

                    self.find('.photojs').photoJs({
                        object: 'editor'
                    });

                    self.find('.icon-upload-alt').photoJs({
                        object: 'picture'
                    });

                    //keywords
                    self.find('.tags-js.tags-keywords').tags({
                        object_name: 'tags',
                        name: 'tags',
                        placeholder: 'Chọn từ khóa',
                        create: true
                    });
                }
            }
        }
    }
});
