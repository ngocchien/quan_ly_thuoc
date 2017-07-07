/**
 * Created by chiennn on 10/06/2017.
 */

Controller.define('administrator/banner', function () {
    var xhr = null;
    return {
        model: {
            delete: function (params) {
                return $.ajax({
                    type: 'post',
                    url: Registry.get('SITE_URL') + 'administrator/banner/delete',
                    data: params,
                    dataType: 'json'
                });
            }
        },
        actions: {
            index: {
                require: {
                    scripts: ['lightbox2-master/src/js/lightbox.js', 'bootbox/bootbox.js'],
                    stylesheets: ['lightbox2-master/src/css/lightbox.css']
                },
                execute: function () {
                    var self = this;
                    self.on('click', '.delete', function () {
                        var id = $(this).attr('rel');
                        if (!id) {
                            bootbox.alert('Xảy ra lỗi trong quá trình xử lý! Vui lòng refresh lại trình duyệt và thử lại!');
                            return false;
                        }
                        bootbox.confirm('Bạn có chắc chắn muốn xóa banner này không ????', function (e) {
                            if (e) {
                                self.model.delete({banner_id: id}).then(function (rs) {
                                    if (rs.st == 1) {
                                        bootbox.alert(rs.ms, function () {
                                            window.location = window.location.href;
                                        })
                                    } else {
                                        bootbox.alert(rs.ms)
                                    }
                                });
                            }
                        })
                    })

                }
            },
            create: {
                require: {
                    scripts: ['photojs/jquery.form.js', 'photojs.js'],
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
                }
            },
            edit: {
                require: {
                    scripts: ['photojs/jquery.form.js', 'photojs.js'],
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
                }
            }
        }
    }
});
