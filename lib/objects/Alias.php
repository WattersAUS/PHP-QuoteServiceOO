<?php
//
//  Module: Alias.php - G.J. Watson
//    Desc: Alias Object
// Version: 1.01
//

final class Alias {
    private $alias_id;
    private $alias_name;
    private $added;

    public function __construct($arg1, $arg2, $arg3) {
        $this->alias_id   = $arg1;
        $this->alias_name = $arg2;
        $this->added      = $arg3;
    }

    public function getAliasID() {
        return $this->alias_id;
    }

    public function getAliasName() {
        return $this->alias_name;
    }

    public function getTimeAdded() {
        return $this->added;
    }
}
?>
