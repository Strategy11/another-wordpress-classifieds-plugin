<?php

class AWPCP_Test_Task_Queue extends AWPCP_UnitTestCase {

    public function setup() {
        parent::setup();

        $this->uploads_dir = '/tmp/awpcp-task-queue';

        $this->tasks = Phake::mock( 'AWPCP_TasksCollection' );
        $this->settings = Phake::mock( 'AWPCP_Settings_API' );

        Phake::when( $this->settings )->get_runtime_option( 'awpcp-uploads-dir' )->thenReturn( $this->uploads_dir );
    }

    public function teardown() {
        parent::teardown();

        if ( file_exists( "{$this->uploads_dir}/task-queue.lock" ) ) {
            unlink( "{$this->uploads_dir}/task-queue.lock" );
        }

        if ( file_exists( $this->uploads_dir ) ) {
            rmdir( $this->uploads_dir );
        }
    }

    public function test_task_queue_event() {
        $active_task = Phake::mock( 'AWPCP_TaskLogic' );
        $next_task = Phake::mock( 'AWPCP_TaskLogic' );

        Phake::when( $this->tasks )->get_next_active_task->thenReturn( $active_task );
        Phake::when( $this->tasks )->get_next_task->thenReturn( $next_task );

        $queue = new AWPCP_TaskQueue( $this->tasks, $this->settings );
        $queue->task_queue_event();

        Phake::verify( $this->tasks )->get_next_active_task();
    }
}
