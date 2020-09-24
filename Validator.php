<?php

namespace LandValidator;

use Closure;
use LandValidator\Exception\NotExpectedDataFormatException;
use LandValidator\Exception\NoRuleMethodException;
use LandValidator\Exception\ValidateFailedException;
use LandValidator\Exception\ValidateFailedListException;

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
     * @param $conditions
     * @param $level
     *
     * @throws ValidateFailedListException | ValidateFailedException
     */
    function validate($data, array $conditions, int $level = self::LEVEL2)
    {
        $failedExceptions = [];
        foreach ($conditions as $path => $rules) {
            $info = [];
            $info['path'] = $path;
            $info['item'] = explode('.', $path);
            $info['count'] = count($info['item']);
            if ($level === self::LEVEL2) {
                try {
                    $this->stack($data, $info, $rules);
                } catch (ValidateFailedException $e) {
                    $failedExceptions[] = $e;
                } catch (NoRuleMethodException $e) {
                    throw $e;
                } catch (NotExpectedDataFormatException $e) {
                    throw $e;
                }
            } else {
                $this->stack($data, $info, $rules);
            }
        }
        if (count($failedExceptions) > 0) {
            throw new ValidateFailedListException($failedExceptions);
        }
    }

    private function stack($data, $info, $rules)
    {
        $firstPath = array_shift($info['item']);
        $info['count']--;
        if ($firstPath === '*') {
            if (is_string($data)) {
                throw new NotExpectedDataFormatException($data, $info, $rules);
            }
            $endDatum = $data;
        } else {
            $endDatum = $data[$firstPath] ?? null;
            if ($info['count'] && is_string($endDatum)) {
                throw new NotExpectedDataFormatException($data, $info, $rules);
            }
        }
        $emptyString = is_string($endDatum) && strlen($endDatum) === 0;
        $emptyArray = is_array($endDatum) && count($endDatum) === 0;
        if ($emptyString || $emptyArray || is_null($endDatum)) {
            if (preg_match('@must@', $rules)) {
                throw new ValidateFailedException($info['path'], 'must');
            }
            return;
        }
        if ($info['count'] === 0) {
            $rules = preg_replace('@[&]?[&]?must[&]?[&]?@', '', $rules);
        }
        if ($firstPath === '*') {
            if ($info['count']) {
                foreach ($data as $datum) {
                    $this->stack($datum, $info, $rules);
                }
            } else {
                foreach ($data as $datum) {
                    $this->pipeline($datum, $info, explode('&&', $rules));
                }
            }
        } else {
            if ($info['count']) {
                $this->stack($endDatum, $info, $rules);
            } else {
                $this->pipeline($endDatum, $info, explode('&&', $rules));
            }
        }
    }

    private function pipeline($data, $info, $rules)
    {
        foreach ($rules as $rule) {
            $into = explode(':', $rule);
            $name = $into[0];
            $value = $into[1] ?? null;
            if (!isset($this->rules[$name])) {
                throw new NoRuleMethodException(sprintf("rule not found: $name"));
            }
            /** @var Closure $method */
            $method = $this->rules[$name];
            $params = explode(',', $value);
            $result = $method($data, $params);
            if ($result !== true) {
                throw new ValidateFailedException($info['path'], $name, $params);
            }
        }
    }
}