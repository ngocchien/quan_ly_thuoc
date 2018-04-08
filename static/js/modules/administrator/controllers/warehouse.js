Controller.define("administrator/warehouse",function(){return{model:{deleteWarehouse:function(a){return $.ajax({type:"post",url:Registry.get("SITE_URL")+"admin/warehouse/delete",data:a,dataType:"json"})},deleteExpire:function(a){return $.ajax({type:"post",url:Registry.get("SITE_URL")+"admin/warehouse/delete-expire",data:a,dataType:"json"})},deleteExpired:function(a){return $.ajax({type:"post",url:Registry.get("SITE_URL")+"admin/warehouse/delete-expired",data:a,dataType:"json"})},calculatorTotalPrice:function(a){var b=
a.unit_price,c=a.quantity;return b*c-b*c*a.discount/100}},actions:{index:{require:{scripts:["bootbox/bootbox.js"],stylesheets:[]},execute:function(){var a=this,b=function(){if(0>=a.find("input[name=data-id]:checked").length)return bootbox.alert("Vui l\u00f2ng ch\u1ecdn s\u1ea3n ph\u1ea9m mu\u1ed1n x\u00f3a!!!"),!1;bootbox.confirm("B\u1ea1n c\u00f3 ch\u1eafc ch\u1eafn mu\u1ed1n x\u00f3a c\u00e1c thu\u1ed1c \u0111\u00e3 nh\u1eadp kho n\u00e0y kh\u00f4ng???",function(b){if(b){var d=[];a.find("input[name=data-id]:checked").each(function(){d.push($(this).val())});
a.model.deleteWarehouse({arr_warehouse_id:d}).then(function(a){1==a.st?bootbox.alert(a.ms,function(){window.location=window.location.href}):bootbox.alert(a.ms)})}})};a.on("click",".remove-all",function(){b()}).on("change",".limit-query",function(){window.location=$(this).val()})}},create:{require:{scripts:["bootstrap-inputmask.js","bootstrap-datepicker.js","bootstrap-datepicker.vi.js","bootstrap-select.js"],stylesheets:["bootstrap-datepicker.css","bootstrap-select.css"]},execute:function(){var a=
this,b=new Date;CKEDITOR.config.height="100px";a.find(".price-mask").inputmask({alias:"decimal",radixPoint:".",groupSeparator:",",autoGroup:!0,rightAlign:!0,autoUnmask:!0,removeMaskOnSubmit:!0,digits:0});a.find(".datetimepicker").inputmask({mask:"99/99/9999"});a.find("input[name=nsx]").datepicker({format:"dd/mm/yyyy",endDate:"today",maxDate:b});a.find("input[name=hsd]").datepicker({format:"dd/mm/yyyy",startDate:"today",minDate:b});a.find(".select-picker").selectpicker({style:"btn-white"});a.on("keyup",
"input[name=unit_price],input[name=discount],input[name=quantity]",function(){var b=+a.find("input[name=unit_price]").val(),d=+a.find("input[name=quantity]").val(),e=+a.find("input[name=discount]").val(),b=a.model.calculatorTotalPrice({unit_price:b,quantity:d,discount:e});a.find("input[name=total_price]").val(b)})}},edit:{require:{scripts:["bootstrap-inputmask.js","bootstrap-datepicker.js","bootstrap-datepicker.vi.js","bootstrap-select.js"],stylesheets:["bootstrap-datepicker.css","bootstrap-select.css"]},
execute:function(){var a=this,b=new Date;a.find(".price-mask").inputmask({alias:"decimal",radixPoint:".",groupSeparator:",",autoGroup:!0,rightAlign:!0,autoUnmask:!0,removeMaskOnSubmit:!0,digits:0});a.find(".datetimepicker").inputmask({mask:"99/99/9999"});a.find("input[name=nsx]").datepicker({format:"dd/mm/yyyy",endDate:"today",maxDate:b});a.find("input[name=hsd]").datepicker({format:"dd/mm/yyyy",startDate:"today",minDate:b});a.find(".select-picker").selectpicker();a.on("keyup","input[name=unit_price],input[name=discount],input[name=quantity]",
function(){var b=+a.find("input[name=unit_price]").val(),d=+a.find("input[name=quantity]").val(),e=+a.find("input[name=discount]").val(),b=a.model.calculatorTotalPrice({unit_price:b,quantity:d,discount:e});a.find("input[name=total_price]").val(b)})}},expire:{require:{scripts:["bootbox/bootbox.js"],stylesheets:[]},execute:function(){var a=this;a.on("click",".remove",function(){var b=$(this).attr("rel");if(!b)return bootbox.alert("X\u1ea3y ra l\u1ed7i, vui l\u00f2ng refresh tr\u00ecnh duy\u1ec7t v\u00e0 th\u1eed l\u1ea1i!!!"),
!1;bootbox.confirm("B\u1ea1n c\u00f3 mu\u1ed1n ng\u1eebng nh\u1eadn th\u00f4ng b\u00e1o s\u1eafp h\u1ebft h\u1ea1n s\u1eed d\u1ee5ng cho thu\u1ed1c n\u00e0y???",function(c){c&&a.model.deleteExpire({id:b}).then(function(a){1==a.st?bootbox.alert(a.ms,function(){window.location=window.location.href}):bootbox.alert(a.ms)})})}).on("click",".delete_warehouse",function(){var b=$(this).attr("rel");if(!b)return bootbox.alert("X\u1ea3y ra l\u1ed7i, vui l\u00f2ng refresh tr\u00ecnh duy\u1ec7t v\u00e0 th\u1eed l\u1ea1i!!!"),
!1;bootbox.confirm("B\u1ea1n c\u00f3 mu\u1ed1n x\u00f3a thu\u1ed1c s\u1eafp h\u1ebft h\u1ea1n n\u00e0y kh\u1ecfi kho kh\u00f4ng???",function(c){c&&a.model.deleteExpired({id:b}).then(function(a){1==a.st?bootbox.alert(a.ms,function(){window.location=window.location.href}):bootbox.alert(a.ms)})})})}},expired:{require:{scripts:["bootbox/bootbox.js"],stylesheets:[]},execute:function(){var a=this;a.on("click",".remove",function(){var b=$(this).attr("rel");if(!b)return bootbox.alert("X\u1ea3y ra l\u1ed7i, vui l\u00f2ng refresh tr\u00ecnh duy\u1ec7t v\u00e0 th\u1eed l\u1ea1i!!!"),
!1;bootbox.confirm("B\u1ea1n c\u00f3 mu\u1ed1n x\u00f3a thu\u1ed1c \u0111\u00e3 h\u1ebft h\u1ea1n n\u00e0y kh\u1ecfi kho kh\u00f4ng???",function(c){c&&a.model.deleteExpired({id:b}).then(function(a){1==a.st?bootbox.alert(a.ms,function(){window.location=window.location.href}):bootbox.alert(a.ms)})})})}}}}});
