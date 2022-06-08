<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer;

final class CsvActiveProfilesParser implements ActiveProfilesParser {

    public function parse(string $profiles) : array {
        if (empty($profiles)) {
            throw new \InvalidArgumentException('The profiles to parse cannot be an empty string.');
        }
        $parsedProfiles = preg_split('/\s*,\s*/', trim($profiles), flags: PREG_SPLIT_NO_EMPTY);
        if (empty($parsedProfiles)) {
            throw new \InvalidArgumentException(sprintf("The profile string '%s' results in no valid profiles.", $profiles));
        }
        return $parsedProfiles;
    }

}