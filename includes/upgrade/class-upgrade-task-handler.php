<?php

class AWPCP_Upgrade_Task_Handler {

    private $implementation;

    public function __construct( AWPCP_Upgrade_Task_Runner $implementation ) {
        $this->implementation = $implementation;
    }

    public function run_task() {
        $last_item_id = $this->implementation->get_last_item_id();

        $pending_items_count_before = $this->implementation->count_pending_items( $last_item_id );
        $pending_items = $this->implementation->get_pending_items( $last_item_id );

        foreach ( $pending_items as $item ) {
            $last_item_id = $this->implementation->process_item( $item, $last_item_id );
        }

        $pending_items_count_now = $this->implementation->count_pending_items( $last_item_id );

        $this->implementation->update_last_item_id( $last_item_id );

        return array( $pending_items_count_before, $pending_items_count_now );
    }
}
