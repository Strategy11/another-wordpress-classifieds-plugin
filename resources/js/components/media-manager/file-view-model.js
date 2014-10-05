/* global AWPCP */
AWPCP.define( 'awpcp/file-view-model', [ 'knockout' ],
function( ko ) {
    var FileViewModel = function( file ) {
        var vm = this;

        vm.id = file.id;
        vm.name = file.name;
        vm.listingId = file.listingId;

        vm.enabled = ko.observable( !! parseInt( file.enabled, 10 ) );
        vm.status = ko.observable( file.status );

        vm.isImage = !! file.isImage;
        vm.isVideo = !! file.isVideo;
        vm.isPrimary = ko.observable( !! file.isPrimary );
        vm.isBeingModified = ko.observable( false );

        vm.thumbnailUrl = file.thumbnailUrl;
        vm.iconUrl = file.iconUrl;
        vm.url = file.url;
    };

    return FileViewModel;
} );
