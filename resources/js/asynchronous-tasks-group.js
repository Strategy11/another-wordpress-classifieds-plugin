/*global AWPCP, _*/

AWPCP.define('awpcp/asynchronous-tasks-group', [
    'jquery',
    'knockout',
    'moment',
    'awpcp/asynchronous-task',
    'awpcp/settings'
],
function($, ko, moment, AsynchronousTask, settings) {

    var AsynchronousTasksGroup = function( params ) {
        this.title = ko.observable( params.title );
        this.content = ko.observable( params.content );

        this.successContent = ko.observable( params.successContent );

        this.tasks = ko.observableArray( params.tasks );

        this.startTime = ko.observable( null );
        this.lastUpdatedTime = ko.observable( null );

        this.tasksCount = this.tasks().length;
        this.currentTaskIndex = ko.observable( 0 );
        this.tasksCompleted = ko.observable( 0 );

        this.tasksLeft = ko.computed( function() {
            return this.tasksCount - this.tasksCompleted();
        }, this );

        this.running = ko.observable( false );
        this.completed = ko.observable( this.tasksLeft() === 0 );

        this.percentageOfCompletion = ko.computed(function() {
            var tasks = this.tasks(),
                totalPoints = 0
                completedPoints = 0;

            $.each( tasks, function( index, task ) {
                totalPoints += task.getWeight();

                if ( task.isCompleted() ) {
                    completedPoints += task.getWeight();
                } else {
                    completedPoints += task.getWeight() * task.getPercentageOfCompletion() / 100;
                }
            } );

            console.log(
                this.title(),
                completedPoints,
                totalPoints,
                Math.round( ( completedPoints / totalPoints ) * 10000 ) / 100
            );

            return Math.round( ( completedPoints / totalPoints ) * 10000 ) / 100;
        }, this).extend({ throttle: 1 });

        this.percentageOfCompletionString = ko.computed(function() {
            return this.percentageOfCompletion() + '%';
        }, this);
    }

    $.extend( AsynchronousTasksGroup.prototype, AsynchronousTask.prototype, {
        getRemainingTime: function() {
            return this.remainingTime;
        },

        getPercentageOfCompletion: function() {
            return this.percentageOfCompletion();
        },

        getWeight: function() {
            return this.tasksCount;
        },

        execute: function( done ) {
            var group = this, index = group.currentTaskIndex();

            if ( index >= group.tasksCount ) {
                group.running( false );
                group.completed( true );

                if ( $.isFunction( done ) ) {
                    return done();
                }
            } else {
                group.tasks()[ index ].execute( function() {
                    group.currentTaskIndex( group.currentTaskIndex() + 1 );
                    group.tasksCompleted( group.tasksCompleted() + 1 );

                    setTimeout( function() {
                        group.execute( done );
                    }, 1 );
                } );
            }

            group.running( true );
        }
    } );

    return AsynchronousTasksGroup;
});
