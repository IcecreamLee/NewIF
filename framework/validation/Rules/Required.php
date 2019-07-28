<?php


namespace Framework\Validation\Rules;

use Respect\Validation\Rules\AbstractRule;

class Required extends AbstractRule {

    public function validate($input) {
        return $input === '' || $input === null ? false : true;
    }
}
