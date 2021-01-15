<?php

namespace LandValidator;

use Closure;
use LandValidator\Exception\Invalid;
use LandValidator\Exception\Undefined;
use LandValidator\Exception\Failed;
use LandValidator\Exception\FailedList;

class Validator
{
    const LEVEL1 = 1;
    const LEVEL2 = 2;

    private array $rules = [];

    function useRule(array $rules)
    {
        if (count($this->rules) === 0) {
            $this->rules = $rules;
        } else {
            foreach ($rules as $i => $v) {
                $this->rules[$i] = $v;
            }
        }
    }

    /**
     * @param $data
     * @param array $conditions
     * @param int $level
     * @return array
     * @throws FailedList
     */
    function validate($data, array $conditions, int $level = self::LEVEL2): array
    {
        $failedList = [];
        foreach ($conditions as $path => $rules) {
            $info          = [];
            $info['path']  = $path;
            $info['item']  = explode('.', $path);
            $info['count'] = count($info['item']);
            if ($level === self::LEVEL2) {
                try {
                    $this->stack($data, $info, $rules, $level);
                } catch (Failed | FailedList $e) {
                    $failedList[] = $e;
                }
            } else {
                $this->stack($data, $info, $rules, $level);
            }
        }
        if (count($failedList) > 0) {
            throw new FailedList($failedList);
        }
        return array_intersect_key($data, $conditions);
    }

    /**
     * @param $data
     * @param $info
     * @param $rules
     * @param $level
     * @throws FailedList
     */
    private function stack(&$data, $info, $rules, $level)
    {
        $firstPath = array_shift($info['item']);
        $info['count']--;
        if (is_array($rules)) {
            try {
                if ($firstPath === '*') {
                    if ($info['count']) {
                        foreach ($data as &$datum) {
                            $this->stack($datum, $info, $rules, $level);
                        }
                    } else {
                        foreach ($data as &$datum) {
                            $datum = $this->validate($datum, $rules, $level);
                        }
                    }
                } else {
                    if ($info['count']) {
                        $this->stack($data[$firstPath], $info, $rules, $level);
                    } else {
                        $data[$firstPath] = $this->validate($data[$firstPath], $rules, $level);
                    }
                }
            } catch (Failed $e) {
                $e->prefix = $info['path'];
                throw $e;
            } catch (FailedList $e) {
                $e->prefix = $info['path'];
                throw $e;
            }
        } else {
            if ($firstPath === '*') {
                if (is_string($data)) {
                    throw new Invalid($data, $info, $rules);
                }
                $endDatum = $data;
            } else {
                $endDatum = $data[$firstPath] ?? null;
                if ($info['count'] && is_string($endDatum)) {
                    throw new Invalid($data, $info, $rules);
                }
            }
            $emptyString = is_string($endDatum) && strlen($endDatum) === 0;
            $emptyArray  = is_array($endDatum) && count($endDatum) === 0;
            if ($emptyString || $emptyArray || is_null($endDatum)) {
                if (preg_match('@must@', $rules)) {
                    throw new Failed($info['path'], 'must');
                }
                return;
            }
            if ($info['count'] === 0) {
                $rules = preg_replace('@[&]?[&]?must[&]?[&]?@', '', $rules);
            }
            if ($firstPath === '*') {
                if ($info['count']) {
                    foreach ($data as $datum) {
                        $this->stack($datum, $info, $rules, $level);
                    }
                } else {
                    foreach ($data as $datum) {
                        $this->pipeline($datum, $info, explode('&&', $rules));
                    }
                }
            } else {
                if ($info['count']) {
                    $this->stack($endDatum, $info, $rules, $level);
                } else {
                    $this->pipeline($endDatum, $info, explode('&&', $rules));
                }
            }
        }
    }

    private function pipeline($data, $info, $rules)
    {
        foreach ($rules as $rule) {
            $into  = explode(':', $rule);
            $name  = $into[0];
            $value = $into[1] ?? null;
            if (!isset($this->rules[$name])) {
                throw new Undefined(sprintf("rule not found: $name"));
            }
            /** @var Closure $method */
            $method = $this->rules[$name];
            $params = explode(',', $value);
            $result = $method($data, count($params) === 1 ? $params[0] : $params);
            if ($result !== true) {
                throw new Failed($info['path'], $name, $params);
            }
        }
    }
}