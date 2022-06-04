<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer;

use InvalidArgumentException;

final class ActiveProfilesBuilder {

    private array $profiles = [];

    private function __construct() {}

    public static function hasDefault() : self {
        return new self();
    }

    public function add(string... $profiles) : self {
        if (empty($profiles)) {
            throw new InvalidArgumentException('When adding a profile at least 1 value must be provided.');
        } else if (in_array('default', $profiles)) {
            throw new InvalidArgumentException("The 'default' profile is already active and should not be added explicitly.");
        } else if (!empty($dupes = array_intersect($this->profiles, $profiles))) {
            throw new InvalidArgumentException(sprintf(
                "The '%s' %s already active and cannot be added again.",
                join("', '", $dupes),
                count($dupes) === 1 ? 'profile is' : 'profiles are'
            ));
        }
        $instance = clone $this;
        $instance->profiles = [...$this->profiles, ...$profiles];
        return $instance;
    }

    public function addIf(string $profile, callable $decider) : self {
        if ($profile === 'default') {
            throw new InvalidArgumentException("The 'default' profile is already active and should not be added explicitly.");
        }
        $instance = clone $this;
        if ($decider()) {
            if (in_array($profile, $this->profiles)) {
                throw new InvalidArgumentException(sprintf(
                    "The '%s' profile is already active and cannot be added again.",
                    $profile
                ));
            }
            $instance->profiles[] = $profile;
        }
        return $instance;
    }

    public function addAllIf(array $profiles, callable $decider) : self {
        if (in_array('default', $profiles)) {
            throw new InvalidArgumentException("The 'default' profile is already active and should not be added explicitly.");
        }
        $instance = clone $this;
        if ($decider()) {
            if (!empty($dupes = array_intersect($this->profiles, $profiles))) {
                throw new InvalidArgumentException(sprintf(
                    "The '%s' %s already active and cannot be added again.",
                    join("', '", $dupes),
                    count($dupes) === 1 ? 'profile is' : 'profiles are'
                ));
            }
            $instance->profiles = [...$this->profiles, ...$profiles];
        }
        return $instance;
    }

    public function build() : array {
        return ['default', ...$this->profiles];
    }

}