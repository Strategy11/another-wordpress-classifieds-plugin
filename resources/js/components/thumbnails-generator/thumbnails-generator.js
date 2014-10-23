/* jshint latedef: false */
/* global AWPCP */

AWPCP.define( 'awpcp/thumbnails-generator', [ 'jquery', 'knockout', 'awpcp/settings' ],
function( $, ko, settings ) {
    var QUEUE_STOPPED = 0;
    var QUEUE_ACTIVE = 1;

    var ThumbnailsGenerator = function( element, options ) {
        var self = this;

        self.element = $( element );
        self.video = self.element.find( 'video' );
        self.canvas = self.element.find( 'canvas' );
        self.image = self.element.find( 'img' );

        self.queue = ko.observableArray( [] );

        self.index = 0;
        self.status = QUEUE_STOPPED;

        $.subscribe( '/file/added', onFileAdded );
        $.subscribe( '/file/uploaded', onFileUploaded );

        self.video.bind( 'canplay', onVideoCanPlay );
        self.video.bind( 'seeked', onVideoSeeked );

        function onFileAdded( event, file ) {
            window.console.log( file, file.type, file.type.match( 'video.*' ) );
            if ( file.type.match( 'video.*' ) ) {
                self.queue.push( { video: file, thumbnail: ko.observable() } );
                processQueue();
            }
        }

        function processQueue() {
            if ( self.status === QUEUE_STOPPED ) {
                setTimeout( processNextFile, 100 );
                self.status = QUEUE_ACTIVE;
            }
        }

        function processNextFile() {
            window.console.log( 'processNextFile' );
            var video = self.video.get(0);

            if ( self.index < self.queue().length ) {
                self.currentItem = self.queue()[ self.index ];
                self.index = self.index + 1;

                if ( ! video.canPlayType( self.currentItem.video.type ) ) {
                    var message = 'This video file format is not supported.';
                    message = message.replace( '<video-format>', self.currentItem.video.type );
                    return $.publish( '/errors/thumbnails-generator', message );
                }

                video.src = URL.createObjectURL( self.currentItem.video.getNative() );
                video.play();
            } else {
                self.status = QUEUE_STOPPED;
                video.src = null;
                return;
            }
        }

        function onVideoCanPlay() {
            var video = this;

            if ( ! $.isNumeric( video.duration ) || isNaN( video.duration ) ) {
                return;
            }

            if ( Math.abs( video.currentTime - ( video.duration / 2 ) ) > 1 ) {
                video.currentTime = video.duration / 2;
                video.pause();
            }
        }

        function onVideoSeeked() {
            self.currentItem.thumbnail( generateThumbnailForCurrentVideo() );
            setTimeout( processNextFile, 100 );
        }

        function generateThumbnailForCurrentVideo() {
            var video = self.video.get(0),
                canvas = self.canvas.get(0),
                context;

            canvas.width = video.clientWidth;
            canvas.height = video.clientHeight;

            context = canvas.getContext( '2d' );
            context.drawImage( video, 0, 0, canvas.width, canvas.height );

            return canvas.toDataURL();
        }

        function onFileUploaded( event, pluploadFile, fileInfo ) {
            var thumbnailUrl = null;

            $.each( self.queue(), function( index, item ) {
                if ( item.video.id === pluploadFile.id ) {
                    thumbnailUrl = item.thumbnail();
                }
            } );

            if ( thumbnailUrl === null ) {
                return;
            }

            setTimeout( function() {
                $.publish( '/file/thumbnail-updated', [ pluploadFile, fileInfo, thumbnailUrl ] );
            }, 100 );

            uploadGeneratedThumbnail( fileInfo, thumbnailUrl );
        }

        function uploadGeneratedThumbnail( fileInfo, thumbnailUrl ) {
            window.console.log( 'uploadGeneratedThumbnail', arguments );
            $.post( settings.get( 'ajaxurl' ), {
                action: 'awpcp-upload-generated-thumbnail',
                // nonce: options.nonce,
                file: fileInfo.id,
                thumbnail: thumbnailUrl
            }, function() {
                window.console.log( 'uploadGeneratedThumbnail', arguments );
            } );
        }
    };

    return ThumbnailsGenerator;
} );
