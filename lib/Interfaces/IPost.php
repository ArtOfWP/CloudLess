<?php
/**
 * Created by PhpStorm.
 * User: Andreas
 * Date: 2013-09-22
 * Time: 15:23
 */
namespace CLMVC\Interfaces;

interface IPost {
    public function getParentID();

    public function getType();

    public function getStatus();

    public function getModificationDate();

    public function getAuthorId();
    public function getAuthor();
    public function getAuthorUrl();
    public function getAuthorArchiveUrl();

    public function getUrlPart();

    public function getContent();

    public function getPublicationDate();

    public function getPermalink();

    public function getID();

    public function getTitle();
    public function getExcerpt();
}