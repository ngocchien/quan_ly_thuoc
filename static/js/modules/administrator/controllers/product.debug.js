/**
 * Created by chiennn on 10/06/2017.
 */

Controller.define('administrator/product', function () {
    // var xhr =null;
    return {
        model: {
            deleteProduct: function (params) {
                return $.ajax({
                    type: 'post',
                    url: Registry.get('SITE_URL') + 'admin/product/delete',
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
                    scripts: ['bootstrap-inputmask.js','bootstrap-select.js'],
                    stylesheets: ['bootstrap-select.css']
                },
                execute: function () {
                    var self = this;
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

                    // self.find('.photojs').photoJs({
                    //     object: 'editor'
                    // });
                    //
                    // self.find('.icon-upload-alt').photoJs({
                    //     object: 'multiple'
                    // });

                    //keywords
                    // self.find('.tags-js.tags-keywords').tags({
                    //     object_name: 'tags',
                    //     name: 'tags',
                    //     placeholder: 'Chọn từ khóa',
                    //     create: true
                    // });
                    //
                    // self.on('click', '.col-product-tab .remove', function () {
                    //     $(this).parent().remove();
                    // });

                    self.find('.select-picker').selectpicker();

                    CKEDITOR.config.toolbar = [
                        ['Styles','Format','Font','FontSize'],
                        '/',
                        ['Bold','Italic','Underline','StrikeThrough','-','Undo','Redo','-','Cut','Copy','Paste','Find','Replace','-','Outdent','Indent','-','Print'],
                        '/',
                        ['NumberedList','BulletedList','-','JustifyLeft','JustifyCenter','JustifyRight','JustifyBlock'],
                        ['Image','Table','-','Link','Flash','Smiley','TextColor','BGColor','Source']
                    ];
                    CKEDITOR.config.height = '100px';
                }
            },
            edit : {
                require: {
                    scripts: ['bootstrap-inputmask.js', 'tags.js', 'photojs/jquery.form.js', 'photojs.js','lightbox2-master/src/js/lightbox.js', 'bootstrap-select.js'],
                    stylesheets: ['photojs.css','lightbox2-master/src/css/lightbox.css', 'bootstrap-select.css']
                },
                execute: function () {
                    var self = this;
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

                    // self.find('.photojs').photoJs({
                    //     object: 'editor'
                    // });
                    //
                    // self.find('.icon-upload-alt').photoJs({
                    //     object: 'multiple'
                    // });
                    //
                    // //keywords
                    // self.find('.tags-js.tags-keywords').tags({
                    //     object_name: 'tags',
                    //     name: 'tags',
                    //     placeholder: 'Chọn từ khóa',
                    //     create: true
                    // });
                    //
                    // self.on('click', '.col-product-tab .remove', function () {
                    //     $(this).parent().remove();
                    // });

                    CKEDITOR.config.toolbar = [
                        ['Styles','Format','Font','FontSize'],
                        '/',
                        ['Bold','Italic','Underline','StrikeThrough','-','Undo','Redo','-','Cut','Copy','Paste','Find','Replace','-','Outdent','Indent','-','Print'],
                        '/',
                        ['NumberedList','BulletedList','-','JustifyLeft','JustifyCenter','JustifyRight','JustifyBlock'],
                        ['Image','Table','-','Link','Flash','Smiley','TextColor','BGColor','Source']
                    ];
                    CKEDITOR.config.height = '100px';

                    self.find('.select-picker').selectpicker();
                }
            }
        }
    }
});
