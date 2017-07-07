/**
 * Created by tuandv on 8/15/16.
 */

(function ($) {
    var obj = null;
    var xhr = null;
    var tags = function (el, options) {
        var self = this;
        this.el = el;
        this.container = options.container || el;

        this.params = {
            object_name: 'category',
            name: 'category',
            placeholder: '',
            create: false,
            element: ''
        };

        this.options = {
            object_name: 'category',
            name: 'category',
            placeholder: '',
            create: false,
            element: ''
        };

        $.each(options, function (field, value) {
            switch (field) {
                case 'object_name':
                case 'name':
                case 'placeholder':
                case 'create':
                case 'element':
                    self.options[field] = value;
                    self.params[field] = value;
                    break;
                default :
                    break;
            }
        });

        this.tagsJS = null;
        this.render = function(){
            this.tagsJS = $('<div></div>').addClass('tags-js');
            var container = '<div class="block-tag"></div>' +
                            '<div class="block-input">' +
                                '<input style="margin-bottom: 5px" type="text" placeholder="'+ self.params.placeholder +'">' +
                                '<div class="block-popup">' +
                                    '<div class="loading">' +
                                        '<div class="loading-icon"></div>' +
                                    '</div>' +
                                    '<ul>' +
                                    '</ul>' +
                                '</div>' +
                            '</div>';

            this.tagsJS.html(container);
            this.tagsJS.appendTo(this.el);

            //Add tags init
            var size = this.el.find('ul li').size();
            if(size){
                this.el.find('ul li').each(function(){
                    var tag_id = $(this).data('tag-id');
                    var tag_name = $(this).data('tag-name');

                    var block_tag = '<a class="tag" data-id="'+ tag_id +'"><i class="ace-icon icon-circle"></i><span>'+ tag_name +'</span><span class="ace-icon icon-remove"></span><input type="hidden" name="'+ self.params.object_name +'[]" value="'+ tag_id +'"></a>';
                    self.tagsJS.find('.block-tag').append(block_tag);
                });

                //this.el.find('ul').remove();
            }

            var timeoutReference;
            this.tagsJS.on('click', '.block-input input', function(){
                if($(this).parent().find('.block-popup').css('display') === 'none'){
                    //hide all popup
                    $('.block-popup').each(function(){
                        $(this).hide();
                    });

                    $(this).parent().find('.block-popup').toggle();

                    //loading
                    self.tagsJS.find('.block-popup .loading').show();

                    if (timeoutReference) clearTimeout(timeoutReference);
                    timeoutReference = setTimeout(function() {
                        self.load();
                    }, 250);
                }
            });

            this.tagsJS.find('.block-input').on('input', function(){
                if (timeoutReference) clearTimeout(timeoutReference);
                timeoutReference = setTimeout(function() {
                    self.load();
                }, 250);
            });

            this.tagsJS.find('.block-input input').keypress(function(event) {
                if(event.which === 13) {
                    event.preventDefault();

                    //Check if created
                    if(self.params.create){
                        var value = $(this).val();
                        var me = $(this);

                        //
                        $.ajax({
                            method: 'POST',
                            dataType: 'json',
                            url: Registry.get('SITE_URL') + 'api/administrator/index',
                            data: {
                                create: value,
                                type: self.params.object_name
                            },
                            beforeSend: function () {},
                            success: function(resp) {
                                if(resp.data){
                                    var block_tag = self.tagsJS.find('.block-tag');
                                    if(self.params.element){
                                        block_tag = $(self.params.element);
                                    }

                                    var container = '';

                                    $.each(resp.data, function (key, value) {
                                        if(value.id && value.name){
                                            container += '<a class="tag" data-id="'+ value.id +'">' +
                                                '<i class="ace-icon icon-circle"></i>' +
                                                '<span>'+ value.name +'</span>' +
                                                '<span class="ace-icon icon-remove"></span>' +
                                                '<input type="hidden" name="'+ self.params.object_name +'[]" value="'+ value.id +'">' +
                                                '</a>';
                                        }
                                    });

                                    block_tag.append(container);

                                    //hide
                                    self.tagsJS.find('.block-popup').fadeOut();
                                    me.val('');
                                }
                            }
                        });
                    }
                }
            });

            this.tagsJS.find('.block-popup ul').on('click', 'li', function(){
                var block_tag = self.tagsJS.find('.block-tag');

                if(self.params.element){
                    block_tag = $(self.params.element);
                }

                var id = $(this).data('id'),
                    name = $(this).data('name');

                if(id){
                    //
                    var tag_exists = false;
                    block_tag.find('.tag').each(function(){
                        if($(this).data('id') === id){
                            tag_exists = true;
                        }
                    });

                    if(!tag_exists){
                        var container = '';

                        container += '<a class="tag" data-id="'+ id +'">' +
                            '<i class="ace-icon icon-circle"></i>' +
                            '<span>'+ name +'</span>' +
                            '<span class="ace-icon icon-remove"></span>' +
                            '<input type="hidden" name="'+ self.params.name +'[]" value="'+ id +'">' +
                            '</a>';

                        block_tag.append(container);

                        //hide
                        self.tagsJS.find('.block-popup').fadeOut();

                        //
                        self.tagsJS.find('.block-input input').focus().val('');

                    }
                }
            });

            this.tagsJS.on('click', '.tag .icon-remove', function(){
                $(this).parent().remove();
            });
        };

        this.load = function(){

            var value = self.tagsJS.find('.block-input').find('input').val();
            var selected_id = [];

            //Show
            self.tagsJS.find('.block-popup').fadeIn();

            if(self.params.element){
                $(self.params.element).find('.tag').each(function () {
                    if($(this).data('id')){
                        selected_id.push($(this).data('id'));
                    }
                });
            }else {
                self.tagsJS.find('.block-tag .tag').each(function () {
                    if($(this).data('id')){
                        selected_id.push($(this).data('id'));
                    }
                });
            }

            if(xhr && xhr.readystate !== 4){
                xhr.abort();
            }

            xhr = $.ajax({
                method: 'GET',
                dataType: 'json',
                url: Registry.get('SITE_URL') + 'api/administrator/index',
                data: {
                    search: value,
                    type: self.params.object_name,
                    selected_id: selected_id
                },
                beforeSend: function () {},
                success: function(resp) {
                    var container = '';

                    if(resp.data.rows && resp.data.rows.length > 0){
                        $.each(resp.data.rows, function(key, value){
                            container += '<li data-id="'+ value.id +'" data-name="'+ value.name +'">'+ value.name +'</li>';
                        });

                    }else{
                        container += '<li>Không tìm thấy kết quả, nhấn Enter để thêm mới</li>';
                    }

                    self.tagsJS.find('.block-popup ul').html(container);
                    self.tagsJS.find('.block-popup .loading').fadeOut();
                }
            });
        };

        this.init();
    };

    $(document).mouseup(function (e){
        var container = $(".block-popup");
        if (!container.is(e.target) // if the target of the click isn't the container...
            && container.has(e.target).length === 0) // ... nor a descendant of the container
        {
            //container
            $('.tags-js').find('.block-popup').hide();
        }
    });

    tags.prototype = {
        init: function () {
            if (!this.el.data('tags-js')) {
                this.el.data('tags-js', 'tags-js-jquery_' + parseInt(Math.random() * 1000));
                var self = this;
                self.render();
            }
        }
    };
    $.fn.tags = function (options) {
        var param = {};
        this.each(function () {
            obj = new tags($(this), $.extend(options, param));
            $(this).data('obj_tags', obj);
        });

        return this;
    };

})(window.jQuery);
