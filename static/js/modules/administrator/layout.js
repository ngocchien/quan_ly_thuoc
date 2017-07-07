/**
 * Created by chiennn on 04/06/2017.
 */
$(document).ready(function(){
    var is_min = localStorage.getItem('menu-min');

    if(is_min == 1){
        $('#sidebar').addClass('menu-min');
    }

    $('#sidebar #sidebar-collapse').on('click',function () {
        if($("#sidebar").hasClass('menu-min')){
            localStorage.setItem('menu-min', 1);
        }else{
            localStorage.setItem('menu-min', 0);
        }
    });
});