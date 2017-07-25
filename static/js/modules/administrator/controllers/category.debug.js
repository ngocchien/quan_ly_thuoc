/**
 * Created by chiennn on 18/06/2017.
 */

Controller.define('administrator/category', function () {
    // var xhr =null;
    return {
        model: {
            delete: function (params) {
                return $.ajax({
                    type: 'post',
                    url: Registry.get('SITE_URL') + 'admin/category/delete',
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
                    var self = this;
                    self.on('click','.delete',function () {
                        var id = $(this).attr('rel');
                        if (!id) {
                            bootbox.alert('Xảy ra lỗi trong quá trình xử lý! Vui lòng refresh lại trình duyệt và thử lại!');
                            return false;
                        }
                        bootbox.confirm('Bạn có chắc chắn muốn xóa danh mục này không ????', function (e) {
                            if (e) {
                                self.model.delete({cate_id : id}).then(function (rs) {
                                    if(rs.st == 1){
                                        bootbox.alert(rs.ms,function () {
                                            window.location = window.location.href;
                                        })
                                    }else{
                                        bootbox.alert(rs.ms)
                                    }
                                });
                            }
                        })
                    }).on('change','.limit-query',function () {
                        window.location = $(this).val();
                    })

                }
            },
            create : {
                require: {
                    scripts: ['bootstrap-select.js'],
                    stylesheets: ['bootstrap-select.css']
                },
                execute: function () {
                    var self = this;
                    self.find('.select-picker').selectpicker();
                }
            },
            edit : {
                require: {
                    scripts: ['bootstrap-select.js'],
                    stylesheets: ['bootstrap-select.css']
                },
                execute: function () {
                    var self = this;
                    self.find('.select-picker').selectpicker();
                }
            }
        }
    }
});

