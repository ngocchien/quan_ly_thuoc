/**
 * Created by chiennn on 27/06/2017.
 */

Controller.define('administrator/properties', function () {
    var xhr = null;
    return {
        model: {
            delete: function (params) {
                return $.ajax({
                    type: 'post',
                    url: Registry.get('SITE_URL') + 'admin/properties/delete',
                    data: params,
                    dataType: 'json'
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
                    var self = this;
                    self.on('click', '.delete', function () {
                        var id = $(this).attr('rel');
                        if (!id) {
                            bootbox.alert('Xảy ra lỗi trong quá trình xử lý! Vui lòng refresh lại trình duyệt và thử lại!');
                            return false;
                        }
                        bootbox.confirm('Bạn có chắc chắn muốn xóa nhãn hiệu này không ????', function (e) {
                            if (e) {
                                self.model.delete({id: id}).then(function (rs) {
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
                    scripts: [],
                    stylesheets: []
                },
                execute: function () {
                }
            },
            edit: {
                require: {
                    scripts: [],
                    stylesheets: []
                },
                execute: function () {
                }
            }
        }
    }
});