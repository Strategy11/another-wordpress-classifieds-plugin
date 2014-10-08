/* jshint latedef: false */
/* global AWPCP, plupload */

AWPCP.define( 'awpcp/media-uploader', [ 'jquery', 'awpcp/settings' ],
function( $, settings) {
    var MediaUplaoder = function( element, options ) {
        var self = this;

        self.element = $( element );
        self.options = options;

        plupload.addFileFilter( 'restrict_file_size', filterFileBySize );
        plupload.addFileFilter( 'restrict_file_count', filterFileByCount );

        // the second call to pluploadQueue is to retrieve a reference to
        // the plupload.Uploader object.
        self.uploader = self.element
            .pluploadQueue( {
                url: settings.get( 'ajaxurl' ),
                filters: {
                    mime_types: getFileTypeFilters(),
                    restrict_file_size: true,
                    restrict_file_count: true,
                },
                multipart_params: {
                    action: 'awpcp-upload-listing-media',
                    listing: options.listingId,
                    nonce: options.nonce
                },
                chunk_size: '10000000',
                runtimes: 'html5,flash,silverlight,html4',
                multiple_queues: true
            } )
            .pluploadQueue();

        self.uploader.bind( 'FileUploaded', onFileUplaoded );
        self.uploader.bind( 'Error', onError );

        function filterFileBySize( enabled, file, done ) {
            console.log( 'filterFileBySize', file );
            done( true );
        }

        function filterFileByCount( enabled, file, done ) {
            console.log( 'filterFileByCount', file );
            done( true );
        }

        function getFileTypeFilters() {
            return $.map( self.options.allowedFiles, function( group/*, index*/ ) {
                return { title: group.title, extensions: group.extensions.join( ',' ) };
            } );
        }

        function onFileUplaoded( uploader, file, response ) {
            console.log( 'FileUplaoded', uploader, file, response.response, $.parseJSON( response.response ) );
            file.status = plupload.FAILED;
        }

        function onError( /*uploader, error*/ ) {
            console.error( 'Error', arguments );
        }
    };

    return MediaUplaoder;
} );
