<?php

namespace Utils;

class Validator {
    private $errors = [];

    public function validate($data, $rules) {
        $this->errors = [];

        foreach ($rules as $field => $fieldRules) {
            $value = $data[$field] ?? null;
            $ruleList = explode('|', $fieldRules);

            foreach ($ruleList as $rule) {
                $this->validateRule($field, $value, $rule);
            }
        }

        return $this->errors;
    }

    private function validateRule($field, $value, $rule) {
        [$ruleName, $ruleParams] = $this->parseRule($rule);

        switch ($ruleName) {
            case 'required':
                if (empty($value)) {
                    $this->errors[$field][] = "$field is required";
                }
                break;

            case 'string':
                if (!is_string($value)) {
                    $this->errors[$field][] = "$field must be a string";
                }
                break;

            case 'integer':
                if (!is_int($value) && !ctype_digit((string)$value)) {
                    $this->errors[$field][] = "$field must be an integer";
                }
                break;

            case 'email':
                if (!empty($value) && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    $this->errors[$field][] = "$field must be a valid email";
                }
                break;

            case 'min':
                if (!empty($value) && strlen($value) < $ruleParams) {
                    $this->errors[$field][] = "$field must be at least $ruleParams characters";
                }
                break;

            case 'max':
                if (!empty($value) && strlen($value) > $ruleParams) {
                    $this->errors[$field][] = "$field must not exceed $ruleParams characters";
                }
                break;

            case 'in':
                $allowed = explode(',', $ruleParams);
                if (!empty($value) && !in_array($value, $allowed)) {
                    $this->errors[$field][] = "$field must be one of: $ruleParams";
                }
                break;

            case 'regex':
                if (!empty($value) && !preg_match($ruleParams, $value)) {
                    $this->errors[$field][] = "$field format is invalid";
                }
                break;
        }
    }

    private function parseRule($rule) {
        if (strpos($rule, ':') === false) {
            return [$rule, null];
        }

        [$name, $params] = explode(':', $rule, 2);
        return [trim($name), trim($params)];
    }
}
