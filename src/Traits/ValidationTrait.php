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

        private function validateIsInteger(string $field, array $data)
        {
            if (isset($data[$field]) && !is_integer($data[$field])) {
                return '"' . $field . '" is not an integer';
            }

            return false;
        }
    }
}