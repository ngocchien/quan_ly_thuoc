/**
 * Created by chiennn on 12/07/2017.
 */

Controller.define('administrator/invoice', function () {
    // var xhr =null;
    var INVOICE_ID = +Registry.get('INVOICE_ID');
    return {
        model: {
            deleteInvoice: function (params) {
                return $.ajax({
                    type: 'post',
                    url: Registry.get('SITE_URL') + 'admin/invoice/delete',
                    data: params,
                    dataType: 'json'
                });
            },
            loadWarehouse: function (params) {
                return $.ajax({
                    type: 'GET',
                    url: Registry.get('SITE_URL') + 'admin/invoice/load-warehouse',
                    data: params,
                    dataType: 'json'
                });
            },
            formatNumber: function (nStr) {
                nStr += '';
                x = nStr.split('.');
                x1 = x[0];
                x2 = x.length > 1 ? '.' + x[1] : '';
                var rgx = /(\d+)(\d{3})/;
                while (rgx.test(x1)) {
                    x1 = x1.replace(rgx, '$1' + ',' + '$2');
                }
                return x1 + x2;
            },
            loadCustomer: function (params) {
                return $.ajax({
                    type: 'GET',
                    url: Registry.get('SITE_URL') + 'admin/invoice/load-customer',
                    data: params,
                    dataType: 'json'
                });
            },
            addCustomer: function (params) {
                return $.ajax({
                    type: 'POST',
                    url: Registry.get('SITE_URL') + 'admin/customer/add-customer',
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
                    var self = this,
                        removeAll = function () {
                            if (self.find('input[name=data-id]:checked').length <= 0) {
                                bootbox.alert('Vui lòng chọn sản phẩm muốn xóa!!!');
                                return false;
                            }
                            bootbox.confirm('Bạn có chắc chắn muốn xóa các sản phẩm này không???', function (e) {
                                if (e) {
                                    var arr_id = [];
                                    self.find('input[name=data-id]:checked').each(function () {
                                        arr_id.push($(this).val())
                                    });
                                    self.model.deleteProduct({arr_product_id: arr_id}).then(function (rs) {
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
                    }).on('click', '.view-info-customer', function () {
                        var customer_info = $(this).data('customer');
                        var html ='<div class="form-group">' +
                            '<label for="recipient-name" class="control-label">Tên:</label>'+
                            '<input type="text" class="form-control" value="'+customer_info.full_name+'" '+
                            '</div>'+
                            '<div class="form-group">'+
                            '<label for="message-text" class="control-label">Số điện thoại:</label>'+
                            '<input type="text" class="form-control" value="'+customer_info.phone+'" '+
                            '</div>'+
                            '<div class="form-group">'+
                            '<label for="message-text" class="control-label">Địa chỉ:</label>'+
                            '<input type="text" class="form-control" value="'+customer_info.address+'" '+
                            '</div>'+
                            '<div class="form-group">'+
                            '<label for="message-text" class="control-label">Ghi chú:</label>' +
                            '<textarea class="form-control" name="customer_note"></textarea>' +
                            '</div>';
                        var dialog = bootbox.dialog({
                            title: 'Thông tin khách hàng',
                            message : html,
                            buttons: {
                                ok: {
                                    label: "OK",
                                    className: 'btn-success'
                                }
                            }
                        });
                    });
                }
            },
            create: {
                require: {
                    scripts: ['bootstrap-select.js', 'bootstrap-inputmask.js', 'bootbox/bootbox.js'],
                    stylesheets: ['bootstrap-select.css']
                },
                execute: function () {
                    var self = this,
                        previous = {},
                        warehouses = Registry.get('WAREHOUSES'),
                        reload_select_picker = function () {
                            self.find('.select-picker').selectpicker('destroy');
                            self.find('.select-picker').selectpicker();
                        },
                        reload_element = function () {
                            self.find('.select-picker').selectpicker({});
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
                            self.find('[data-toggle="tooltip"]').tooltip();
                        },
                        reload_sum_total_price = function () {
                            var sum_total_price = 0;
                            if (self.find('.total_price').length > 0) {
                                $.each(self.find('.total_price'), function () {
                                    sum_total_price += +$(this).val();
                                })
                            }
                            self.find('.sum_total_price').text(self.model.formatNumber(sum_total_price));
                            self.find('input[name=sum_total_price]').val(sum_total_price);
                        },
                        reloadSTT = function () {
                            if (self.find('tbody tr').length > 0) {
                                $.each(self.find('tbody tr'), function (k, item) {
                                    $(item).find('.stt').text(k + 1);
                                });
                            }
                        },
                        loadWarehouse = function () {
                            var warehouse_id_selected = [];
                            if (self.find('select.warehouse_id').length > 0) {
                                self.find('select.warehouse_id').each(function (k, v) {
                                    warehouse_id_selected.push(+$(this).val());
                                });
                            }
                            var html_select = '<select name="warehouse_id[]" class="form-control warehouse_id select-picker" data-live-search="true">';
                            var first_dvt = '';
                            var first_price = 0;
                            var flag = false;
                            var current_val = 0;
                            $.each(warehouses.rows, function (k, item) {
                                if ($.inArray(item.warehouse_id, warehouse_id_selected) != -1) {
                                    return;
                                }
                                var name_show = item.product_name + ' - Số Lô :' + item.production_batch + ' - Hãng : ' + item.brand_name + ' - HSD : ' + item.hsd + ' - Tồn :' + item.stock;
                                html_select += '<option value="' + item.warehouse_id + '" data-price="' + item.unit_price + '" data-properties="' + item.properties_name + '">';
                                html_select += name_show;
                                html_select += '</option>';

                                if (flag == false) {
                                    first_dvt = item.properties_name;
                                    first_price = +item.unit_price;
                                    current_val = item.warehouse_id;
                                }
                                flag = true;
                            });
                            var total_choose = self.find('table tbody tr').length + 1;
                            html_select += '</select>';
                            var html = '<tr>';
                            html += '<td class="text-center stt">' + total_choose + '</td>';
                            html += '<td class="text-center">' + html_select + '</td>';
                            html += '<td class="text-center">' + first_dvt + '</td>';
                            // html += '<td><textarea width="100%" cols="30" name="note[]"></textarea></td>';
                            html += '<td class="text-right">' +
                                '<input class="form-control input price-mask quantity" name="quantity[]">' +
                                '<span class="error error-no-choose-quantity red" style="display: none">Nhập SL</span>' +
                                '<span class="error error-quantity-not-enough red" style="display: none">Tồn không đủ</span>' +
                                '</td>';
                            html += '<td class="text-right"><input class="form-control input price-mask unit_price" name="price[]" readonly value="' + first_price + '"></td>';
                            html += '<td class="text-right"><input class="form-control input price-mask discount" name="discount[]"></td>';
                            html += '<td class="text-right"><input class="form-control input price-mask total_price" name="total_price[]" readonly value="0"></td>';
                            html += '<td class="text-center">';
                            html += '<div class="hidden-sm hidden-xs btn-group">';
                            html += '<a class="btn btn-xs btn-primary edit" data-toggle="tooltip"  style="display: none" title="Sửa">';
                            html += '<i class="ace-icon fa fa-pencil bigger-120"></i>';
                            html += '</a>';
                            html += '<a class="btn btn-xs btn-success save" data-toggle="tooltip" style="display: none" title="Xác nhận">';
                            html += '<i class="ace-icon fa fa-check bigger-120"></i>';
                            html += '</a>';
                            html += '<a class="btn btn-xs btn-danger remove-item" title="Xóa">';
                            html += '<i class="ace-icon fa fa-trash bigger-120"></i>';
                            html += '</a>';
                            html += '</td>';
                            self.find('tbody').append(html);
                            previous = {};
                            reloadSelect(total_choose, current_val)
                            //reload_element();
                        },
                        addFormRegister = function () {
                            self.find('.error-customer-name').text('');
                            var customer_name = self.find('.search-customer').val();
                            self.find('input[name=full_name]').val(customer_name);
                            self.find('#modal-register-customer').modal('show');
                        },
                        registerCustomer = function () {
                            self.find('.error-customer-name').text('');
                            var full_name = self.find('input[name=full_name]').val(),
                                customer_phone = self.find('input[name=customer_phone]').val(),
                                customer_address = self.find('input[name=customer_address]').val(),
                                customer_note = self.find('input[name=customer_note]').val();
                            if (!full_name) {
                                self.find('.error-customer-name').text('Tên khách hàng không được bỏ trống!!!');
                                return false;
                            }
                            var params = {
                                full_name: full_name,
                                customer_phone: customer_phone,
                                customer_address: customer_address,
                                customer_note: customer_note
                            };
                            self.model.addCustomer(params).then(function (rs) {
                                if (rs.st == 1) {
                                    self.find('input[name=customer_id]').val(rs.data.customer_id);
                                    self.find('.info-full-name').text(full_name);
                                    self.find('.info-phone').text(customer_phone);
                                    self.find('.info-address').text(customer_address);
                                    self.find('.input-register-customer').hide();
                                    self.find('.info-customer-invoice').show();
                                    self.find('#modal-register-customer').modal('hide');
                                    bootbox.alert(rs.ms);
                                } else {
                                    bootbox.alert(rs.ms);
                                }
                            });
                        },
                        changeAnotherCustomer = function () {
                            self.find('input[name=customer_id]').val();
                            self.find('.info-customer-invoice').hide();
                            self.find('.input-register-customer').show();
                        },
                        cancelChangeAnotherCustomer = function () {
                            self.find('input[name=customer_id]').val();
                            self.find('.input-register-customer').hide();
                            self.find('.info-customer-invoice').show();
                        },
                        loadCustomer = function () {
                            var dataCustomers = [];
                            var search = self.find('#tags').val();
                            self.model.loadCustomer({search: search}).then(function (rs) {
                                if (rs.st == -1) {
                                    return dataCustomers;
                                }
                                $.each(rs.data.rows, function (k, v) {
                                    var value = v.full_name,
                                        label = v.full_name;
                                    if (v.phone) {
                                        label += ' - '.v.phone;
                                    }
                                    if (v.phone) {
                                        label += ' - '.v.address;
                                    }
                                    dataCustomers.push({
                                        'value': value,
                                        'label': label,
                                        'customer_id': v.customer_id,
                                        'phone': v.phone,
                                        'address': v.address,
                                        'note': v.note,
                                        'full_name': v.full_name
                                    });
                                });
                            });
                            return dataCustomers;
                        },
                        validateForm = function () {
                            self.find('.error').hide();
                            var valid = true;
                            if (!self.find('input[name=customer_id]').val()) {
                                self.find('.error-no-choose-customer').show();
                                valid = false;
                            }
                            if (self.find('tbody tr').length < 1) {
                                self.find('.error-no-choose-warehouse').show();
                                valid = false;
                            } else {
                                $.each(self.find('.quantity'), function () {
                                    if (!$(this).val()) {
                                        valid = false;
                                        $(this).closest('td').find('.error-no-choose-quantity').show();
                                    }
                                })
                            }
                            return valid;
                        },
                        reloadSelect = function (stt, val) {
                            $.each(self.find('select.warehouse_id'), function (k, item) {
                                if (stt != 0 && k == stt - 1) {
                                    return;
                                }
                                $(this).find('option[value=' + val + ']').remove();
                                if (!$.isEmptyObject(previous)) {
                                    var option = '<option value=' + previous.warehouse_id + ' data-price=' + previous.price + ' data-properties=' + previous.properties_name + '>';
                                    option += previous.name_show;
                                    option += '</option>';
                                    $(this).append(option);
                                }
                            });
                            previous = {};
                            reload_select_picker();
                            reload_element();
                        };
                    self.on('click', '.add-product', loadWarehouse)
                        .on('click', '.edit', function () {
                        })
                        .on('keyup', '.quantity, .discount', function () {
                            //validate quantity
                            var warehouse = Registry.get('WAREHOUSES');
                            var warehouse_id = $(this).closest('tr').find('select.warehouse_id').val();

                            var quantity_old_choose = +$(this).closest('tr').find('.old_quantity').val();
                            if(isNaN(quantity_old_choose)){
                                quantity_old_choose = 0;
                            }
                            var quantity = +$(this).closest('tr').find('.quantity').val(),
                                flag = false;

                            if(quantity){
                                $(this).closest('tr').find('.error-no-choose-quantity').hide();
                            }

                            $.each(warehouse.rows, function (k,v) {
                                if(v.warehouse_id == warehouse_id && (v.stock+quantity_old_choose) >= quantity ){
                                    flag = true;
                                }
                            });

                            if(!flag){
                                $(this).closest('tr').find('.error-quantity-not-enough').show();
                            }else{
                                $(this).closest('tr').find('.error-quantity-not-enough').hide();
                            }

                            var unit_price = +$(this).closest('tr').find('.unit_price').val(),
                                discount = +$(this).closest('tr').find('.discount').val();
                            var price_discount = unit_price * quantity * discount / 100,
                                amount_price = unit_price * quantity;
                            var total_price = amount_price - price_discount;
                            $(this).closest('tr').find('.total_price').val(total_price);
                            reload_sum_total_price();
                        })
                        .on('click', '.remove-item', function () {
                            var current_option = $(this).closest('tr').find('option:selected');
                            previous.price = $(current_option).data('price');
                            previous.warehouse_id = $(current_option).val();
                            previous.properties_name = $(current_option).data('properties');
                            previous.name_show = $(current_option).text();
                            $(this).closest('tr').remove();
                            reloadSTT();
                            reloadSelect(0, 0);
                            reload_sum_total_price();
                        })
                        .on('change', 'select.warehouse_id', function () {
                            var stt = +$(this).closest('tr').find('.stt').text(),
                                val = $(this).val();
                            var quantity = +$(this).closest('tr').find('.quantity').val(),
                                unit_price = +$(this).find('option:selected').data('price'),
                                discount = +$(this).closest('tr').find('.discount').val();
                            var price_discount = unit_price * quantity * discount / 100,
                                amount_price = unit_price * quantity;
                            var total_price = amount_price - price_discount;
                            $(this).closest('tr').find('.unit_price').val(unit_price);
                            $(this).closest('tr').find('.total_price').val(total_price);
                            reload_sum_total_price();
                            reloadSelect(stt, val);
                        })
                        .on('click', '.add-customer', addFormRegister)
                        .on('click', '#register-customer', registerCustomer)
                        .on('click', '.change-another-customer', changeAnotherCustomer)
                        .on('keyup', '#tags', function () {
                            var value = $(this).val();
                            if (value.length >= 0) {
                                $(this).autocomplete({
                                    source: loadCustomer(),
                                    select: function (k, v) {
                                        self.find('input[name=customer_id]').val(v.item.customer_id);
                                        self.find('.info-full-name').text(v.item.full_name);
                                        self.find('.info-phone').text(v.item.phone);
                                        self.find('.info-address').text(v.item.address);
                                        self.find('.input-register-customer').hide();
                                        self.find('.info-customer-invoice').show();
                                    },
                                    minLength: 0
                                });
                            }
                            $(this).focus(function () {
                                $(this).autocomplete("search", "");
                            });
                        })
                        .on('click', 'button[type=submit]', validateForm)
                        .on('show.bs.select', 'select.warehouse_id', function () {
                            var current_option = $(this).find('option:selected');
                            previous.price = $(current_option).data('price');
                            previous.warehouse_id = $(current_option).val();
                            previous.properties_name = $(current_option).data('properties');
                            previous.name_show = $(current_option).text();
                        }).on('click','.cancel-choose-customer', cancelChangeAnotherCustomer);
                    if (self.find('tbody tr').length < 1) {
                        loadWarehouse();
                    } else {
                        reload_element();
                    }
                }
            },
            edit: {
                require: {
                    scripts: ['bootstrap-datepicker.js', 'bootstrap-select.js', 'bootstrap-inputmask.js'],
                    stylesheets: ['datepicker.css', 'bootstrap-select.css']
                },
                execute: function () {
                    var self = this,
                        previous = {},
                        warehouses = Registry.get('WAREHOUSES'),
                        reload_select_picker = function () {
                            self.find('.select-picker').selectpicker('destroy');
                            self.find('.select-picker').selectpicker();
                        },
                        reload_element = function () {
                            self.find('.select-picker').selectpicker({});
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
                            self.find('[data-toggle="tooltip"]').tooltip();
                        },
                        reload_sum_total_price = function () {
                            var sum_total_price = 0;
                            if (self.find('.total_price').length > 0) {
                                $.each(self.find('.total_price'), function () {
                                    sum_total_price += +$(this).val();
                                })
                            }
                            self.find('.sum_total_price').text(self.model.formatNumber(sum_total_price));
                            self.find('input[name=sum_total_price]').val(sum_total_price);
                        },
                        reloadSTT = function () {
                            if (self.find('tbody tr').length > 0) {
                                $.each(self.find('tbody tr'), function (k, item) {
                                    $(item).find('.stt').text(k + 1);
                                });
                            }
                        },
                        loadWarehouse = function () {
                            var warehouse_id_selected = [];
                            if (self.find('select.warehouse_id').length > 0) {
                                self.find('select.warehouse_id').each(function (k, v) {
                                    warehouse_id_selected.push(+$(this).val());
                                });
                            }
                            var html_select = '<select name="warehouse_id[]" class="form-control warehouse_id select-picker" data-live-search="true">';
                            var first_dvt = '';
                            var first_price = 0;
                            var flag = false;
                            var current_val = 0;
                            $.each(warehouses.rows, function (k, item) {
                                if ($.inArray(item.warehouse_id, warehouse_id_selected) != -1) {
                                    return;
                                }
                                var name_show = item.product_name + ' - Số Lô :' + item.production_batch + ' - HSD : ' + item.hsd + ' - Tồn :' + item.stock;
                                html_select += '<option value="' + item.warehouse_id + '" data-price="' + item.unit_price + '" data-properties="' + item.properties_name + '">';
                                html_select += name_show;
                                html_select += '</option>';

                                if (flag == false) {
                                    first_dvt = item.properties_name;
                                    first_price = +item.unit_price;
                                    current_val = item.warehouse_id;
                                }
                                flag = true;
                            });
                            var total_choose = self.find('table tbody tr').length + 1;
                            html_select += '</select>';
                            var html = '<tr>';
                            html += '<td class="text-center stt">' + total_choose + '</td>';
                            html += '<td class="text-center">' + html_select + '</td>';
                            html += '<td class="text-center">' + first_dvt + '</td>';
                            // html += '<td><textarea width="100%" cols="30" name="note[]"></textarea></td>';
                            html += '<td class="text-right">' +
                                '<input class="form-control input price-mask quantity" name="quantity[]">' +
                                '<span class="error error-no-choose-quantity red" style="display: none">Nhập SL</span>' +
                                '</td>';
                            html += '<td class="text-right"><input class="form-control input price-mask unit_price" name="price[]" readonly value="' + first_price + '"></td>';
                            html += '<td class="text-right"><input class="form-control input price-mask discount" name="discount[]"></td>';
                            html += '<td class="text-right"><input class="form-control input price-mask total_price" name="total_price[]" readonly value="0"></td>';
                            html += '<td class="text-center">';
                            html += '<div class="hidden-sm hidden-xs btn-group">';
                            html += '<a class="btn btn-xs btn-primary edit" data-toggle="tooltip"  style="display: none" title="Sửa">';
                            html += '<i class="ace-icon fa fa-pencil bigger-120"></i>';
                            html += '</a>';
                            html += '<a class="btn btn-xs btn-success save" data-toggle="tooltip" style="display: none" title="Xác nhận">';
                            html += '<i class="ace-icon fa fa-check bigger-120"></i>';
                            html += '</a>';
                            html += '<a class="btn btn-xs btn-danger remove-item" title="Xóa">';
                            html += '<i class="ace-icon fa fa-trash bigger-120"></i>';
                            html += '</a>';
                            html += '</td>';
                            self.find('tbody').append(html);
                            previous = {};
                            reloadSelect(total_choose, current_val)
                            //reload_element();
                        },
                        addFormRegister = function () {
                            self.find('.error-customer-name').text('');
                            var customer_name = self.find('.search-customer').val();
                            self.find('input[name=full_name]').val(customer_name);
                            self.find('#modal-register-customer').modal('show');
                        },
                        registerCustomer = function () {
                            self.find('.error-customer-name').text('');
                            var full_name = self.find('input[name=full_name]').val(),
                                customer_phone = self.find('input[name=customer_phone]').val(),
                                customer_address = self.find('input[name=customer_address]').val(),
                                customer_note = self.find('input[name=customer_note]').val();
                            if (!full_name) {
                                self.find('.error-customer-name').text('Tên khách hàng không được bỏ trống!!!');
                                return false;
                            }
                            var params = {
                                full_name: full_name,
                                customer_phone: customer_phone,
                                customer_address: customer_address,
                                customer_note: customer_note
                            };
                            self.model.addCustomer(params).then(function (rs) {
                                if (rs.st == 1) {
                                    self.find('input[name=customer_id]').val(rs.data.customer_id);
                                    self.find('.info-full-name').text(full_name);
                                    self.find('.info-phone').text(customer_phone);
                                    self.find('.info-address').text(customer_address);
                                    self.find('.input-register-customer').hide();
                                    self.find('.info-customer-invoice').show();
                                    self.find('#modal-register-customer').modal('hide');
                                    bootbox.alert(rs.ms);
                                } else {
                                    bootbox.alert(rs.ms);
                                }
                            });
                        },
                        changeAnotherCustomer = function () {
                            self.find('input[name=customer_id]').val();
                            self.find('.info-customer-invoice').hide();
                            self.find('.input-register-customer').show();
                        },
                        loadCustomer = function () {
                            var dataCustomers = [];
                            var search = self.find('#tags').val();
                            self.model.loadCustomer({search: search}).then(function (rs) {
                                if (rs.st == -1) {
                                    return dataCustomers;
                                }
                                $.each(rs.data.rows, function (k, v) {
                                    var value = v.full_name,
                                        label = v.full_name;
                                    if (v.phone) {
                                        label += ' - '.v.phone;
                                    }
                                    if (v.phone) {
                                        label += ' - '.v.address;
                                    }
                                    dataCustomers.push({
                                        'value': value,
                                        'label': label,
                                        'customer_id': v.customer_id,
                                        'phone': v.phone,
                                        'address': v.address,
                                        'note': v.note,
                                        'full_name': v.full_name
                                    });
                                });
                            });
                            return dataCustomers;
                        },
                        validateForm = function () {
                            self.find('.error').hide();
                            var valid = true;
                            if (!self.find('input[name=customer_id]').val()) {
                                self.find('.error-no-choose-customer').show();
                                valid = false;
                            }
                            if (self.find('tbody tr').length < 1) {
                                self.find('.error-no-choose-warehouse').show();
                                valid = false;
                            } else {
                                $.each(self.find('.quantity'), function () {
                                    if (!$(this).val()) {
                                        valid = false;
                                        $(this).closest('td').find('.error-no-choose-quantity').show();
                                    }
                                })
                            }
                            return valid;
                        },
                        reloadSelect = function (stt, val) {
                            $.each(self.find('select.warehouse_id'), function (k, item) {
                                if (stt != 0 && k == stt - 1) {
                                    return;
                                }
                                $(this).find('option[value=' + val + ']').remove();
                                if (!$.isEmptyObject(previous)) {
                                    var option = '<option value=' + previous.warehouse_id + ' data-price=' + previous.price + ' data-properties=' + previous.properties_name + '>';
                                    option += previous.name_show;
                                    option += '</option>';
                                    $(this).append(option);
                                }
                            });
                            previous = {};
                            reload_select_picker();
                            reload_element();
                        };
                    self.on('keyup', '.quantity, .discount', function () {
                        //validate quantity
                        var quantity = +$(this).closest('tr').find('.quantity').val(),
                            unit_price = +$(this).closest('tr').find('.unit_price').val(),
                            discount = +$(this).closest('tr').find('.discount').val();
                        var price_discount = unit_price * quantity * discount / 100,
                            amount_price = unit_price * quantity;
                        var total_price = amount_price - price_discount;
                        $(this).closest('tr').find('.total_price').val(total_price);
                        reload_sum_total_price();
                    }).on('change', 'select.warehouse_id', function () {
                            var stt = +$(this).closest('tr').find('.stt').text(),
                                val = $(this).val();
                            var quantity = +$(this).closest('tr').find('.quantity').val(),
                                unit_price = +$(this).find('option:selected').data('price'),
                                discount = +$(this).closest('tr').find('.discount').val();
                            var price_discount = unit_price * quantity * discount / 100,
                                amount_price = unit_price * quantity;
                            var total_price = amount_price - price_discount;
                            $(this).closest('tr').find('.unit_price').val(unit_price);
                            $(this).closest('tr').find('.total_price').val(total_price);
                            reload_sum_total_price();
                            reloadSelect(stt, val);
                    }).on('click','.cancel-customer', function () {
                        console.log(111111);
                    });
                    if (self.find('tbody tr').length < 1) {
                        loadWarehouse();
                    } else {
                        reload_element();
                    }
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
                            if (self.find('input[name=data-id]:checked').length <= 0) {
                                bootbox.alert('Vui lòng chọn sản phẩm muốn xóa!!!');
                                return false;
                            }
                            bootbox.confirm('Bạn có chắc chắn muốn xóa các sản phẩm này không???', function (e) {
                                if (e) {
                                    var arr_id = [];
                                    self.find('input[name=data-id]:checked').each(function () {
                                        arr_id.push($(this).val())
                                    });
                                    self.model.deleteProduct({arr_product_id: arr_id}).then(function (rs) {
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
                    })

                }
            }
        }
    }
});