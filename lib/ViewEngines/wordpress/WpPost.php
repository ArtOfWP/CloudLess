<?php

namespace CLMVC\ViewEngines\WordPress;

/*
ID				// ID of the post
post_author    		// ID of the post author
post_date      		// timestamp in local time
post_date_gmt  		// timestamp in gmt time
post_content   		// Full (unprocessed) body of the post
post_title     		// title of the post
post_excerpt   		// excerpt field of the post, caption if attachment
post_status    		// post status: publish, new, pending, draft, auto-draft, future, private, inherit, trash
comment_status 		// comment status: open, closed
ping_status    		// ping/trackback status
post_password  		// password of the post
post_name      		// post slug, string to use in the URL
post_modified  		// timestamp in local time
post_modified_gmt 	// timestatmp in gmt time
post_parent    		// id of the parent post.
guid           		// global unique id of the post
menu_order     		// menu order
post_type      		// type of post: post, page, attachment, or custom string
post_mime_type 		// mime type for attachment posts
comment_count  		// number of comments
*/
use CLMVC\Interfaces\IPost;

class WpPost implements IPost{
    /**
     * @var null|\WP_Post
     */
    private $currentPost = null;
    private $postId = 0;
    public function __construct($id = 0) {
        $this->postId = $id;
        if ($id)
            $this->currentPost = get_post($id);
        else {
            global $post;
            $this->currentPost = $post;

        }
    }

    public function getParentID() {
        return $this->currentPost->ID;
    }

    public function getType() {
        return $this->currentPost->post_type;
    }

    public function getStatus() {
        return $this->currentPost->post_status;
    }

    public function getModificationDate() {
        return $this->currentPost->post_modified;
    }

    public function getAuthor() {
        return get_the_author();
    }

    public function getAuthorUrl() {
        return esc_url(get_the_author_meta('url'));
    }

    public function getAuthorArchiveUrl() {
        return esc_url( get_author_posts_url( $this->getAuthorId()));
    }

    public function getUrlPart() {
        return $this->currentPost->post_name;
    }

    public function getContent() {
        return get_the_content();
    }

    public function getPublicationDate() {
        return get_the_date();
    }

    public function getPermalink() {
        return get_permalink($this->currentPost);
    }

    public function getID() {
        return $this->currentPost->ID;
    }

    public function getTitle() {
        return get_the_title($this->postId);
    }

    public function getAuthorId() {
        return $this->currentPost->post_author;
    }

    public function getExcerpt() {
        return get_the_excerpt();
    }
}