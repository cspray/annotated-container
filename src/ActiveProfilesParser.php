<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer;

interface ActiveProfilesParser {

    public function parse(string $profiles) : array;

}