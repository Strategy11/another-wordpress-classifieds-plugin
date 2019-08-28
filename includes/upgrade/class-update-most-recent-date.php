<?php
/**
 * @package AWPCP\Upgrade
 */

/**
 * Upgrade routine to update `_awpcp_most_recent_start_date` to match renewal date.
 */
class AWPCP_UpdateMostRecentDate implements AWPCP_Upgrade_Task_Runner {

    /**
     * @var object
     */
    private $db;

    /**
     * Constructor.
     */
    public function __construct( $db ) {
        $this->db = $db;
    }

    /**
     * @since 4.0.5
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter) $last_item_id
     */
    public function count_pending_items( $last_item_id ) {
        $sql    = <<<SQL
SELECT COUNT(p.ID) AS COUNT
FROM {$this->db->posts} AS p
         INNER JOIN {$this->db->postmeta}  AS pm ON p.ID = pm.post_id
         INNER JOIN {$this->db->postmeta} AS pm2 ON p.ID = pm2.post_id
WHERE pm.meta_key = '_awpcp_renewed_date'
  AND pm.meta_value != ''
  AND pm2.meta_key = '_awpcp_most_recent_start_date'
  AND pm2.meta_value < pm.meta_value
SQL;
        $result = $this->db->get_results( $sql );

        return $result[0]->COUNT;
    }

    /**
     * @since 4.0.5
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter) $last_item_id
     */
    public function get_pending_items( $last_item_id ) {
        $sql    = <<<SQL
SELECT p.ID, pm.meta_value AS renewed, pm2.meta_value AS start
FROM {$this->db->posts} AS p
         INNER JOIN {$this->db->postmeta} AS pm ON p.ID = pm.post_id
         INNER JOIN {$this->db->postmeta} AS pm2 ON p.ID = pm2.post_id
WHERE pm.meta_key = '_awpcp_renewed_date'
  AND pm.meta_value != ''
  AND pm2.meta_key = '_awpcp_most_recent_start_date'
  AND pm2.meta_value < pm.meta_value
lIMIT 50
SQL;
        $result = $this->db->get_results( $sql );

        return $result;
    }

    /**
     * @since 4.0.5
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter) $last_item_id
     */
    public function process_item( $item, $last_item_id ) {
        update_post_meta( absint( $item->ID ), '_awpcp_most_recent_start_date', $item->renewed );

        return $item->ID;
    }
}
