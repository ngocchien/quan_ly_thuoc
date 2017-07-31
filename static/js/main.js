(function (window, document, $) {

    var Configs = {
            library_url: location.host,
            package_url: location.host,
            module_url: location.host,
            model_url: location.host,
            css_url: location.host,
            mod_concat: false,
            debug: false,
            cache: true,
            version : 'v1'
        },
        undef = "undefined";

    window.MVC = {
        setup: function (config) {
            $.extend(Configs, config);
        }
    };

    window.Request = (function () {

        var _params = {};

        return {
            setParams: function (params) {
                $.extend(_params, params);
            },
            setParam: function (param, val) {
                _params[param] = val;
            },
            getParams: function () {
                return _params;
            },
            getParam: function (param, _default) {
                return _params[param] || _default;
            }
        }

    })();

    window.Hash = (function () {

        function parse(string) {

            var data = {}, i, l;
            string = string.split('&');

            for (i = 0, l = string.length; i < l; i++) {
                var segs = string[i].split('=');
                data[segs[0]] = segs[1];
            }

            return data;
        }

        function getParams() {
            return parse(window.location.hash.replace('#', '')) || {};
        }

        function getParam(name, _default) {
            return getParams()[name] || _default;
        }

        function setParams(params) {
            var query = [];

            for (var k in params) {
                query.push(k + '=' + params[k]);
            }

            window.location.hash = "#" + query.join('&');
        }

        function setup(callback, _default) {

            if (window.location.hash) {
                var href = parse(window.location.hash.replace('#', ''));
                params = getParams();
                params = $.extend({}, _default, href);

                setParams(params);
                callback(window.Event, params);
            } else {
                setParams(_default);
                callback(window.Event, _default);
            }
        }

        return {
            apply: function (callback, _default) {
                (typeof callback == 'function') && setup(callback, _default)
            },
            getParam: getParam,
            getParams: getParams,
            setParams: setParams
        };

    })();

    var Error = function (e) {

        if (Configs.debug) {
            throw new window.Error(e);
        }

        return undefined;
    };


    (function () {

        var namespaces = {};

        window.Registry = {
            set: function (ns, val) {
                namespaces[ns] = val;
            },
            get: function (ns, prop) {
                return (typeof ns === undef) ? namespaces : (typeof prop === 'string') ? (namespaces[ns][prop] || null) : namespaces[ns];
            }
        }

    })();

    var Sandbox = (function ($) {

        var Sandbox = {};

        (function ($) {

            var subscribers = {};

            $.extend(Sandbox, {
                subscribe: function (pub, eventType, callback) {

                    if (typeof callback !== 'function') {
                        return Error('Callback must be a function.');
                    }

                    var id = this.attr('data-view');

                    undef === typeof subscribers[pub] && (subscribers[pub] = {});

                    pub = subscribers[pub];

                    undef === typeof pub[eventType] && (pub[eventType] = {});

                    eventType = pub[eventType];

                    undef === typeof eventType[id] && (eventType[id] = [])

                    eventType[id].push(callback);

                    return this;
                },
                unsubscribe: function (pub, eventType, callback) {

                    if (typeof subscribers[pub] === undef || (typeof eventType !== undef && typeof subscribers[pub][eventType] === undef)) {
                        return;
                    }

                    var sub = this.attr('data-view');

                    if (typeof eventType === undef) {
                        for (var eventType in subscribers[pub]) {
                            typeof subscribers[pub][eventType][sub] !== undef && delete subscribers[pub][eventType][sub];
                        }
                    } else {
                        subscribers[pub][eventType][sub] = null;
                    }

                    return this;

                },
                publish: function (eventType, data) {
                    var id = this.attr('data-view');

                    setTimeout(function () {
                        if (typeof subscribers[id] === undef || subscribers[id][eventType] === undef) {
                            return;
                        }

                        var events = subscribers[id][eventType];

                        for (var sub in events) {
                            for (var callback in events[sub]) {
                                events[sub][callback](data);
                            }
                        }

                    }, 0);

                    return this;
                }
            });
        })($);

        return Sandbox;

    })($);

    var Model = {};

    var Controller = (function ($, undefined) {

        var namespaces = {},
            modules = [],
            processed = 0,
            views = {},
            execute = function (module, action) {

                if (typeof namespaces[module] === undef) return;

                var dom = views[namespaces[module]['id'] + '/' + action];

                if (typeof dom !== undef && dom.length && !dom.attr('data-view-executed')) {
                    var sandbox = $.extend(dom, Sandbox),
                        actions = namespaces[module]['actions'][action];

                    sandbox.model = $.extend({}, Model, namespaces[module]['model']);

                    loadRequirement(actions, function () {
                        actions.execute.call(sandbox);
                        dom.attr('data-view-executed', 1);
                    });

                    return true;
                }

                return false;
            }


        function bootstrap() {
            for (var module in namespaces) {
                for (var action in namespaces[module]['actions']) {
                    execute(module, action);
                }
            }
        }

        function loadRequirement(actions, callback) {
            var require = {
                    scripts: [],
                    stylesheets: [],
                    models: []
                },
                i, l;

            if (undef !== typeof actions['require']) {
                if (undef !== typeof actions.require['models']) {
                    for (i = 0, l = actions.require.models.length; i < l; i++) {
                        ($.inArray(actions.require.models[i], require.models) === -1) && require.models.push(actions.require.models[i]);
                    }
                }

                if (undef !== typeof actions.require['scripts']) {
                    for (i = 0, l = actions.require.scripts.length; i < l; i++) {
                        ($.inArray(actions.require.scripts[i], require.scripts) === -1) && require.scripts.push(actions.require.scripts[i]);
                    }
                }

                if (undef !== typeof actions.require['stylesheets']) {
                    for (i = 0, l = actions.require.stylesheets.length; i < l; i++) {
                        ($.inArray(actions.require.stylesheets[i], require.stylesheets) === -1) && require.stylesheets.push(actions.require.stylesheets[i]);
                    }
                }
            }

            if (require.stylesheets.length > 0) {
                for (i = 0; i < require.stylesheets.length; i++) {
                    l = document.createElement("link");
                    l.rel = 'stylesheet';
                    l.type = 'text/css';
                    l.href = require.stylesheets[i].indexOf(':') !== -1 ? require.stylesheets[i] : Configs.css_url + '/' + require.stylesheets[i];
                    document.getElementsByTagName('head')[0].appendChild(l);
                }
            }

            if (require.scripts.length > 0) {
                Bootloader.queueScript($.map(require.scripts, function (n) {
                    return n.indexOf(':') > -1 ? n : Configs.library_url + '/' + (Configs.debug ? n.replace(/\.js$/, '.debug.js') : n);
                }));
            }

            if (require.models.length > 0) {
                Bootloader.queueScript($.map(require.models, function (n) {
                    return n.indexOf(':') > -1 ? n : Configs.model_url + '/' + n + (Configs.debug ? '.debug.js' : '.js');
                }));
            }

            if (require.scripts.length || require.models.length) {
                Bootloader.runQueue().wait(callback);
            }
            else {
                callback();
            }
        }

        MVC.apply = function (module, controller, action) {
            Controller.prepare(module, controller, action);
        }

        return {
            define: function (id, callback) {
                if (typeof callback !== 'function') {
                    return Error('Callback must be a function.');
                }

                if (id in namespaces) {
                    return Error('Controller ' + id + ' has been defined already.');
                }

                var init = callback.call();

                var model = init.model || {};

                var actions = init.actions || {};

                namespaces[id] = {
                    id: id,
                    model: model,
                    actions: actions
                };

                if (++processed === modules.length) {
                    bootstrap();
                }

            },
            get: function (controller, action, callback) {
                if (typeof namespaces[controller] === undef || typeof namespaces[controller]['actions'][action] === undef) {
                    callback && callback(null);
                    return null;
                }

                var clone = $.extend({
                    model: namespaces[controller]['model']
                }, namespaces[controller]['actions'][action]);

                callback && callback(clone);
                return clone;
            },
            prepare: function (module, controller, action) {
                if (arguments.length === 3) {
                    if (Controller.get([module, controller].join('/'), action) !== null) {
                        var dataView = [module, controller, action].join('/');
                        views[dataView] = views[dataView] || $('div[data-view="' + dataView + '"]');
                        execute([module, controller].join('/'), action);
                    }
                    else {
                        Controller.prepare();
                    }
                }
                else {
                    $('div[data-view]:not([data-view-executed])').each(function () {
                        var dataView = this.getAttribute('data-view'),
                            segments = dataView.split('/'),
                            file = segments[0] + '/controllers/' + segments[1] + (Configs.debug ? '.debug.js' : '.js');
                        ($.inArray(file, modules) === -1) && modules.push(file)
                        views[dataView] = $(this);
                    });


                    if (modules.length > 0) {
                        Bootloader.script.apply({}, $.map(modules, function (n, i) {
                            return Configs.module_url + '/' + n;
                        }));
                    }
                }
            }
        }

    })($);

    window.Controller = Controller;

    var Model = (function (undefined) {

        var models = {};

        return {
            define: function (name, model) {

                if (typeof model !== 'function'){
                    var er = Error('Model must be a function!');
                }

                models[name] = model;
            },
            get: function (name, params) {

                if (!(name in models)){
                    var er = Error('Model ' + name + ' has not been defined!');
                }

                return models[name].apply({}, params || {});
            }
        }

    })();

    window.Model = Model;

    $(function () {
        Configs = $.extend(Configs, window.MVC);

        if (Configs.mod_concat == false) {
            (function (global) {
                var _Bootloader = global.Bootloader,

                    // constants for the valid keys of the options object
                    _UseLocalXHR = "UseLocalXHR",
                    _AlwaysPreserveOrder = "AlwaysPreserveOrder",
                    _AllowDuplicates = "AllowDuplicates",
                    _CacheBust = "CacheBust",
                    /*!START_DEBUG*/
                    _Debug = "Debug",
                    /*!END_DEBUG*/
                    _BasePath = "BasePath",

                    // stateless variables used across all Bootloader instances
                    root_page = /^[^?#]*\//.exec(location.href)[0],
                    root_domain = /^\w+\:\/\/\/?[^\/]+/.exec(root_page)[0],
                    append_to = document.head || document.getElementsByTagName("head"),

                    // inferences... ick, but still necessary
                    opera_or_gecko = (global.opera && Object.prototype.toString.call(global.opera) == "[object Opera]") || ("MozAppearance" in document.documentElement.style),

                    /*!START_DEBUG*/
                    // console.log() and console.error() wrappers
                    log_msg = function () {

                    },
                    log_error = log_msg,
                    /*!END_DEBUG*/

                    // feature sniffs (yay!)
                    test_script_elem = document.createElement("script"),
                    explicit_preloading = typeof test_script_elem.preload == "boolean",
                    // http://wiki.whatwg.org/wiki/Script_Execution_Control#Proposal_1_.28Nicholas_Zakas.29
                    real_preloading = explicit_preloading || (test_script_elem.readyState && test_script_elem.readyState == "uninitialized"),
                    // will a script preload with `src` set before DOM append?
                    script_ordered_async = !real_preloading && test_script_elem.async === true,
                    // http://wiki.whatwg.org/wiki/Dynamic_Script_Execution_Order
                    // XHR preloading (same-domain) and cache-preloading (remote-domain) are the fallbacks (for some browsers)
                    xhr_or_cache_preloading = !real_preloading && !script_ordered_async && !opera_or_gecko;

                /*!START_DEBUG*/
                // define console wrapper functions if applicable
                if (global.console && global.console.log) {
                    if (!global.console.error) global.console.error = global.console.log;
                    log_msg = function (msg) {
//                        global.console.log(msg);
                    };
                    log_error = function (msg, err) {
                        global.console.error(msg, err);
                    };
                }
                /*!END_DEBUG*/

                // make script URL absolute/canonical
                function canonical_uri(src, base_path) {
                    var absolute_regex = /^\w+\:\/\//;

                    // is `src` is protocol-relative (begins with // or ///), prepend protocol
                    if (/^\/\/\/?/.test(src)) {
                        src = location.protocol + src;
                    }
                    // is `src` page-relative? (not an absolute URL, and not a domain-relative path, beginning with /)
                    else if (!absolute_regex.test(src) && src.charAt(0) != "/") {
                        // prepend `base_path`, if any
                        src = (base_path || "") + src;
                    }
                    // make sure to return `src` as absolute
                    return absolute_regex.test(src) ? src : ((src.charAt(0) == "/" ? root_domain : root_page) + src);
                }

                // does the chain group have any ready-to-execute scripts?
                function check_chain_group_scripts_ready(chain_group) {
                    var any_scripts_ready = false;
                    for (var i = 0; i < chain_group.scripts.length; i++) {
                        if (chain_group.scripts[i].ready && chain_group.scripts[i].exec_trigger) {
                            any_scripts_ready = true;
                            chain_group.scripts[i].exec_trigger();
                            chain_group.scripts[i].exec_trigger = null;
                        }
                    }
                    return any_scripts_ready;
                }

                // creates a script load listener
                function create_script_load_listener(elem, registry_item, flag, onload) {
                    elem.onload = elem.onreadystatechange = function () {
                        if ((elem.readyState && elem.readyState != "complete" && elem.readyState != "loaded") || registry_item[flag]) return;
                        elem.onload = elem.onreadystatechange = null;
                        onload();
                    };
                }

                // script executed handler
                function script_executed(registry_item) {
                    registry_item.ready = registry_item.finished = true;
                    for (var i = 0; i < registry_item.finished_listeners.length; i++) {
                        registry_item.finished_listeners[i]();
                    }
                    registry_item.ready_listeners = [];
                    registry_item.finished_listeners = [];
                }

                // make the request for a scriptha
                function request_script(chain_opts, script_obj, registry_item, onload, preload_this_script) {
                    // setTimeout() "yielding" prevents some weird race/crash conditions in older browsers
                    setTimeout(function () {
                        var script, src = script_obj.real_src + '?' + Configs.version,
                            xhr;

                        // don't proceed until `append_to` is ready to append to
                        if (typeof append_to.item !== undef) { // check if `append_to` ref is still a live node list
                            if (!append_to[0]) { // `append_to` node not yet ready
                                // try again in a little bit -- note: will re-call the anonymous function in the outer setTimeout, not the parent `request_script()`
                                setTimeout(arguments.callee, 25);
                                return;
                            }
                            // reassign from live node list ref to pure node ref -- avoids nasty IE bug where changes to DOM invalidate live node lists
                            append_to = append_to[0];
                        }
                        script = document.createElement("script");
                        if (script_obj.type) script.type = script_obj.type;
                        if (script_obj.charset) script.charset = script_obj.charset;

                        // should preloading be used for this script?
                        if (preload_this_script) {
                            // real script preloading?
                            if (real_preloading) {
                                /*!START_DEBUG*/
                                if (chain_opts[_Debug]) log_msg("start script preload: " + src);
                                /*!END_DEBUG*/
                                registry_item.elem = script;
                                if (explicit_preloading) { // explicit preloading (aka, Zakas' proposal)
                                    script.preload = true;
                                    script.onpreload = onload;
                                } else {
                                    script.onreadystatechange = function () {
                                        if (script.readyState == "loaded") onload();
                                    };
                                }
                                script.src = src;
                                // NOTE: no append to DOM yet, appending will happen when ready to execute
                            }
                            // same-domain and XHR allowed? use XHR preloading
                            else if (preload_this_script && src.indexOf(root_domain) == 0 && chain_opts[_UseLocalXHR]) {
                                xhr = new XMLHttpRequest(); // note: IE never uses XHR (it supports true preloading), so no more need for ActiveXObject fallback for IE <= 7
                                /*!START_DEBUG*/
                                if (chain_opts[_Debug]) log_msg("start script preload (xhr): " + src);
                                /*!END_DEBUG*/
                                xhr.onreadystatechange = function () {
                                    if (xhr.readyState == 4) {
                                        xhr.onreadystatechange = function () {
                                        }; // fix a memory leak in IE
                                        registry_item.text = xhr.responseText + "\n//@ sourceURL=" + src; // http://blog.getfirebug.com/2009/08/11/give-your-eval-a-name-with-sourceurl/
                                        onload();
                                    }
                                };
                                xhr.open("GET", src);
                                xhr.send();
                            }
                            // as a last resort, use cache-preloading
                            else {
                                /*!START_DEBUG*/
                                if (chain_opts[_Debug]) log_msg("start script preload (cache): " + src);
                                /*!END_DEBUG*/
                                script.type = "text/cache-script";
                                create_script_load_listener(script, registry_item, "ready", function () {
                                    append_to.removeChild(script);
                                    onload();
                                });
                                script.src = src;
                                append_to.insertBefore(script, append_to.firstChild);
                            }
                        }
                        // use async=false for ordered async? parallel-load-serial-execute http://wiki.whatwg.org/wiki/Dynamic_Script_Execution_Order
                        else if (script_ordered_async) {
                            /*!START_DEBUG*/
                            if (chain_opts[_Debug]) log_msg("start script load (ordered async): " + src);
                            /*!END_DEBUG*/
                            script.async = false;
                            create_script_load_listener(script, registry_item, "finished", onload);
                            script.src = src;
                            append_to.insertBefore(script, append_to.firstChild);
                        }
                        // otherwise, just a normal script element
                        else {
                            /*!START_DEBUG*/
                            if (chain_opts[_Debug]) log_msg("start script load: " + src);
                            /*!END_DEBUG*/
                            create_script_load_listener(script, registry_item, "finished", onload);
                            script.src = src;
                            append_to.insertBefore(script, append_to.firstChild);
                        }
                    }, 0);
                }

                // create a clean instance of Bootloader
                function create_sandbox() {
                    var global_defaults = {},
                        can_use_preloading = real_preloading || xhr_or_cache_preloading,
                        queue = [],
                        registry = {},
                        instanceAPI;

                    // global defaults
                    global_defaults[_UseLocalXHR] = true;
                    global_defaults[_AlwaysPreserveOrder] = false;
                    global_defaults[_AllowDuplicates] = false;
                    global_defaults[_CacheBust] = false;
                    /*!START_DEBUG*/
                    global_defaults[_Debug] = false;
                    /*!END_DEBUG*/
                    global_defaults[_BasePath] = "";

                    // execute a script that has been preloaded already
                    function execute_preloaded_script(chain_opts, script_obj, registry_item) {
                        var script;

                        function preload_execute_finished() {
                            if (script != null) { // make sure this only ever fires once
                                script = null;
                                script_executed(registry_item);
                            }
                        }

                        if (registry[script_obj.src].finished) return;
                        if (!chain_opts[_AllowDuplicates]) registry[script_obj.src].finished = true;

                        script = registry_item.elem || document.createElement("script");
                        if (script_obj.type) script.type = script_obj.type;
                        if (script_obj.charset) script.charset = script_obj.charset;
                        create_script_load_listener(script, registry_item, "finished", preload_execute_finished);

                        // script elem was real-preloaded
                        if (registry_item.elem) {
                            registry_item.elem = null;
                        }
                        // script was XHR preloaded
                        else if (registry_item.text) {
                            script.onload = script.onreadystatechange = null; // script injection doesn't fire these events
                            script.text = registry_item.text;
                        }
                        // script was cache-preloaded
                        else {
                            script.src = script_obj.real_src;
                        }
                        append_to.insertBefore(script, append_to.firstChild);

                        // manually fire execution callback for injected scripts, since events don't fire
                        if (registry_item.text) {
                            preload_execute_finished();
                        }
                    }

                    // process the script request setup
                    function do_script(chain_opts, script_obj, chain_group, preload_this_script) {
                        var registry_item, registry_items, ready_cb = function () {
                                script_obj.ready_cb(script_obj, function () {
                                    execute_preloaded_script(chain_opts, script_obj, registry_item);
                                });
                            },
                            finished_cb = function () {
                                script_obj.finished_cb(script_obj, chain_group);
                            };

                        script_obj.src = canonical_uri(script_obj.src, chain_opts[_BasePath]);
                        script_obj.real_src = script_obj.src +
                            // append cache-bust param to URL?
                            (chain_opts[_CacheBust] ? ((/\?.*$/.test(script_obj.src) ? "&_" : "?_") + ~~(Math.random() * 1E9) + "=") : "");

                        if (!registry[script_obj.src]) registry[script_obj.src] = {
                            items: [],
                            finished: false
                        };
                        registry_items = registry[script_obj.src].items;

                        // allowing duplicates, or is this the first recorded load of this script?
                        if (chain_opts[_AllowDuplicates] || registry_items.length == 0) {
                            registry_item = registry_items[registry_items.length] = {
                                ready: false,
                                finished: false,
                                ready_listeners: [ready_cb],
                                finished_listeners: [finished_cb]
                            };

                            request_script(chain_opts, script_obj, registry_item,
                                // which callback type to pass?
                                (
                                    (preload_this_script) ? // depends on script-preloading
                                        function () {
                                            registry_item.ready = true;
                                            for (var i = 0; i < registry_item.ready_listeners.length; i++) {
                                                registry_item.ready_listeners[i]();
                                            }
                                            registry_item.ready_listeners = [];
                                        } : function () {
                                        script_executed(registry_item);
                                    }),
                                // signal if script-preloading should be used or not
                                preload_this_script);
                        } else {
                            registry_item = registry_items[0];
                            if (registry_item.finished) {
                                finished_cb();
                            } else {
                                registry_item.finished_listeners.push(finished_cb);
                            }
                        }
                    }

                    // creates a closure for each separate chain spawned from this Bootloader instance, to keep state cleanly separated between chains
                    function create_chain() {
                        var chainedAPI, chain_opts = $.extend({}, global_defaults),
                            chain = [],
                            exec_cursor = 0,
                            scripts_currently_loading = false,
                            group;

                        // called when a script has finished preloading
                        function chain_script_ready(script_obj, exec_trigger) {
                            /*!START_DEBUG*/
                            if (chain_opts[_Debug]) log_msg("script preload finished: " + script_obj.real_src);
                            /*!END_DEBUG*/
                            script_obj.ready = true;
                            script_obj.exec_trigger = exec_trigger;
                            advance_exec_cursor(); // will only check for 'ready' scripts to be executed
                        }

                        // called when a script has finished executing
                        function chain_script_executed(script_obj, chain_group) {
                            /*!START_DEBUG*/
                            if (chain_opts[_Debug]) log_msg("script execution finished: " + script_obj.real_src);
                            /*!END_DEBUG*/
                            script_obj.ready = script_obj.finished = true;
                            script_obj.exec_trigger = null;
                            // check if chain group is all finished
                            for (var i = 0; i < chain_group.scripts.length; i++) {
                                if (!chain_group.scripts[i].finished) return;
                            }
                            // chain_group is all finished if we get this far
                            chain_group.finished = true;
                            advance_exec_cursor();
                        }

                        // main driver for executing each partials of the chain
                        function advance_exec_cursor() {
                            while (exec_cursor < chain.length) {
                                if ($.isFunction(chain[exec_cursor])) {
                                    /*!START_DEBUG*/
                                    if (chain_opts[_Debug]) log_msg("Bootloader.wait() executing: " + chain[exec_cursor]);
                                    /*!END_DEBUG*/
                                    try {
                                        chain[exec_cursor++]();
                                    } catch (err) {
                                        /*!START_DEBUG*/
                                        if (chain_opts[_Debug]) log_error("Bootloader.wait() error caught: ", err.message);
                                        /*!END_DEBUG*/
                                    }
                                    continue;
                                } else if (!chain[exec_cursor].finished) {
                                    if (check_chain_group_scripts_ready(chain[exec_cursor])) continue;
                                    break;
                                }
                                exec_cursor++;
                            }
                            // we've reached the end of the chain (so far)
                            if (exec_cursor == chain.length) {
                                scripts_currently_loading = false;
                                group = false;
                            }
                        }

                        // setup next chain script group
                        function init_script_chain_group() {
                            if (!group || !group.scripts) {
                                chain.push(group = {
                                    scripts: [],
                                    finished: true
                                });
                            }
                        }

                        // API for Bootloader chains
                        chainedAPI = {
                            // start loading one or more scripts
                            script: function () {
                                for (var i = 0; i < arguments.length; i++) {
                                    (function (script_obj, script_list) {
                                        var splice_args;

                                        if (!$.isArray(script_obj)) {
                                            script_list = [script_obj];
                                        }
                                        for (var j = 0; j < script_list.length; j++) {
                                            init_script_chain_group();
                                            script_obj = script_list[j];

                                            if ($.isFunction(script_obj)) script_obj = script_obj();
                                            if (!script_obj) continue;
                                            if ($.isArray(script_obj)) {
                                                // set up an array of arguments to pass to splice()
                                                splice_args = [].slice.call(script_obj); // first include the actual array elements we want to splice in
                                                splice_args.unshift(j, 1); // next, put the `index` and `howMany` parameters onto the beginning of the splice-arguments array
                                                [].splice.apply(script_list, splice_args); // use the splice-arguments array as arguments for splice()
                                                j--; // adjust `j` to account for the loop's subsequent `j++`, so that the next loop iteration uses the same `j` index value
                                                continue;
                                            }
                                            if (typeof script_obj == "string") script_obj = {
                                                src: script_obj
                                            };
                                            script_obj = $.extend({
                                                ready: false,
                                                ready_cb: chain_script_ready,
                                                finished: false,
                                                finished_cb: chain_script_executed
                                            }, script_obj);
                                            group.finished = false;
                                            group.scripts.push(script_obj);

                                            do_script(chain_opts, script_obj, group, (can_use_preloading && scripts_currently_loading));
                                            scripts_currently_loading = true;
                                            if (chain_opts[_AlwaysPreserveOrder]) chainedAPI.wait();
                                        }
                                    })(arguments[i], arguments[i]);
                                }
                                return chainedAPI;
                            },
                            // force LABjs to pause in execution at this point in the chain, until the execution thus far finishes, before proceeding
                            wait: function () {
                                if (arguments.length > 0) {
                                    for (var i = 0; i < arguments.length; i++) {
                                        chain.push(arguments[i]);
                                    }
                                    group = chain[chain.length - 1];
                                } else group = false;

                                advance_exec_cursor();

                                return chainedAPI;
                            }
                        };

                        // the first chain link API (includes `setOptions` only this first time)
                        return {
                            script: chainedAPI.script,
                            wait: chainedAPI.wait,
                            setOptions: function (opts) {
                                $.extend(chain_opts, opts);
                                return chainedAPI;
                            }
                        };
                    }

                    // API for each initial Bootloader instance (before chaining starts)
                    instanceAPI = {
                        // main API functions
                        setGlobalDefaults: function (opts) {
                            $.extend(global_defaults, opts);
                            return instanceAPI;
                        },
                        setOptions: function () {
                            return create_chain().setOptions.apply(null, arguments);
                        },
                        script: function () {
                            return create_chain().script.apply(null, arguments);
                        },
                        wait: function () {
                            return create_chain().wait.apply(null, arguments);
                        },

                        // built-in queuing for Bootloader `script()` and `wait()` calls
                        // useful for building up a chain programmatically across various script locations, and simulating
                        // execution of the chain
                        queueScript: function () {
                            queue[queue.length] = {
                                type: "script",
                                args: [].slice.call(arguments)
                            };
                            return instanceAPI;
                        },
                        queueWait: function () {
                            queue[queue.length] = {
                                type: "wait",
                                args: [].slice.call(arguments)
                            };
                            return instanceAPI;
                        },
                        runQueue: function () {
                            var $L = instanceAPI,
                                len = queue.length,
                                i = len,
                                val;
                            for (; --i >= 0;) {
                                val = queue.shift();
                                $L = $L[val.type].apply(null, val.args);
                            }
                            return $L;
                        },

                        // rollback `[global].Bootloader` to what it was before this file was loaded, the return this current instance of Bootloader
                        noConflict: function () {
                            global.Bootloader = _Bootloader;
                            return instanceAPI;
                        },

                        // create another clean instance of Bootloader
                        sandbox: function () {
                            return create_sandbox();
                        }
                    };

                    return instanceAPI;
                }

                // create the main instance of Bootloader
                global.Bootloader = create_sandbox();
                Bootloader.setGlobalDefaults({
                    CacheBust: Configs.cache,
                    Debug: Configs.debug
                });

                /* The following "hack" was suggested by Andrea Giammarchi and adapted from: http://webreflection.blogspot.com/2009/11/195-chars-to-help-lazy-loading.html
                 NOTE: this hack only operates in FF and then only in versions where document.readyState is not present (FF < 3.6?).

                 The hack essentially "patches" the **page** that LABjs is loaded onto so that it has a proper conforming document.readyState, so that if a script which does
                 proper and safe dom-ready detection is loaded onto a page, after dom-ready has passed, it will still be able to detect this state, by inspecting the now hacked
                 document.readyState property. The loaded script in question can then immediately trigger any queued code executions that were waiting for the DOM to be ready.
                 For instance, jQuery 1.4+ has been patched to take advantage of document.readyState, which is enabled by this hack. But 1.3.2 and before are **not** safe or
                 fixed by this hack, and should therefore **not** be lazy-loaded by script loader tools such as LABjs.
                 */
                (function (addEvent, domLoaded, handler) {
                    if (document.readyState == null && document[addEvent]) {
                        document.readyState = "loading";
                        document[addEvent](domLoaded, handler = function () {
                            document.removeEventListener(domLoaded, handler, false);
                            document.readyState = "complete";
                        }, false);
                    }
                })("addEventListener", "DOMContentLoaded");

            })(window);
        }

        Controller.prepare();
    });

})(window, document, jQuery);