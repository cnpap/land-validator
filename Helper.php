<?php


namespace LandValidator;


use Generator;
use LandValidator\Exception\Failed;
use LandValidator\Exception\FailedList;

class Helper
{
    private array $message = [];
    private array $trans   = [];

    function useMessage(array $message)
    {
        if (count($this->message) === 0) {
            $this->message = $message;
        } else {
            foreach ($message as $i => $v) {
                $this->message[$i] = $v;
            }
        }
    }

    function useTrans(array $trans)
    {
        if (count($this->trans) === 0) {
            $this->trans = $trans;
        } else {
            foreach ($trans as $i => $v) {
                $this->trans[$i] = $v;
            }
        }
    }

    function fmt(Failed $e, $prefix = ''): array
    {
        $path = $prefix . $e->prefix;
        if ($path) {
            if ($e->path) {
                $path .= '.' . $e->path;
            }
        } else {
            $path = $e->path;
        }
        $name     = $this->trans[$path] ?? $path;
        $template = $this->message[$e->name];
        $params   = [$name, ...$e->params];
        $message  = preg_replace_callback('@{\$([\d])}@', function ($matches) use ($params) {
            return $params[$matches[1]];
        }, $template);
        return [$path => $message];
    }

    function fmtList(FailedList $es): array
    {
        $result = [];
        foreach ($this->errMap($es) as $fail) {
            $result[key($fail)] = current($fail);
        }
        return $result;
    }

    function errMap(FailedList $es): Generator
    {
        foreach ($es->exceptions as $e) {
            if ($e instanceof Failed) {
                yield $this->fmt($e, $es->prefix);
            } else if ($e instanceof FailedList) {
                if ($es->prefix !== '') {
                    $e->prefix = $es->prefix;
                }
                foreach ($this->errMap($e) as $fail) {
                    yield $fail;
                }
            }
        }
    }
}