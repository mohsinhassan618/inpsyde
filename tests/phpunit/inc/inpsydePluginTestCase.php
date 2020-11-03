<?php
use PHPUnit\Framework\TestCase;
use Brain\Monkey;



/**
 * An abstraction over WP_Mock to do things fast
 */
class inpsydePluginTestCase extends TestCase {

    /**
     * Setup which calls \WP_Mock setup
     *
     * @return void
     */
    public function setUp(): void {
        parent::setUp();
        Monkey\setUp();
        // A few common passthrough
        // 1. WordPress i18n functions
        Monkey\Functions\stubs(
            [
                '_n',
                '_e',
                'esc_attr',
                'esc_html',
                'esc_textarea',
                '__',
                '_x',
                'esc_html__',
                'esc_html_x',
                'esc_attr_x',
            ]
        );

    }

    /**
     * Teardown which calls \WP_Mock tearDown
     *
     * @return void
     */
    public function tearDown() : void  {
        Monkey\tearDown();
        parent::tearDown();
    }
}