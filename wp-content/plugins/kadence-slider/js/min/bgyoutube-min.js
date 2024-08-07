"use strict";function _typeof(e){return(_typeof="function"==typeof Symbol&&"symbol"==typeof Symbol.iterator?function e(t){return typeof t}:function e(t){return t&&"function"==typeof Symbol&&t.constructor===Symbol&&t!==Symbol.prototype?"symbol":typeof t})(e)}
/*
 * YoutubeBackground - A wrapper for the Youtube API - Great for fullscreen background videos or just regular videos.
 *
 * Licensed under the MIT license:
 *   http://www.opensource.org/licenses/mit-license.php
 *
 *
 * Version:  1.0.5
 *
 */
// Chain of Responsibility pattern. Creates base class that can be overridden.
"function"!=typeof Object.create&&(Object.create=function(e){function t(){}return t.prototype=e,new t}),function(d,a,i){var r=function e(t){
// Load Youtube API
var o=i.createElement("script"),n=i.getElementsByTagName("head")[0];"file://"==a.location.origin?o.src="http://www.youtube.com/iframe_api":o.src="//www.youtube.com/iframe_api",n.appendChild(o),o=// Clean up Tags.
n=null,l(t)},l=function e(t){
// Listen for Gobal YT player callback
"undefined"==typeof YT&&void 0===a.loadingPlayer?(
// Prevents Ready Event from being called twice
a.loadingPlayer=!0,// Creates deferred so, other players know when to wait.
a.dfd=d.Deferred(),a.onYouTubeIframeAPIReady=function(){a.onYouTubeIframeAPIReady=null,a.dfd.resolve("done"),t()}):"object"===("undefined"==typeof YT?"undefined":_typeof(YT))?t():a.dfd.done(function(e){t()})};// YTPlayer Object
YTPlayer={player:null,
// Defaults
defaults:{ratio:16/9,videoId:"LSmgKRx5pBo",mute:!0,repeat:!0,width:d(a).width(),playButtonClass:"YTPlayer-play",pauseButtonClass:"YTPlayer-pause",muteButtonClass:"YTPlayer-mute",volumeUpClass:"YTPlayer-volume-up",volumeDownClass:"YTPlayer-volume-down",start:0,pauseOnScroll:!1,fitToBackground:!0,playerVars:{iv_load_policy:3,modestbranding:1,autoplay:1,controls:0,showinfo:0,wmode:"opaque",branding:0,autohide:0},events:null},
/**
     * @function init
     * Intializes YTPlayer object
     */
init:function e(t,o){var n=this;return n.userOptions=o,n.$body=d("body"),n.$node=d(t),n.$window=d(a),// Setup event defaults with the reference to this
n.defaults.events={onReady:function e(t){n.onPlayerReady(t),// setup up pause on scroll
n.options.pauseOnScroll&&n.pauseOnScroll(),// Callback for when finished
"function"==typeof n.options.callback&&n.options.callback.call(this)},onStateChange:function e(t){1===t.data?(n.$node.find("img").fadeOut(400),n.$node.addClass("loaded")):0===t.data&&n.options.repeat&&
// video ended and repeat option is set true
n.player.seekTo(n.options.start)}},n.options=d.extend(!0,{},n.defaults,n.userOptions),n.options.height=Math.ceil(n.options.width/n.options.ratio),n.ID=(new Date).getTime(),n.holderID="YTPlayer-ID-"+n.ID,n.options.fitToBackground?n.createBackgroundVideo():n.createContainerVideo(),// Listen for Resize Event
n.$window.on("resize.YTplayer"+n.ID,function(){n.resize(n)}),r(n.onYouTubeIframeAPIReady.bind(n)),n.resize(n),n},
/**
     * @function pauseOnScroll
     * Adds window events to pause video on scroll.
     */
pauseOnScroll:function e(){var t=this;t.$window.on("scroll.YTplayer"+t.ID,function(){var e;1===t.player.getPlayerState()&&t.player.pauseVideo()}),t.$window.scrollStopped(function(){var e;2===t.player.getPlayerState()&&t.player.playVideo()})},
/**
     * @function createContainerVideo
     * Adds HTML for video in a container
     */
createContainerVideo:function e(){var t=this,o=d('<div id="ytplayer-container'+t.ID+'" >                                    <div id="'+t.holderID+'" class="ytplayer-player-inline"></div>                                     </div>                                     <div id="ytplayer-shield" class="ytplayer-shield"></div>');
/*jshint multistr: true */t.$node.append(o),t.$YTPlayerString=o,o=null},
/**
     * @function createBackgroundVideo
     * Adds HTML for video background
     */
createBackgroundVideo:function e(){
/*jshint multistr: true */
var t=this,o=d('<div id="ytplayer-container'+t.ID+'" class="ytplayer-container background">                                    <div id="'+t.holderID+'" class="ytplayer-player"></div>                                    </div>                                    <div id="ytplayer-shield" class="ytplayer-shield"></div>');t.$node.append(o),t.$YTPlayerString=o,o=null},
/**
     * @function resize
     * Resize event to change video size
     */
resize:function e(t){
//var self = this;
var o=t.$node;t.options.fitToBackground||(o=t.$node);var n=o.width(),a,
// player width, to be defined
i=o.height(),r,
// player height, tbd
l=d("#"+t.holderID);// when screen aspect ratio differs from video, video must center and underlay one dimension
n/t.options.ratio<i?(a=Math.ceil(i*t.options.ratio),// get new player width
l.width(a).height(i).css({position:"absolute",left:(n-a)/2,top:0})):(
// new video width < window width (gap to right)
r=Math.ceil(n/t.options.ratio),// get new player height
l.width(n).height(r).css({position:"absolute",left:0,top:(i-r)/2})),o=l=null},
/**
     * @function onYouTubeIframeAPIReady
     * @ params {object} YTPlayer object for access to options
     * Youtube API calls this function when the player is ready.
     */
onYouTubeIframeAPIReady:function e(){var t=this;t.player=new a.YT.Player(t.holderID,t.options)},
/**
     * @function onPlayerReady
     * @ params {event} window event from youtube player
     */
onPlayerReady:function e(t){this.options.mute&&t.target.mute(),t.target.playVideo()},
/**
     * @function getPlayer
     * returns youtube player
     */
getPlayer:function e(){return this.player},
/**
     * @function destroy
     * destroys all!
     */
destroy:function e(){var t=this;t.$node.removeData("yt-init").removeData("ytPlayer").removeClass("loaded"),t.$YTPlayerString.remove(),d(a).off("resize.YTplayer"+t.ID),d(a).off("scroll.YTplayer"+t.ID),t.$body=null,t.$node=null,t.$YTPlayerString=null,t.player.destroy(),t.player=null}},// Scroll Stopped event.
d.fn.scrollStopped=function(e){var t=d(this),o=this;t.scroll(function(){t.data("scrollTimeout")&&clearTimeout(t.data("scrollTimeout")),t.data("scrollTimeout",setTimeout(e,250,o))})},// Create plugin
d.fn.YTPlayer=function(o){return this.each(function(){var e=this;d(e).data("yt-init",!0);var t=Object.create(YTPlayer);t.init(e,o),d.data(e,"ytPlayer",t)})}}(jQuery,window,document);
//# sourceMappingURL=bgyoutube-min.js.map