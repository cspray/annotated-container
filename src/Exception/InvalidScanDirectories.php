<?php

namespace Cspray\AnnotatedContainer\Exception;

final class InvalidScanDirectories extends Exception {

    public static function fromEmptyList() : self {
        $msg = 'ContainerDefinitionCompileOptions must include at least 1 directory to scan, but none were provided.';
        return new self($msg);
    }

    public static function fromDuplicatedDirectories() : self {
        $message = 'ContainerDefinitionCompileOptions includes duplicate scan directories. Please pass a distinct set of directories to scan.';
        return new self($message);
    }

}