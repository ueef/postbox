<?php

namespace Ueef\Postbox\Traits {

    trait ValidationTrait
    {
        protected function validate(array $data, array $validators)
        {
            foreach ($validators as $validator => $fields) {
                if (!$fields) {
                    continue;
                }

                $validator = ucwords(str_replace(['-', '_'], ' ', $validator));
                $validator = 'validate' . str_replace(' ', '', $validator);

                if (!method_exists($this, $validator)) {
                    return 'validator "' . $validator . '" does\'nt exists';
                }

                foreach ($fields as $field) {
                    $message = $this->{$validator}($field, $data);

                    if ($message) {
                        return $message;
                    }
                }
            }

            return false;
        }

        private function validateRequired(string $field, array $data)
        {
            if (!isset($data[$field])) {
                return '"' . $field . '" is required';
            }

            return false;
        }

        private function validateIsArray(string $field, array $data)
        {
            if (isset($data[$field]) && !is_array($data[$field])) {
                return '"' . $field . '" is not an array';
            }

            return false;
        }

        private function validateIsIntegerArray(string $field, array $data)
        {
            if (isset($data[$field])) {
                if (!is_array($data[$field])) {
                    return sprintf('"%s" is not an array', $field);
                }

                foreach ($data[$field] as $value) {
                    if (!is_integer($value)) {
                        return sprintf('"%s" is not an integer array', $field);
                    }
                }
            }

            return false;
        }

        private function validateIsPositiveIntegerArray(string $field, array $data)
        {
            if (isset($data[$field])) {
                if (!is_array($data[$field])) {
                    return sprintf('"%s" is not an array', $field);
                }

                foreach ($data[$field] as $value) {
                    if (!is_integer($value) || $data[$field] <= 0) {
                        return sprintf('"%s" is not an positive integer array', $field);
                    }
                }
            }

            return false;
        }

        private function validateIsNumeric(string $field, array $data)
        {
            if (isset($data[$field]) && !is_numeric($data[$field])) {
                return '"' . $field . '" is not a number';
            }

            return false;
        }

        private function validateIsString(string $field, array $data)
        {
            if (isset($data[$field]) && !is_string($data[$field])) {
                return '"' . $field . '" is not a string';
            }

            return false;
        }

        private function validateIsInteger(string $field, array $data)
        {
            if (isset($data[$field]) && !is_integer($data[$field])) {
                return '"' . $field . '" is not an integer';
            }

            return false;
        }

        private function validateIsPositiveInteger(string $field, array $data)
        {
            if (isset($data[$field]) && !is_integer($data[$field]) && $data[$field] <= 0) {
                return '"' . $field . '" isn\'t a positive integer';
            }

            return false;
        }
    }
}