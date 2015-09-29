/* jshint latedef: false */
/* global AWPCP, plupload */

AWPCP.define( 'awpcp/media-uploader-view', [ 'jquery', 'awpcp/settings' ],
function( $, settings) {
    var MediaUploaderView = Backbone.View.extend({
        initialize: function() {
            var self = this.render();

            self.$dropzone = self.$('.awpcp-media-uploader-dropzone'),
            self.$browseButton = self.$('.awpcp-media-uploader-browser-button');
            self.$restrictions = self.$('.awpcp-media-uploader-restrictions');

            // couldn't make it work using View's events property :(
            self.$dropzone.on( 'dragover', _.bind( self.onDragOver, self ) );
            self.$dropzone.on( 'dragleave', _.bind( self.onDragLeave, self ) );
            self.$dropzone.on( 'drop', _.bind( self.onDragStop, self ) );

            self.listenTo( self.model, 'media-uploader:file-uploaded', _.bind( self.updateUploadRestrictionsMessage, self ) )
            self.listenTo( self.model, 'media-uploader:file-deleted', _.bind( self.updateUploadRestrictionsMessage, self ) );

            self.model.prepareUploader( self.$el, self.$dropzone, self.$browseButton );

            self.configureBeforeUnloadEventHandler();
            self.updateUploadRestrictionsMessage();
        },

        render: function() {
            var self = this;
            return self;
        },

        onDragOver: function( event ) {
            this.$dropzone.addClass( 'awpcp-media-uploader-dropzone-active' );
        },

        onDragLeave: function() {
            this.$dropzone.removeClass( 'awpcp-media-uploader-dropzone-active' );
        },

        onDragStop: function() {
            this.$dropzone.removeClass( 'awpcp-media-uploader-dropzone-active' );
        },

        configureBeforeUnloadEventHandler: function() {
            var self = this;

            window.onbeforeunload = function() {
                if ( ! self.model.uploader ) {
                    return false;
                }

                if ( self.model.uploader.state == plupload.STARTED ) {
                    return settings.l10n( 'media-uploader-beforeunload', 'files-are-being-uploaded' );
                }

                if ( self.model.uploader.total.queued > 0 ) {
                    return settings.l10n( 'media-uploader-beforeunload', 'files-pending-to-be-uploaded' );
                }

                if ( self.model.uploader.total.uploaded == 0 ) {
                    return settings.l10n( 'media-uploader-beforeunload', 'no-files-were-uploaded' );
                }
            }
        },

        updateUploadRestrictionsMessage: function() {
            this.$restrictions.html( this.model.buildUploadRestrictionsMessage() );
        }
    });

    return MediaUploaderView;
} );
