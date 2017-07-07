/**
 * Created by tuandv on 8/2/16.
 */

Controller.define('administrator/permission', function () {
    var xhr =null;
    return {
        model: {
            addPermission: function (params) {
                return $.ajax({
                    type: 'post',
                    url: Registry.get('SITE_URL') + 'administrator/permission/add',
                    data: params
                });
            },
            deletePermission: function (params) {
                console.log(params);
                return $.ajax({
                    type: 'post',
                    url: Registry.get('SITE_URL') + 'administrator/permission/delete',
                    data: params
                });
            }
        },
        actions: {
            index: {
                require: {
                    scripts: [],
                    stylesheets: []
                },
                execute: function () {
                    var self = this;

                }
            },
            grant : {
                require: {
                    scripts: [],
                    stylesheets: []
                },
                execute: function () {
                    var self = this;
                    self.on('click', '.actionName', function () {
                        var isChecked = $(this).is(':checked'),
                            resource = $(this).val(),
                            part = Registry.get('part'),
                            id = +Registry.get('id');
                        var params = {
                            'part' : part,
                            'id_access' : id,
                            'resource' : resource
                        };
                        if(isChecked == true){
                            self.model.addPermission(params).then(function (resp) {

                            });
                        }else{
                            console.log(111111);
                            self.model.deletePermission(params).then(function (resp) {

                            });
                        }
                    });
                }
            }
        }
    }
});