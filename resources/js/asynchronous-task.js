/*global AWPCP*/

AWPCP.define('awpcp/asynchronous-task', ['jquery', 'knockout', 'moment', 'awpcp/settings'],
function($, ko, moment, settings) {

    var AsynchronousTask = function( params ) {
        this.name = ko.observable( params.name );
        this.description = ko.observable( params.description );

        this.action = params.action;
        this.context = params.context;

        this.startTime = ko.observable( null );
        this.lastUpdatedTime = ko.observable( null );

        this.recordsCount = ko.observable( params.recordsCount || null );
        this.recordsLeft = ko.observable( params.recordsLeft || null );

        this.templates = params.templates;

        this.numberOfRecordsProcessed = ko.computed(function() {
            var recordsCount = this.recordsCount(),
                recordsLeft = this.recordsLeft();

            if ( recordsCount !== null && recordsLeft !== null ) {
                return recordsCount - recordsLeft;
            } else {
                return 0;
            }
        }, this);

        this.numberOfRecordsProcessedMessage = ko.computed(function() {
            var numberOfRecordsProcessed = this.numberOfRecordsProcessed(),
                totalNumberOfRecords = this.recordsCount(),
                message = this.templates.itemsProcessed;

                message = message.replace( '<number-of-items-processed>', numberOfRecordsProcessed );
                message = message.replace( '<total-number-of-items>', totalNumberOfRecords );

            return message;
        }, this);

        this.running = ko.observable( false );
        this.completed = ko.observable( false );

        this.percentageOfCompletion = ko.computed(function() {
            var recordsCount = this.recordsCount(),
                recordsLeft = this.recordsLeft(),
                progress;

            if ( recordsLeft === null || recordsCount === null ) {
                progress = 0;
            } else if ( recordsLeft === 0 ) {
                progress = 100;
            } else if ( recordsCount > 0 ) {
                progress = 100 * ( recordsCount - recordsLeft ) / recordsCount;
            }

            return Math.round( progress * 100 ) / 100;
        }, this).extend({ throttle: 1 });

        this.percentageOfCompletionString = ko.computed(function() {
            return this.percentageOfCompletion() + '%';
        }, this);

        this.remainingTime = ko.computed((function() {
            var lastRemainingTimeUpdateTime = new Date(),
                remainingTime = null;

            return function() {
                var startTime = this.startTime(),
                    lastUpdatedTime = this.lastUpdatedTime(),
                    percentageOfCompletion = this.percentageOfCompletion(),
                    now = new Date(),
                    progressLeft, timeTaken, remainingSeconds;

                if ( ( now - lastRemainingTimeUpdateTime ) < 2500 ) {
                    return remainingTime;
                }

                if ( startTime === null || lastUpdatedTime === null ) {
                    return null;
                }

                if ( percentageOfCompletion === 0 ) {
                    return null;
                }

                progressLeft = 100 - percentageOfCompletion;
                timeTaken = lastUpdatedTime - startTime;
                remainingSeconds = progressLeft * timeTaken / percentageOfCompletion / 1000;
                lastRemainingTimeUpdateTime = now;

                if ( remainingSeconds > 0 ) {
                    remainingTime = moment(now).add(remainingSeconds, 'seconds').from(now, true);
                } else {
                    remainingTime = null;
                }

                return remainingTime;
            };
        })(), this);
    };

    $.extend( AsynchronousTask.prototype, {
        getStartTime: function() {
            return this.startTime();
        },

        setStartTime: function( startTime ) {
            this.startTime( startTime );
        },

        getLastUpdatedTime: function() {
            return this.lastUpdatedTime();
        },

        setLastUpdatedTime: function( lastUpdatedTime ) {
            this.lastUpdatedTime( lastUpdatedTime );
        },

        getRemainingTime: function() {
            return this.remainingTime();
        },

        getPercentageOfCompletion: function() {
            return this.percentageOfCompletion();
        },

        getWeight: function() {
            return 1;
        },

        isCompleted: function() {
            return this.completed();
        },

        isRunning: function() {
            return this.running();
        },

        setStatusMessage: function( message ) {
            this.messages.status( message );
        },

        setErrorMessage: function( message ) {
            this.messages.error( message );
        },

        execute: function( done ) {
            var task = this;

            if ( task.getStartTime() === null ) {
                task.setStartTime( new Date() );
            }

            $.getJSON( settings.get( 'ajaxurl' ), {
                action: task.action,
                context: task.context
            }, function( response ) {
                task._onSuccess( response, $.isFunction( done ) ? done : $.noop );
            } );

            this.running( true );
        },

        _onSuccess: function( response, done ) {
            if (response.status === 'ok') {
                this._handleSuccessfulResponse( response, done );
            } else {
                this._handleErrorResponse( response, done );
            }
        },

        _handleSuccessfulResponse: function( response, done ) {
            var task = this;

            if ( response.message ) {
                task.setStatusMessage( response.message );
            }

            task.recordsCount( task.recordsCount() || parseInt( response.recordsCount, 10 ) );
            task.recordsLeft( parseInt( response.recordsLeft, 10 ) );
            task.setLastUpdatedTime( new Date() );

            if ( task.recordsLeft() === 0 ) {
                task.running( false );
                task.completed( true );
                done();
            } else {
                setTimeout( function() { task.execute( done ); }, 1 );
            }
        },

        _handleErrorResponse: function( response, done ) {
            this.setErrorMessage( response.error );
            done();
        }
    } );

    return AsynchronousTask;
});
