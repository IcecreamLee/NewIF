<?php


namespace Framework\Validation;


use Respect\Validation\Exceptions\ValidationException;
use Respect\Validation\Validator;

/**
 * Class IValidator
 * 表单验证器
 *
 * @package Framework\Validation
 */
class IValidator {

    public $inputs = [];

    public $rules = [];

    public $messages = [];

    /**
     * @param array      $inputs
     * @param null|array $rules
     * @param null|array $messages
     * @return bool|mixed
     */
    public function validate(array $inputs, $rules = null, $messages = null) {

        // load custom rules
        Validator::with('Framework\\Validation\\Rules\\');

        $this->inputs = $inputs;

        if (is_array($rules)) {
            $this->rules = $rules;
        }

        if (is_array($messages)) {
            $this->messages = $messages;
        }

        return $this->exec();
    }

    /**
     * @return bool|string
     */
    private function exec() {
        foreach ($this->inputs as $key => $val) {
            if (isset($this->rules[$key])) {
                $ruleItems = is_string($this->rules[$key]) ? explode('|', $this->rules[$key]) : $this->rules[$key];

                // 如input为空，并且非必填，则跳过验证
                if (($val === null || $val === '') && !in_array('required', $ruleItems)) {
                    continue;
                }

                foreach ($ruleItems as $ruleItem) {
                    @list($ruleItem, $range) = explode(':', $ruleItem);
                    try {
                        if ($range) {
                            $v = call_user_func_array([Validator::class, $ruleItem], explode(',', $range));
                        } else {
                            $v = call_user_func(Validator::class . '::' . $ruleItem);
                        }
                        $v->assert($val);
                    } catch (ValidationException $e) {
                        if (isset($this->messages[$key . '.' . $ruleItem])) {
                            $e->findMessages([
                                $ruleItem => $this->messages[$key . '.' . $ruleItem],
                            ]);
                        }
                        return current($e->getMessages());
                    }
                }
            }
        }
        return true;
    }

    /**
     * @param array $rules
     * @return $this
     */
    public function setRules(array $rules) {
        $this->rules = $rules;
        return $this;
    }

    /**
     * @param array $messages
     * @return $this
     */
    public function setMessages(array $messages) {
        $this->messages = $messages;
        return $this;
    }

    /**
     * @param string $key
     * @param string $rule
     * @return $this
     */
    public function setRule(string $key, string $rule) {
        $this->rules[$key] = $rule;
        return $this;
    }

    /**
     * @param string $key
     * @param string $message
     * @return $this
     */
    public function setMessage(string $key, string $message) {
        $this->messages[$key] = $message;
        return $this;
    }
}
