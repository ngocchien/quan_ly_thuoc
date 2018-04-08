/**
 * Created by chiennn on 07/07/2017.
 */


Controller.define('administrator/warehouse', function () {
    // var xhr =null;
    return {
        model: {
            deleteWarehouse: function (params) {
                return $.ajax({
                    type: 'post',
                    url: Registry.get('SITE_URL') + 'admin/warehouse/delete',
                    data: params,
                    dataType: 'json'
                });
            },
            deleteExpire: function (params) {
                return $.ajax({
                    type: 'post',
                    url: Registry.get('SITE_URL') + 'admin/warehouse/delete-expire',
                    data: params,
                    dataType: 'json'
                });
            },
            deleteExpired: function (params) {
                return $.ajax({
                    type: 'post',
                    url: Registry.get('SITE_URL') + 'admin/warehouse/delete-expired',
                    data: params,
                    dataType: 'json'
                });
            },
            calculatorTotalPrice: function (params) {
                var unit_price = params.unit_price,
                    discount = params.discount,
                    quantity = params.quantity;
                var price_discount = unit_price * quantity * discount / 100,
                    amount_price = unit_price * quantity;
                return amount_price - price_discount;
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
                            if (self.find('input[name=data-id]:checked').length <= 0) {
                                bootbox.alert('Vui lòng chọn sản phẩm muốn xóa!!!');
                                return false;
                            }
                            bootbox.confirm('Bạn có chắc chắn muốn xóa các thuốc đã nhập kho này không???', function (e) {
                                if (e) {
                                    var arr_id = [];
                                    self.find('input[name=data-id]:checked').each(function () {
                                        arr_id.push($(this).val())
                                    });
                                    self.model.deleteWarehouse({arr_warehouse_id: arr_id}).then(function (rs) {
                                        if (rs.st == 1) {
                                            bootbox.alert(rs.ms, function () {
                                                window.location = window.location.href;
                                            })
                                        } else {
                                            bootbox.alert(rs.ms)
                                        }
                                    });
                                }
                            });
                        };
                    self.on('click', '.remove-all', function () {
                        removeAll();
                    }).on('change', '.limit-query', function () {
                        window.location = $(this).val();
                    })

                }
            },
            create: {
                require: {
                    scripts: ['bootstrap-inputmask.js', 'bootstrap-datepicker.js', 'bootstrap-datepicker.vi.js', 'bootstrap-select.js'],
                    stylesheets: ['bootstrap-datepicker.css', 'bootstrap-select.css']
                },
                execute: function () {
                    var self = this;
                    var today = new Date();
                    var calculatorTotalPrice = function () {
                        var unit_price = +self.find('input[name=unit_price]').val(),
                            quantity = +self.find('input[name=quantity]').val(),
                            discount = +self.find('input[name=discount]').val();
                        var total_price = self.model.calculatorTotalPrice({
                            unit_price: unit_price,
                            quantity: quantity,
                            discount: discount
                        });
                        self.find('input[name=total_price]').val(total_price);
                    };
                    CKEDITOR.config.height = '100px';

                    self.find(".price-mask").inputmask({
                        alias: 'decimal',
                        radixPoint: '.',
                        groupSeparator: ',',
                        autoGroup: true,
                        rightAlign: true,
                        autoUnmask: true,
                        removeMaskOnSubmit: true,
                        digits: 0
                    });
                    self.find('.datetimepicker').inputmask({"mask": "99/99/9999"});
                    self.find('input[name=nsx]').datepicker({
                        format: 'dd/mm/yyyy',
                        endDate: "today",
                        maxDate: today
                    });
                    self.find('input[name=hsd]').datepicker({
                        format: 'dd/mm/yyyy',
                        startDate: "today",
                        minDate: today
                    });
                    self.find('.select-picker').selectpicker({
                        'style': 'btn-white'
                    });
                    self.on('keyup', 'input[name=unit_price],input[name=discount],input[name=quantity]', calculatorTotalPrice);
                }
            },
            edit: {
                require: {
                    scripts: ['bootstrap-inputmask.js', 'bootstrap-datepicker.js', 'bootstrap-datepicker.vi.js', 'bootstrap-select.js'],
                    stylesheets: ['bootstrap-datepicker.css', 'bootstrap-select.css']
                },
                execute: function () {
                    var self = this;
                    var today = new Date();
                    var calculatorTotalPrice = function () {
                        var unit_price = +self.find('input[name=unit_price]').val(),
                            quantity = +self.find('input[name=quantity]').val(),
                            discount = +self.find('input[name=discount]').val();
                        var total_price = self.model.calculatorTotalPrice({
                            unit_price: unit_price,
                            quantity: quantity,
                            discount: discount
                        });
                        self.find('input[name=total_price]').val(total_price);
                    };
                    self.find(".price-mask").inputmask({
                        alias: 'decimal',
                        radixPoint: '.',
                        groupSeparator: ',',
                        autoGroup: true,
                        rightAlign: true,
                        autoUnmask: true,
                        removeMaskOnSubmit: true,
                        digits: 0
                    });
                    self.find('.datetimepicker').inputmask({"mask": "99/99/9999"});
                    self.find('input[name=nsx]').datepicker({
                        format: 'dd/mm/yyyy',
                        endDate: "today",
                        maxDate: today
                    });
                    self.find('input[name=hsd]').datepicker({
                        format: 'dd/mm/yyyy',
                        startDate: "today",
                        minDate: today
                    });
                    self.find('.select-picker').selectpicker({
                        'style': 'btn-white'
                    });
                    self.on('keyup', 'input[name=unit_price],input[name=discount],input[name=quantity]', calculatorTotalPrice);
                }
            },
            expire: {
                require: {
                    scripts: ['bootbox/bootbox.js'],
                    stylesheets: []
                },
                execute: function () {
                    var self = this;
                    self.on('click', '.remove', function () {
                        var id = $(this).attr('rel');
                        if (!id) {
                            bootbox.alert('Xảy ra lỗi, vui lòng refresh trình duyệt và thử lại!!!');
                            return false;
                        }

                        bootbox.confirm('Bạn có muốn ngừng nhận thông báo sắp hết hạn sử dụng cho thuốc này???', function (e) {
                            if (e) {
                                self.model.deleteExpire({id: id}).then(function (rs) {
                                    if (rs.st == 1) {
                                        bootbox.alert(rs.ms, function () {
                                            window.location = window.location.href;
                                        })
                                    } else {
                                        bootbox.alert(rs.ms)
                                    }
                                });
                            }
                        });
                    }).on('click', '.delete_warehouse', function () {
                        var id = $(this).attr('rel');
                        if (!id) {
                            bootbox.alert('Xảy ra lỗi, vui lòng refresh trình duyệt và thử lại!!!');
                            return false;
                        }

                        bootbox.confirm('Bạn có muốn xóa thuốc sắp hết hạn này khỏi kho không???', function (e) {
                            if (e) {
                                self.model.deleteExpired({id: id}).then(function (rs) {
                                    if (rs.st == 1) {
                                        bootbox.alert(rs.ms, function () {
                                            window.location = window.location.href;
                                        })
                                    } else {
                                        bootbox.alert(rs.ms)
                                    }
                                });
                            }
                        });
                    })
                }
            },
            expired: {
                require: {
                    scripts: ['bootbox/bootbox.js'],
                    stylesheets: []
                },
                execute: function () {
                    var self = this;
                    self.on('click', '.remove', function () {
                        var id = $(this).attr('rel');
                        if (!id) {
                            bootbox.alert('Xảy ra lỗi, vui lòng refresh trình duyệt và thử lại!!!');
                            return false;
                        }

                        bootbox.confirm('Bạn có muốn xóa thuốc đã hết hạn này khỏi kho không???', function (e) {
                            if (e) {
                                self.model.deleteExpired({id: id}).then(function (rs) {
                                    if (rs.st == 1) {
                                        bootbox.alert(rs.ms, function () {
                                            window.location = window.location.href;
                                        })
                                    } else {
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