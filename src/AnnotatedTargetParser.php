<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer;

use Generator;

/**
 * Will produce a series of AnnotatedTarget instances for a given set of source code directories.
 */
interface AnnotatedTargetParser {

    /**
     * For each PHP file in the given set of $dirs yield an AnnotatedTarget for each annotated-container Attribute
     * found.
     *
     * @param array $dirs
     * @return Generator
     */
    public function parse(array $dirs) : Generator;

}