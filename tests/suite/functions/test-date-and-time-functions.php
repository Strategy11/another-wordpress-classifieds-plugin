<?php

/**
 * @group core
 */
class AWPCP_TestDateAndTimeFunctions extends AWPCP_UnitTestCase {

    function test_awpcp_strptime_replacement() {
        $expected = array(
            'tm_sec' => 0,
            'tm_min' => 0,
            'tm_hour' => 0,
            'tm_mday' => 20,
            'tm_mon' => 0,
            'tm_year' => 120,
            'unparsed' => 14
        );

        $this->assertEquals( $expected, awpcp_strptime_replacement( '01/20/2014', '%m/%d/%y' ) );
        $this->assertEquals( $expected, awpcp_strptime_replacement( '20/01/2014', '%d/%m/%y' ) );

        $expected['tm_year'] = 114;
        $expected['unparsed'] = '';

        $this->assertEquals( $expected, awpcp_strptime_replacement( '01/20/2014', '%m/%d/%Y' ) );
        $this->assertEquals( $expected, awpcp_strptime_replacement( '20/01/2014', '%d/%m/%Y' ) );
    }

    function test_awpcp_datetime() {
        $timestamp = 1383180176;

        $this->assertEquals( '2013-10-31 00:42:56', awpcp_datetime( 'mysql', $timestamp ) );
    }

    function test_awpcp_date_set_date() {
        $subject_datetime = '2013-11-22 13:45:23';
        $disired_date = '2013-11-23 04:37:57';
        $expected_datetime = '2013-11-23 13:45:23';

        $this->assertEquals( $expected_datetime, awpcp_set_datetime_date( $subject_datetime, $disired_date ) );
    }

    function test_awpcp_get_datetime_format() {
        $date = mktime(12, 34, 0, 11, 25, 2013);

        awpcp()->settings->update_option( 'time-format', 'H:i A' );
        awpcp()->settings->update_option( 'date-format', 'D, M d, Y' );
        awpcp()->settings->update_option( 'date-time-format', '<date> at <time>' );

        $format = awpcp_get_datetime_format();

        $this->assertEquals( 'Mon, Nov 25, 2013 at 12:34 PM', date( $format, $date ) );
    }

    function test_awpcp_get_datetime_format_with_long_text() {
        $date = mktime(12, 34, 0, 11, 25, 2013);

        awpcp()->settings->update_option( 'time-format', 'H:i A' );
        awpcp()->settings->update_option( 'date-format', 'D, M d, Y' );
        awpcp()->settings->update_option( 'date-time-format', '<date> when the clock hit <time>' );

        $format = awpcp_get_datetime_format();

        $this->assertEquals( 'Mon, Nov 25, 2013 when the clock hit 12:34 PM', date( $format, $date ) );
    }

    function test_awpcp_is_mysql_date() {
        $this->assertTrue( awpcp_is_mysql_date( '2013-12-31 12:19:25' ) );
        $this->assertFalse( awpcp_is_mysql_date( 'Tue 12:20' ) );
    }

    function test_awpcp_extend_date_to_end_of_the_day() {
        $test_date = strtotime( '2014-05-06 12:34:45' );
        $expected_date = strtotime( '2014-05-06 23:59:59' );

        $extended_date = awpcp_extend_date_to_end_of_the_day( $test_date );

        $this->assertEquals( $expected_date, $extended_date );
    }
}
