/**
 * Created by chiennn on 12/07/2017.
 */

Controller.define('administrator/invoice', function () {
    // var xhr =null;
    return {
        model: {
            delete: function (params) {
                return $.ajax({
                    type: 'post',
                    url: Registry.get('SITE_URL') + 'admin/invoice/delete',
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
                                bootbox.alert('Vui lòng chọn sản phẩm muốn xóa!!!');
                                return false;
                            }
                            bootbox.confirm('Bạn có chắc chắn muốn xóa các sản phẩm này không???', function (e) {
                                if(e){
                                    var arr_id = [];
                                    self.find('input[name=data-id]:checked').each(function () {
                                        arr_id.push($(this).val())
                                    });
                                    self.model.deleteProduct({arr_product_id : arr_id}).then(function (rs){
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
                    scripts: ['bootstrap-datepicker.js', 'bootstrap-select.js', 'jquery.sumoselect.min.js'],
                    stylesheets: ['datepicker.css', 'bootstrap-select.css', 'sumoselect.css']
                },
                execute: function () {
                    var self = this;
                    // self.find('.datetimepicker').datepicker({
                    //     format: 'dd/mm/yyyy'
                    // });
                    self.find('.select-picker').SumoSelect({
                        'search' : true,
                        'floatWidth' : '400px'
                        // 'showContent' : true,
                        // 'showSubtext' : true,
                        // 'container' : true
                    });
                    $('[data-toggle="tooltip"]').tooltip();

                    $('[data-toggle="tooltip"], [data-toggle="tooltip"] + .bootstrap-select > button').tooltip({
                        container: 'body'
                    });

                    // $('body').tooltip({
                    //     selector: '.select-picker',
                    //     container: 'body',
                    //     placement: 'bottom'
                    // });

                    // $('body').tooltip({
                    //     selector: '.select-picker',
                    //     container: 'body',
                    //     placement: 'bottom'
                    // });

                    self.on('change','.stock-warehouse', function () {
                        var stock = $(this).find(':selected').data('stock');
                        $(this).closest('.choose-warehouse').find('.choose-quantity').show();
                    });
                }
            },
            edit : {
                require: {
                    scripts: ['bootstrap-datepicker.js', 'bootstrap-select.js'],
                    stylesheets: ['datepicker.css', 'bootstrap-select.css']
                },
                execute: function () {
                    var self = this;
                    self.find('.datetimepicker').datepicker({
                        format: 'dd/mm/yyyy'
                    });
                    self.find('.select-picker').selectpicker();
                }
            },
            expire: {
                require: {
                    scripts: ['bootbox/bootbox.js'],
                    stylesheets: []
                },
                execute: function () {
                    var self = this,
                        removeNotify = function () {
                            if(self.find('input[name=data-id]:checked').length <= 0){
                                bootbox.alert('Vui lòng chọn sản phẩm muốn xóa!!!');
                                return false;
                            }
                            bootbox.confirm('Bạn có chắc chắn muốn xóa các sản phẩm này không???', function (e) {
                                if(e){
                                    var arr_id = [];
                                    self.find('input[name=data-id]:checked').each(function () {
                                        arr_id.push($(this).val())
                                    });
                                    self.model.deleteProduct({arr_product_id : arr_id}).then(function (rs){
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
                    self.on('click','.remove',function () {
                        var id = $(this).attr('rel');
                        if(!id){
                            bootbox.alert('Xảy ra lỗi, vui lòng refresh trình duyệt và thử lại!!!');
                            return false;
                        }

                        bootbox.confirm('Bạn có muốn ngừng nhận thông báo sắp hết hạn sử dụng cho thuốc này???', function (e) {
                            if(e){
                                self.model.deleteExpire({id : id}).then(function (rs){
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
                    })

                }
            }
        }
    }
});