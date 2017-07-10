/**
 * Created by tuandv on 8/2/16.
 */

Controller.define('administrator/group', function () {
    var xhr = null;
    return {
        model: {
            delete: function (params) {
                return $.ajax({
                    type: 'post',
                    url: Registry.get('SITE_URL') + 'admin/group/delete',
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
                        var ms = 'Bạn có chắc chắn muốn xóa nhóm này không ? <br>' +
                            'Xóa nhóm thì tất cả <b class="red">Người dùng</b> thuộc nhóm này sẽ bị xóa theo!! <br>' +
                            'Bạn chắc chắn về việc này chứ ???';
                        bootbox.confirm(ms, function (e) {
                            if (e) {
                                self.model.delete({group_id: id}).then(function (rs) {
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
                    });
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