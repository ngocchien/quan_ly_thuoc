/**
 * Created by chiennn on 25/07/2017.
 */

const IS_DEBUG = false;
var prefix = 'qlt:notification:';
var separator = ':';
var ChannelConfig = [
    {
        channel: 'channel-warehouse',
        eventName: 'notify-warehouse-expire'
    }
];
module.exports = {
    IS_DEBUG: IS_DEBUG,
    getEventName: function(channel, userId) {
        for (var i = 0; i < ChannelConfig.length; i++) {
            var channelObj = ChannelConfig[i];
            var formattedChannel = this.formatChannel(channelObj.channel, userId);
            if (formattedChannel === channel) {
                return channelObj.eventName;
            }
        }
    },
    getAllChannel: function (userId) {
        var channels = [];
        for (var i = 0; i < ChannelConfig.length; i++) {
            var channelObj = ChannelConfig[i];
            var formattedChannel = this.formatChannel(channelObj.channel, userId);
            channels.push(formattedChannel);
        }
        return channels;
    },
    formatChannel: function(channel, userId) {
        return prefix + channel + separator + userId;
    }
};