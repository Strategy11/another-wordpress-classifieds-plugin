<?php

class AWPCP_Test_Credit_Plans_Table_Template extends AWPCP_UnitTestCase {

    public function test_credit_plans_table_template_uses_currency_settings() {
        $currency_symbol = '$';
        $thousands_separator = '*';
        $decimal_separator = 'ª';

        awpcp()->settings->set_or_update_option( 'currency-symbol', $currency_symbol );
        awpcp()->settings->set_or_update_option( 'show-currency-symbol', 'show-currency-symbol-on-left' );
        awpcp()->settings->set_or_update_option( 'thousands-separator', $thousands_separator );
        awpcp()->settings->set_or_update_option( 'decimal-separator', $decimal_separator );
        awpcp()->settings->set_or_update_option( 'include-space-between-currency-symbol-and-amount', false );

        $credit_plan = Phake::mock( 'AWPCP_CreditPlan' );

        $credit_plan->id = rand() + 1;
        $credit_plan->name = 'Test Plan';
        $credit_plan->description = 'Test Description';
        $credit_plan->credits = 12345;
        $credit_plan->price = 12345.67;

        $params = array(
            'column_names' => array(
                'plan' => 'Plan',
                'description' => 'Description',
                'credits' => 'Credits',
                'price' => 'Price',
            ),
            'table_only' => true,
            'credit_plans' => array( $credit_plan ),
            'selected' => '',
        );

        $template = AWPCP_DIR . '/frontend/templates/payments-credit-plans-table.tpl.php';

        $content = awpcp_render_template( $template, $params );

        $this->assertContains( '>' . '12' . $thousands_separator . '345' . '<', $content );
        $this->assertContains( '>' . $currency_symbol . '12' . $thousands_separator . '345' . $decimal_separator . '67' . '<', $content );
    }
}
