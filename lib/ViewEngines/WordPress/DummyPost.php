<?php
namespace CLMVC\ViewEngines\WordPress;


class DummyPost {
    /**
     * @var array<string|mixed>
     */
    private $post_data;
    private $post;
    public function __construct($args){
        $this->post_data = wp_parse_args( $args, array(
            'ID'                    => -9999,
            'post_status'           => 'public',
            'post_author'           => 0,
            'post_parent'           => 0,
            'post_type'             => 'page',
            'post_date'             => 0,
            'post_date_gmt'         => 0,
            'post_modified'         => 0,
            'post_modified_gmt'     => 0,
            'post_content'          => '',
            'post_title'            => '',
            'post_excerpt'          => '',
            'post_content_filtered' => '',
            'post_mime_type'        => '',
            'post_password'         => '',
            'post_name'             => '',
            'guid'                  => '',
            'menu_order'            => 0,
            'pinged'                => '',
            'to_ping'               => '',
            'ping_status'           => '',
            'comment_status'        => 'closed',
            'comment_count'         => 0,
            'filter'                => 'raw',

            'is_404'                => false,
            'is_page'               => true,
            'is_single'             => false,
            'is_archive'            => false,
            'is_tax'                => false,
        ) );
        $this->post = new \WP_Post( (object) $this->post_data);
    }

    public function overrideWpQuery() {
        global $wp_query, $post;
        $post = $this->post;
        $wp_query->post       = $post;
        $wp_query->posts      = array( $post );
        $wp_query->post_count = 1;
        $wp_query->is_404     = $this->post_data['is_404'];
        $wp_query->is_page    = $this->post_data['is_page'];
        $wp_query->is_single  = $this->post_data['is_single'];
        $wp_query->is_archive = $this->post_data['is_archive'];
        $wp_query->is_tax     = $this->post_data['is_tax'];
        $wp_query->queried_object = $post;
    }
}