/**
 * Created by chiennn on 25/07/2017.
 */
var io = require('socket.io').listen(8001);
//console.log('create server success ... ');
var prefix = 'Proship:invoice:notification:';
var redisClient = require('redis').createClient();
var channelConfig = require('./config.js');
var IS_DEBUG = channelConfig.IS_DEBUG;
var allClients = [];
io.of('/notifications').
on('connection', function (socket) {
    if (IS_DEBUG) {
        console.log("New connection: " + socket.id);
    }
    // Send the message of connection for receiving the user ID
    socket.emit('connected');
    // Receive the ID
    socket.on('join', function (userId) {
        allClients[userId] = socket.id;
        var allChannels = channelConfig.getAllChannel(userId);
        if (IS_DEBUG) {
            console.log('All Channel: ', allChannels);
        }
        redisClient.subscribe(allChannels);
        // subscribe to our channel (We don't need to check because we have a
        // connection per channel/user)
        redisClient.on('message', function (channel, message) {
            var data = JSON.parse(message);
            if (IS_DEBUG) {
                console.log('Channel and data on message: ', channel, data);
            }
            var eventName = channelConfig.getEventName(channel, userId);
            if (IS_DEBUG) {
                console.log('eventName: ', eventName);
            }
            if (eventName) {
                socket.emit(eventName, channel, data);
            }
        });
    });
    socket.on('disconnect', function() {
        if (IS_DEBUG) {
            console.log('disconnected !!!', allClients);
        }
        var userId = allClients.indexOf(socket.id);
        if (userId) {
            var allChannels = channelConfig.getAllChannel(userId);
            redisClient.unsubscribe(allChannels);
            if (IS_DEBUG) {
                console.log('Unsubscribe all channel of user: ', userId);
            }
            allClients.splice(userId, 1);
        }
    });
    socket.on('get-customer-edit-invoice-notification', function () {
        var redisClient2 = require('redis').createClient();
        var total = 5;
        var listNotifKey = prefix + 'list:customer:edit:notification';
        redisClient2.select(7, function () {
            redisClient2.lrange(listNotifKey, 0, total, function (err, keys) {
                var i = 0,
                    arrNotificationList = [];
                keys.forEach(function (key) {
                    var prefixKey = prefix + key;
                    redisClient2.hgetall(prefixKey, function (err, data) {
                        ++i;
                        arrNotificationList.push(data);
                        if (total === i) {
                            var objData = {total: total, notificationList: arrNotificationList};
                            socket.emit('get-customer-edit-invoice-notification-list', objData);
                        }
                    });
                });
                redisClient2.quit();
            });
        });
    });
});