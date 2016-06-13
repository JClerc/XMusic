
jQuery(document).ready(function($) {

    // Variables
    // -------------------------------

    var playClip;
    var stopClip;
    var pauseTracks;


    // YouTube Player
    // -------------------------------

    (function () {

        var loaded = false;
        var player;

        var onPlayerReady = function (e) {
            e.target.playVideo();
        };

        playClip = function (id) {

            if (!loaded) {

                // Lazy-loading
                loaded = true;

                window.onYouTubeIframeAPIReady = function () {
                    player = new YT.Player('yt-player', {
                        height: '390',
                        width: '640',
                        videoId: id,
                        events: {
                            'onReady': onPlayerReady
                        }
                    });
                };

                var tag = document.createElement('script');
                tag.src = "https://www.youtube.com/iframe_api";
                var firstScriptTag = document.getElementsByTagName('script')[0];
                firstScriptTag.parentNode.insertBefore(tag, firstScriptTag);
            } else {
                if (player) {
                    player.loadVideoById(id);
                } else {
                    var loop = setInterval(function () {
                        if (player) {
                            player.loadVideoById(id);
                            clearInterval(loop);
                        }
                    }, 1000);
                }
            }

            if (pauseTracks) pauseTracks();
        };

        stopClip = function () { player.stopVideo(); };

    })();


    // Bulma Modal
    // -------------------------------

    var $toggle = $('.nav-toggle');
    var $menu = $('.nav-menu');

    $toggle.click(function() {
        $(this).toggleClass('is-active');
        $menu.toggleClass('is-active');
    });

    $('.modal-button').click(function() {
        var target = $(this).data('target');
        $('html').addClass('is-clipped');
        $(target).addClass('is-active');
    });

    $('.modal-background, .modal-close').click(function() {
        $('html').removeClass('is-clipped');
        $(this).parent().removeClass('is-active');
        stopClip();
    });


    // Play music preview
    // -------------------------------

    var tracks = [];

    pauseTracks = function () {
        $('.play-track.track-is-playing').each(function (i, e) {
            var src = $(this).data('src');
            if (src && tracks[src] && tracks[src] instanceof Audio) {
                tracks[src].pause();
            }
            $(this).removeClass('track-is-playing');
            $(this).find('.fa-pause').removeClass('fa-pause').addClass('fa-music');
        });
    };

    $('.play-track').on('click', function (e) {
        var src = $(this).data('src');
        var start = !$(this).hasClass('track-is-playing');
        e.preventDefault();
        pauseTracks();
        if (start) {
            var $this = $(this);
            // Run after event "pause"
            setTimeout(function () {
                // Restart audio
                var music = new Audio(src);
                music.addEventListener('pause', pauseTracks);
                music.addEventListener('play', function () {
                    $this.removeClass('is-loading');
                });
                tracks[src] = music;
                $this.addClass('is-loading track-is-playing');
                $this.find('.fa-music').removeClass('fa-music').addClass('fa-pause');
                setTimeout(function () {
                    if ($this.hasClass('track-is-playing')) {
                        music.play();
                    } else {
                        $this.removeClass('is-loading');
                    }
                }, 200);
            }, 2);
        }
    });


    // View clip
    // -------------------------------

    $('.view-clip').on('click', function (e) {
        e.preventDefault();
        var $this = $(this).addClass('is-loading');
        $.ajax({
            url: $this.data('src'),
            dataType: 'json',
            method: 'POST',
            data: {
                prepare: false
            }
        }).done(function (json) {
            if (json.success) {
                playClip(json.id);
                $this.removeClass('is-loading');
                $('html').addClass('is-clipped');
                $('.clip-modal').addClass('is-active');
            } else {
                $this.hide();
                $this.siblings('.clip-error').show();
            }
        }).fail(function () {
            $this.hide();
            $this.siblings('.clip-error').show();
        });
    });


    // Download a track
    // -------------------------------

    $('.download-track').on('click', function (e) {
        e.preventDefault();
        var $this = $(this).addClass('is-loading');
        $.ajax({
            url: $this.attr('href'),
            dataType: 'json',
            method: 'POST',
            data: {
                prepare: true
            }
        }).done(function (json) {
            if (json.success) {
                window.location.href = $this.data('goto') + json.id + '/' + encodeURIComponent(json.artist) + '/' + encodeURIComponent(json.track);
                $this.removeClass('is-loading');
            } else {
                $this.hide();
                $this.siblings('.download-error').show();
            }
        }).fail(function () {
            $this.hide();
            $this.siblings('.download-error').show();
        });
    });

});
