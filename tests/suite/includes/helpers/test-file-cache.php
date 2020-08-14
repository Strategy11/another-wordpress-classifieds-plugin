<?php

/**
 * @group core
 */
class AWPCP_TestFileCache extends AWPCP_UnitTestCase {

    public function setup() {
        parent::setup();

        $this->cache_dir = WP_CONTENT_DIR . '/uploads/test/awpcp/cache-' . time();
        $this->property = 'test-' . time();
    }

    public function teardown() {
        awpcp_rmdir( $this->cache_dir );
        parent::teardown();
    }

    public function test_cache() {
        $cache = new AWPCP_FileCache( $this->cache_dir );
        $cache->set( $this->property, 'test' );

        $this->assertTrue( file_exists( $cache->path( $this->property ) ) );
        $this->assertEquals( 'test', $cache->get( $this->property ) );
    }

    public function test_cache_throws_an_exception_if_cant_write_cache_entry() {
        $cache = new AWPCP_FileCache( '/root' );

        try {
            $cache->set( $this->property, 'test' );
            $this->fail();
        } catch ( AWPCP_IOError $e ) {
            // success
        }
    }

    public function test_path() {
        $cache = new AWPCP_FileCache( $this->cache_dir );
        $path = $this->cache_dir . '/' . $this->property . '.json';

        $this->assertEquals( $path, $cache->path( $this->property ) );
    }

    public function test_remove() {
        $cache = new AWPCP_FileCache( $this->cache_dir );
        $cache->set( $this->property, 'test' );

        $this->assertTrue( file_exists( $cache->path( $this->property ) ) );

        try {
            $cache->remove( $this->property );
        } catch ( AWPCP_IOError $e ) {
            $this->fail();
        }

        $this->assertFalse( file_exists( $cache->path( $this->property ) ) );
    }

    public function test_url() {
        $cache = new AWPCP_FileCache( $this->cache_dir );
        $cache->set( $this->property, 'test' );

        $url = str_replace( WP_CONTENT_DIR, WP_CONTENT_URL, $cache->path( $this->property ) );

        $this->assertEquals( $url, $cache->url( $this->property ) );
    }
}
