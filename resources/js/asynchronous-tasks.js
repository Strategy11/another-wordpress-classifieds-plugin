/*global AWPCP, _*/

AWPCP.define( 'awpcp/asynchronous-tasks', [
    'jquery',
    'knockout',
    'moment',
    'awpcp/asynchronous-task',
    'awpcp/asynchronous-tasks-group',
    'awpcp/settings'
],
function($, ko, moment, AsynchronousTask, AsynchronousTasksGroup, settings) {

    ko.bindingHandlers.progress = {
        init: function(element, accessor) {
            var observable = accessor();
            $(element).animate({width: observable()});
        },
        update: function(element, accessor) {
            var observable = accessor();
            $(element).animate({width: observable()});
        }
    };

    function AsynchronousTasks( params ) {
        console.log( params );
        this.title = ko.observable( params.title );
        this.introduction = ko.observable( params.introduction );
        this.submit = ko.observable( params.submit );
        this.templates = params.templates;

        this.group = new AsynchronousTasksGroup({
            tasks: this._getTasksGroups( params )
        });ko.observableArray([]);
    }

    $.extend(AsynchronousTasks.prototype, {
        _getTasksGroups: function( params ) {
            var self = this, groups = [];

            $.each( params.groups, function( index, group ) {
                groups.push( new AsynchronousTasksGroup( {
                    title: group.title,
                    content: group.content,
                    successContent: group.successContent,
                    tasks: self._getTasks( group ),
                } ) );
            } );

            return groups;
        },

        _getTasks: function( group ) {
            var tasks = [];

            $.each( group.tasks, function( index, task ) {
                tasks.push( new AsynchronousTask( task ) );
            } );

            return tasks;
        },

        render: function(element) {
            ko.applyBindings(this, $(element).get(0));
        },

        start: function() {
            this.group.execute();
        }
    });

    return AsynchronousTasks;
});
