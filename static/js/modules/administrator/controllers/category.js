Controller.define("administrator/category",function(){return{model:{deleteCate:function(a){return $.ajax({type:"post",url:Registry.get("SITE_URL")+"admin/category/delete",data:a,dataType:"json"})}},actions:{index:{require:{scripts:["bootbox/bootbox.js"],stylesheets:[]},execute:function(){var a=this;a.on("click",".delete",function(){var b=$(this).attr("rel");if(!b)return bootbox.alert("X\u1ea3y ra l\u1ed7i trong qu\u00e1 tr\u00ecnh x\u1eed l\u00fd! Vui l\u00f2ng refresh l\u1ea1i tr\u00ecnh duy\u1ec7t v\u00e0 th\u1eed l\u1ea1i!"),
!1;bootbox.confirm("B\u1ea1n c\u00f3 ch\u1eafc ch\u1eafn mu\u1ed1n x\u00f3a danh m\u1ee5c n\u00e0y kh\u00f4ng ????",function(c){c&&a.model.deleteCate({cate_id:b}).then(function(a){1==a.st?bootbox.alert(a.ms,function(){window.location=window.location.href}):bootbox.alert(a.ms)})})}).on("change",".limit-query",function(){window.location=$(this).val()})}},create:{require:{scripts:["bootstrap-select.js"],stylesheets:["bootstrap-select.css"]},execute:function(){this.find(".select-picker").selectpicker()}},
edit:{require:{scripts:["bootstrap-select.js"],stylesheets:["bootstrap-select.css"]},execute:function(){this.find(".select-picker").selectpicker()}}}}});
