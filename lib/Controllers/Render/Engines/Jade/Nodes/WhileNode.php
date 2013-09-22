<?php
namespace Jade\Nodes;


class WhileNode extends Node {
    public $expr;
    public $block;

    function __construct($expr, $block) {
        $this->expr = $expr;
        $this->block = $block;
    }
} 