/* jshint latedef: false */
/* global AWPCP */
AWPCP.define( 'awpcp/media-manager', [ 'jquery', 'knockout', 'awpcp/file-view-model', 'awpcp/settings' ],
function( $, ko, FileViewModel, settings ) {
    var MediaManager = function( nonce, files ) {
        var vm = this;

        vm.files = ko.observableArray( prepareFiles( files ) );
        vm.images = ko.computed( filterImageFiles );
        vm.videos = ko.computed( filterVideoFiles );
        vm.others = ko.computed( filterOtherFiles );

        vm.haveImages = ko.computed( haveImages );
        vm.haveVideos = ko.computed( haveVideos );
        vm.haveOtherFiles = ko.computed( haveOtherFiles );

        vm.deleteFile = deleteFile;
        vm.setFileAsPrimary = setFileAsPrimary;

        vm.getFileCSSClasses = getFileCSSClasses;
        vm.getFileId = getFileId;

        function prepareFiles( files ) {
            return $.map( files, function( file ) {
                var model = new FileViewModel( file );
                model.enabled.subscribe( updateFileEnabledStatus, model );
                return model;
            } );
        }

        function filterImageFiles() {
            return $.grep( vm.files(), function( file ) { return file.isImage; } );
        }

        function filterVideoFiles() {
            return $.grep( vm.files(), function( file ) { return file.isVideo; } );
        }

        function filterOtherFiles() {
            return $.grep( vm.files(), function( file ) { return ( ! file.isImage && ! file.isVideo ); } );
        }

        function haveImages() {
            return vm.images().length > 0;
        }

        function haveVideos() {
            return vm.videos().length > 0;
        }

        function haveOtherFiles() {
            return vm.others().length > 0;
        }

        function setFileAsPrimary( file ) {
            file.isBeingModified( true );

            $.post( settings.get( 'ajaxurl' ), {
                nonce: nonce,
                action: 'awpcp-set-image-as-primary',
                listing_id: file.listingId,
                file_id: file.id
            }, function( response ) {
                if ( response.status === 'ok' ) {
                    $.each( vm.files(), function( index, file ) {
                        file.isPrimary( false );
                    } );

                    file.isPrimary( true );
                    file.isBeingModified( false );
                }
            } );
        }

        function updateFileEnabledStatus( newStatus ) {
            var file = this;

            if ( file.isBeingModified() ) {
                return;
            } else {
                file.isBeingModified( true );
            }

            $.post( settings.get( 'ajaxurl' ), {
                nonce: nonce,
                action: 'awpcp-update-file-enabled-status',
                listing_id: file.listingId,
                file_id: file.id,
                new_status: newStatus
            }, function( response ) {
                if ( response.status !== 'ok' ) {
                    file.enabled( ! newStatus );
                }
                file.isBeingModified( false );
            } );
        }

        function deleteFile( file ) {
            file.isBeingModified( true );

            $.post( settings.get( 'ajaxurl' ), {
                nonce: nonce,
                action: 'awpcp-delete-file',
                listing_id: file.listingId,
                file_id: file.id
            }, function( response ) {
                if ( response.status === 'ok' ) {
                    vm.files.remove( file );
                    file.isBeingModified( false );
                }
            } );
        }

        function getFileCSSClasses( file ) {
            var classes = [ 'awpcp-uploaded-file' ];

            classes.push( file.enabled() ? 'is-enabled' : 'is-disabled' );
            classes.push( 'is-' + file.status().toLowerCase() );

            if ( file.isPrimary() ) {
                classes.push( 'is-primary' );
            }

            return classes.join( ' ' );
        }

        function getFileId( file ) {
            return 'file-' + file.id;
        }
    };

    return MediaManager;
} );
