/**
 * Created by tuandv on 8/2/16.
 */

Controller.define('application/index', function () {
    var xhr =null;
    return {
        model: {

        },
        actions: {
            top: {
                require: {
                    scripts: [],
                    stylesheets: []
                },
                execute: function () {
                    var self = this;

                    self.find('.site-menu .menu-item').hover(function () {
                        if(!$(this).hasClass('active')){
                            self.find('.site-menu .menu-item.active').removeClass('active');
                            $(this).addClass('active');
                        }
                    });

                    self.find('.site-menu').mouseleave(function () {
                        self.find('.site-menu .menu-item.active').removeClass('active');
                    });

                    $(window).scroll(function() {
                        if($(this).scrollTop() > 100){
                            self.find('.top').hide();
                            self.find('.content').addClass('content-scroll');
                        }else {
                            self.find('.top').show();
                            self.find('.content').removeClass('content-scroll');
                        }
                    });
                }
            },
            index: {
                require: {
                    scripts: ['carousel/owl.carousel.js'],
                    stylesheets: ['owl.carousel.2.2.1/owl.carousel.min.css', 'owl.carousel.2.2.1/owl.theme.default.min.css']
                },
                execute: function () {
                    var self = this;

                    var owl = $(".banner-sidebar");

                    owl.owlCarousel({
                        loop:true,
                        nav:true,
                        items: 1,
                        autoplay: true,
                        autoplayTimeout: 3000,
                        autoplayHoverPause:true
                    });

                    $('.banner-previous').click(function () {
                        owl.trigger('owl.prev');
                    });

                    $('.banner-next').click(function () {
                        owl.trigger('owl.next');
                    });
                }
            }
        }
    }
});