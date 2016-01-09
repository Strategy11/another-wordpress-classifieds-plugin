<?php

interface AWPCP_Upgrade_Task_Runner {

    function get_last_item_id();
    function update_last_item_id( $last_item_id );

    function count_pending_items( $last_item_id );
    function get_pending_items( $last_item_id );

    function process_item( $item, $last_item_id );
}
