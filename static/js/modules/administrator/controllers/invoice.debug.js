/**
 * Created by chiennn on 12/07/2017.
 */

Controller.define('administrator/invoice', function () {
    // var xhr =null;
    return {
        model: {
            deleteInvoice: function (params) {
                return $.ajax({
                    type: 'post',
                    url: Registry.get('SITE_URL') + 'admin/invoice/delete',
                    data: params,
                    dataType : 'json'
                });
            },
            loadWarehouse: function (params) {
                return $.ajax({
                    type: 'GET',
                    url: Registry.get('SITE_URL') + 'admin/invoice/load-warehouse',
                    data: params,
                    dataType : 'json'
                });
            },
            formatNumber : function (nStr){
                nStr += '';
                x = nStr.split('.');
                x1 = x[0];
                x2 = x.length > 1 ? '.' + x[1] : '';
                var rgx = /(\d+)(\d{3})/;
                while (rgx.test(x1)) {
                    x1 = x1.replace(rgx, '$1' + ',' + '$2');
                }
                return x1 + x2;
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
                    scripts: ['bootstrap-select.js', 'bootstrap-inputmask.js', 'bootbox/bootbox.js'],
                    stylesheets: ['bootstrap-select.css']
                },
                execute: function () {
                    var self = this,
                        warehouses = Registry.get('WAREHOUSES'),
                        reload_select_picker = function () {
                            self.find('.select-picker').selectpicker({});
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
                            if(self.find('.total_price').length > 0){
                                $.each(self.find('.total_price'),function () {
                                    sum_total_price += +$(this).val();
                                })
                            }
                            self.find('.sum_total_price').text(self.model.formatNumber(sum_total_price));
                        },
                        reloadSTT = function () {
                            if(self.find('tbody tr').length > 0){
                                $.each(self.find('tbody tr'),function (k,item) {
                                    $(item).find('.stt').text(k+1);
                                });
                            }
                        };
                    var loadWarehouse = function () {
                        var warehouse_id_selected = [];
                        if(self.find('select.warehouse_id').length > 0){
                            self.find('select.warehouse_id').each(function (k,v) {
                                warehouse_id_selected.push(+$(this).val());
                            });
                        }
                        var html_select = '<select name="warehouse_id[]" class="form-control warehouse_id select-picker" data-live-search="true">';
                        var first_dvt = '';
                        var first_price = 0;
                        var flag = false;
                        $.each(warehouses.rows,function (k,item) {
                            if($.inArray(item.warehouse_id,warehouse_id_selected) == -1){
                                html_select += '<option value="'+item.warehouse_id+'" data-price="'+item.unit_price+'" data-properties="'+item.properties_name+'">';
                                html_select += item.product_name;
                                html_select += '</option>';
                            }

                            if(flag == false){
                                first_dvt = item.properties_name;
                                first_price = +item.unit_price;
                            }
                            flag = true;
                        });
                        var total_choose = self.find('table tbody tr').length + 1;
                        html_select += '</select>';
                        var html = '<tr>';
                        html += '<td class="text-center stt">'+total_choose+'</td>';
                        html += '<td class="text-center">'+html_select+'</td>';
                        html += '<td class="text-center">'+first_dvt+'</td>';
                        html += '<td><input class="form-control input"></td>';
                        html += '<td class="text-right"><input class="form-control input price-mask quantity" name="quantity[]"></td>';
                        html += '<td class="text-right"><input class="form-control input price-mask unit_price" name="price[]" readonly value="'+first_price+'"></td>';
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
                        html += '<a class="btn btn-xs btn-danger remove-item" data-toggle="tooltip" title="Xóa">';
                        html += '<i class="ace-icon fa fa-trash bigger-120"></i>';
                        html += '</a>';
                        html += '</td>';
                        self.find('tbody').append(html);
                        reload_element();
                    };
                    self.on('click','.add-product', loadWarehouse)
                        .on('click','.edit',function () {})
                        .on('keyup','.quantity, .discount',function () {
                            var quantity = +$(this).closest('tr').find('.quantity').val(),
                                unit_price = +$(this).closest('tr').find('.unit_price').val(),
                                discount = +$(this).closest('tr').find('.discount').val();
                            var price_discount = unit_price*quantity*discount/100,
                                amount_price = unit_price*quantity;
                            var total_price = amount_price-price_discount;
                            $(this).closest('tr').find('.total_price').val(total_price);
                            reload_sum_total_price();
                        }).on('click', '.remove-item', function () {
                            $(this).closest('tr').remove();
                            reloadSTT();
                            reload_sum_total_price();
                        }).on('change','select.warehouse_id',function () {
                            console.log($(this).data(''));
                        });
                    loadWarehouse();


                    // $('[data-toggle="tooltip"], [data-toggle="tooltip"] + .bootstrap-select > button').tooltip({
                    //     container: 'body'
                    // });
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