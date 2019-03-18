<?php
/**
 * Upgrade Container Configuration class.
 *
 * @package AWPCP\Upgrade
 */

/**
 * Container configuration object responsible for registering classes used
 * in manual upgrade routines.
 */
class AWPCP_UpgradeContainerConfiguration implements AWPCP_ContainerConfigurationInterface {

    /**
     * @since 4.0.0
     *
     * @see AWPCP_ContainerConfigurationInterface::modify()
     */
    public function modify( $container ) {
        $container['ImportPaymentTransactionsTaskHandler'] = $container->service(
            function( $container ) {
                return new AWPCP_Import_Payment_Transactions_Task_Handler();
            }
        );
    }
}
