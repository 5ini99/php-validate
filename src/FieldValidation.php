<?php
/**
 * Created by PhpStorm.
 * User: inhere
 * Date: 2017-11-07
 * Time: 10:04
 */

namespace Inhere\Validate;

/**
 * Class FieldValidation
 * - one field to many rules. like Laravel framework
 * ```php
 * [
 *  ['field', 'required|string:5,10|...', ...],
 *  ['field0', ['required', 'string:5,10'], ...],
 *  ['field1', 'rule1|rule2|...', ...],
 *  ['field2', 'rule1|rule3|...', ...],
 * ]
 * ```
 * @package Inhere\Validate
 */
class FieldValidation extends AbstractValidation
{
    protected function collectRules()
    {
        $scene = $this->scene;

        // 循环规则, 搜集当前场景可用的规则
        foreach ($this->getRules() as $rule) {
            // check field
            if (!isset($rule[0]) || !$rule[0]) {
                throw new \InvalidArgumentException('Please setting the field(string) to wait validate! position: rule[0].');
            }

            // check validators
            if (!isset($rule[1]) || !$rule[1]) {
                throw new \InvalidArgumentException('The field validators must be is a validator name(s) string! position: rule[1].');
            }

            // global rule.
            if (empty($rule['on'])) {
                // $this->_availableRules[] = $rule;
                // only use to special scene.
            } else {
                $sceneList = is_string($rule['on']) ? array_map('trim', explode(',', $rule['on'])) : (array)$rule['on'];

                if (!in_array($scene, $sceneList, true)) {
                    continue;
                }

                unset($rule['on']);
                // $this->_availableRules[] = $rule;
            }

            $field = trim(array_shift($rule));

            if (is_object($rule[0])) {
                yield [$field] => $rule;
            } else {
                // 'required|string:5,10;' OR 'required|in:5,10'
                $rules = is_array($rule[0]) ? $rule[0] : array_map('trim', explode('|', $rule[0]));

                foreach ($rules as $aRule) {
                    $rule = $this->parseRule($aRule, $rule);

                    yield [$field] => $rule;
                }
            }

        }
    }

    protected function parseRule($rule, $row)
    {
        $rule = trim($rule, ': ');

        if (false === strpos($rule, ':')) {
            $row[0] = $rule;
            return $row;
        }

        list($name, $args) = explode(':', $rule, 2);
        $row[0] = $name;

        switch ($name) {
            case 'in':
            case 'ontIn':
                $row[] = array_map('trim', explode(',', $args));
                break;

            default:
                $row[] = $args;
                break;
        }

        return $row;
    }
}
