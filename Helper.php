<?php


namespace LandValidator;


use LandValidator\Exception\ValidateFailedException;
use LandValidator\Exception\ValidateFailedListException;

class Helper
{
    private array $message = [];

    private array $transName = [];

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

    function useTransName(array $transName)
    {
        if (count($this->transName) === 0) {
            $this->transName = $transName;
        } else {
            foreach ($transName as $i => $v) {
                $this->transName[$i] = $v;
            }
        }
    }

    function fmtFailed(ValidateFailedException $e)
    {
        $name = $this->transName[$e->path];
        $template = $this->message[$e->name];
        $params = [$name, ...$e->params];
        $message = preg_replace_callback('@{\$([\d])}@', function ($matches) use ($params) {
            return $params[$matches[1]];
        }, $template);
        return [$e->path => $message];
    }

    function fmtFailedList(ValidateFailedListException $es)
    {
        $result = [];
        /** @var ValidateFailedException $e */
        foreach ($es->exceptions as $e) {
            $name = $this->transName[$e->path];
            $template = $this->message[$e->name];
            $params = [$name, ...$e->params];
            $message = preg_replace_callback('@{\$([\d])}@', function ($matches) use ($params) {
                return $params[$matches[1]];
            }, $template);
            $result[$e->path] = $message;
        }
        return $result;
    }
}